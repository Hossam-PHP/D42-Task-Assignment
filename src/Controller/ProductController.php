<?php


// src/Controller/ProductController.php
namespace App\Controller;


use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @Route("/api/products", name="product_api")
 */
class ProductController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ManagerRegistry $doctrine;


    public function __construct(EntityManagerInterface $entityManager, ManagerRegistry $doctrine)
    {
        $this->entityManager = $entityManager;
        $this->doctrine = $doctrine;
    }

    /**
     * @Route("/products", name="products_list", methods={"GET"})
     */
    public function list(Request $request): Response
    {
        $repository = $this->doctrine->getRepository(Product::class);

        // Filtering
        $category = $request->query->get('category');
        $queryBuilder = $repository->createQueryBuilder('p');
        if ($category) {
            $queryBuilder->andWhere('p.category = :category')
                ->setParameter('category', $category);
        }

        // Sorting
        $sort = $request->query->get('sort', 'title');
        $direction = $request->query->get('direction', 'asc');
        $queryBuilder->orderBy('p.' . $sort, $direction);

        $query = $queryBuilder->getQuery();

        // Pagination
        $page = $request->query->get('page', 1);
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $page,
            5
        );

        return $this->render('product/list.html.twig', [
            'pagination' => $pagination,
            'categories' => $repository->findAllCategories(),
        ]);
    }

    /**
     * @Route("/import", name="import_products", methods={"GET", "POST"})
     */
    public function import(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $xmlFile = $request->files->get('xml_file');
            if ($xmlFile && $xmlFile->getClientOriginalExtension() === 'xml') {
                $importedProducts = $this->parseXmlFile($xmlFile);
                // Save imported products to the database
                // ...

                $this->addFlash('success', 'Products imported successfully.');
                return $this->redirectToRoute('products_list');
            }

            $this->addFlash('error', 'Invalid XML file.');
        }

        return $this->render('product/import.html.twig');
    }

    private function parseXmlFile(UploadedFile $xmlFile): array
    {
        $xml = simplexml_load_file($xmlFile->getPathname());
        $products = [];

        foreach ($xml->product as $product) {
            $name = (string) $product->name;
            $description = (string) $product->description;
            $weight = (string) $product->weight;
            $category = (string) $product->category;

            $products[] = [
                'name' => $name,
                'description' => $description,
                'weight' => $weight,
                'category' => $category,
            ];
        }

        return $products;
    }

    /**
     * @Route("/generate-report", name="generate_report", methods={"GET"})
     */
    public function generateReport(): Response
    {
        $products = $this->doctrine->getRepository(Product::class)->findAll();

        $response = new Response($this->renderView('product/report.csv.twig', [
            'products' => $products,
        ]));

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="import_report.csv"');

        return $response;
    }
}
