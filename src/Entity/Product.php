<?php


namespace App\Entity;


use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private $id;

    #[ORM\Column(length: 255, nullable: true)]
    private $title;

    #[ORM\Column(length: 255, nullable: true)]
    private $description;

    #[ORM\Column(nullable: true)]
    private $weight;

    #[ORM\Column(nullable: true)]
    private $category;

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getTitle(): ?string
    {
        return $this->title;
    }


    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }


    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }


    public function getWeight(): ?float
    {
        return $this->weight;
    }


    public function setWeight(float $weight): self
    {
        $this->weight = $weight;

        return $this;
    }
}
