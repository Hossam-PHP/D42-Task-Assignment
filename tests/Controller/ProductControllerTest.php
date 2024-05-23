<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductControllerTest extends WebTestCase
{
    public function testListAction(): void
    {
        $client = static::createClient();
        $client->request('GET', '/products');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Products List');
    }

    public function testImportAction(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/products');

        $form = $crawler->selectButton('Import')->form();
        $form['xml_file']->upload('path/to/import.xml');
        $client->submit($form);

        $this->assertResponseRedirects('/products');
    }

    public function testImportAction2(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/import',
            [],
            ['xml_file' => new UploadedFile('path/to/import.xml', 'import.xml')]
        );

        $this->assertResponseRedirects('/products');
    }

    public function testImportAction3(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/import');

        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form();
        $form['xml_file']->upload('path/to/import.xml');

        $client->submit($form);

        $this->assertResponseRedirects('/products');
        $this->assertFlashMessage('Products imported successfully.');
    }
}

    public function testGenerateReportAction(): void
    {
        $client = static::createClient();
        $client->request('GET', '/generate-report');

        $this->assertResponseHeaderSame('Content-Type', 'text/csv');
        $this->assertResponseHeaderContains('Content-Disposition', 'attachment; filename="import_report.csv"');
    }
}
