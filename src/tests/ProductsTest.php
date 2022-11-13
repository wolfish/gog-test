<?php

namespace App\Tests;

use App\Entity\GogProducts;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ProductsTest extends WebTestCase
{
    private KernelBrowser $client;

    private object $entityManager;

    public function setUp(): void
    {
        $this->client = static::createClient([], [
            'HTTP_HOST' => 'localhost:8080'
        ]);

        static::bootKernel();
        $this->entityManager = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');

        parent::setUp();
    }

    private function checkResponseCode(string $method, string $uri, int $code): void
    {
        $this->client->request($method, $uri);
        $response = $this->client->getResponse();
        $this->assertSame($code, $response->getStatusCode());
    }

    private function checkResponseJson(string $method, string $uri, int $code): void
    {
        $this->client->request($method, $uri);
        $response = $this->client->getResponse();
        $this->assertSame($code, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }

    private function checkJsonRequest(string $method, string $uri, int $code, array $data): void
    {
        $this->client->jsonRequest($method, $uri, $data);
        $response = $this->client->getResponse();
        $this->assertSame($code, $response->getStatusCode());
    }

    public function testProductList(): void
    {
        $this->checkResponseJson('GET', '/v1/api/product', Response::HTTP_OK);
    }

    public function testProductListPagination(): void
    {
        $this->checkResponseJson('GET', '/v1/api/product/1', Response::HTTP_OK);
        $this->checkResponseJson('GET', '/v1/api/product/3', Response::HTTP_OK);
        $this->checkResponseCode('GET', '/v1/api/product/0', Response::HTTP_BAD_REQUEST);
        $this->checkResponseCode('GET', '/v1/api/product/-1', Response::HTTP_BAD_REQUEST);

        $this->expectException(\TypeError::class);
        $this->checkResponseCode('GET', '/v1/api/product/asdf', Response::HTTP_BAD_REQUEST);
    }

    public function testProductCreate(): void
    {
        $this->checkJsonRequest('POST', '/v1/api/product', Response::HTTP_CONFLICT, [
            'title' => 'Fallout',
            'price' => 100,
            'currency' => 'PLN'
        ]);

        $this->checkJsonRequest('POST', '/v1/api/product', Response::HTTP_BAD_REQUEST, [
            'title' => 'Gothic',
            'price' => '100',
            'currency' => 'PLN'
        ]);

        $this->checkJsonRequest('POST', '/v1/api/product', Response::HTTP_BAD_REQUEST, [
            'title' => 'Gothic',
            'price' => 100,
            'currency' => 'asdf'
        ]);

        $this->checkJsonRequest('POST', '/v1/api/product', Response::HTTP_BAD_REQUEST, [
            'title' => 'Gothic',
            'price' => -1,
            'currency' => 'PLN'
        ]);

        $this->checkJsonRequest('POST', '/v1/api/product', Response::HTTP_BAD_REQUEST, [
            'title' => 'Gothic'
        ]);

        $this->checkJsonRequest('POST', '/v1/api/product', Response::HTTP_CREATED, [
            'title' => 'Gothic',
            'price' => 100,
            'currency' => 'PLN'
        ]);
    }

    public function testProductUpdate(): void
    {
        $productsRepository = $this->entityManager->getRepository(GogProducts::class);
        $product = $productsRepository->findOneBy( ['title' => 'Gothic'] );
        $this->assertInstanceOf(GogProducts::class, $product);

        $this->checkJsonRequest('PUT', '/v1/api/product/' . $product->getId(), Response::HTTP_CONFLICT, [
            'title' => 'Fallout',
            'price' => 100,
            'currency' => 'PLN'
        ]);

        $this->checkJsonRequest('PUT', '/v1/api/product/' . $product->getId(), Response::HTTP_BAD_REQUEST, [
            'title' => 'Gothic',
            'price' => '100',
            'currency' => 'PLN'
        ]);

        $this->checkJsonRequest('PUT', '/v1/api/product/' . $product->getId(), Response::HTTP_BAD_REQUEST, [
            'title' => 'Gothic',
            'price' => 100,
            'currency' => 'asdf'
        ]);

        $this->checkJsonRequest('PUT', '/v1/api/product/' . $product->getId(), Response::HTTP_BAD_REQUEST, [
            'title' => 'Gothic',
            'price' => -1,
            'currency' => 'PLN'
        ]);

        $this->checkJsonRequest('PUT', '/v1/api/product/' . $product->getId(), Response::HTTP_BAD_REQUEST, [
            'title' => 'Gothic'
        ]);

        $this->checkJsonRequest('PUT', '/v1/api/product/99999999', Response::HTTP_NOT_FOUND, [
            'title' => 'Gothic',
            'price' => 100,
            'currency' => 'PLN'
        ]);

        $this->checkJsonRequest('PUT', '/v1/api/product/' . $product->getId(), Response::HTTP_OK, [
            'title' => 'Gothic2',
            'price' => 200,
            'currency' => 'PLN'
        ]);
    }

    public function testProductDelete(): void
    {
        $productsRepository = $this->entityManager->getRepository(GogProducts::class);
        $product = $productsRepository->findOneBy( ['title' => 'Gothic2'] );
        $this->assertInstanceOf(GogProducts::class, $product);

        $this->checkResponseCode('DELETE', '/v1/api/product/99999999', Response::HTTP_NOT_FOUND);
        $this->checkResponseCode('DELETE', '/v1/api/product/' . $product->getId(), Response::HTTP_OK);

        $productsRepository = $this->entityManager->getRepository(GogProducts::class);
        $product = $productsRepository->findOneBy( ['title' => 'Gothic'] );
        $this->assertNull($product);
    }
}
