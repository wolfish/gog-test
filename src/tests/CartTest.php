<?php

namespace App\Tests;

use App\Entity\GogCart;
use App\Entity\GogCartHasProducts;
use App\Entity\GogProducts;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CartTest extends WebTestCase
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

    private function checkResponseJson(string $method, string $uri, int $code): Response
    {
        $this->client->request($method, $uri);
        $response = $this->client->getResponse();
        $this->assertSame($code, $response->getStatusCode());
        $this->assertJson($response->getContent());
        return $response;
    }

    private function checkJsonRequest(string $method, string $uri, int $code, array $data): void
    {
        $this->client->jsonRequest($method, $uri, $data);
        $response = $this->client->getResponse();
        $this->assertSame($code, $response->getStatusCode());
    }

    public function testCartCreate(): void
    {
        $cartRepository = $this->entityManager->getRepository(GogCart::class);
        $cart = $cartRepository->findOneBy( ['idSession' => '588f895a-636e-11ed-81ce-0242ac120003'] );
        if ($cart instanceof GogCart) {
            $this->entityManager->remove($cart);
            $this->entityManager->flush();
        }

        $this->checkResponseCode('POST', '/v1/api/cart', Response::HTTP_BAD_REQUEST);
        $this->checkJsonRequest('POST', '/v1/api/cart', Response::HTTP_BAD_REQUEST, [
            'id_session' => 123
        ]);
        $this->checkJsonRequest('POST', '/v1/api/cart', Response::HTTP_BAD_REQUEST, [
            'id_session' => ''
        ]);
        $this->checkJsonRequest('POST', '/v1/api/cart', Response::HTTP_BAD_REQUEST, [
            'id_sesion' => '588f895a-636e-11ed-81ce-0242ac120003'
        ]);
        $this->checkJsonRequest('POST', '/v1/api/cart', Response::HTTP_CREATED, [
            'id_session' => '588f895a-636e-11ed-81ce-0242ac120003'
        ]);
        $this->checkJsonRequest('POST', '/v1/api/cart', Response::HTTP_CONFLICT, [
            'id_session' => '588f895a-636e-11ed-81ce-0242ac120003'
        ]);
    }

    public function testCartAddProduct(): void
    {
        $productsRepository = $this->entityManager->getRepository(GogProducts::class);
        $product = $productsRepository->findOneBy( ['title' => 'Fallout'] );
        $this->assertInstanceOf(GogProducts::class, $product);

        $product2 = $productsRepository->findOneBy( ['title' => 'Icewind Dale'] );
        $this->assertInstanceOf(GogProducts::class, $product2);

        $product3 = $productsRepository->findOneBy( ['title' => 'Bloodborne'] );
        $this->assertInstanceOf(GogProducts::class, $product3);

        $product4 = $productsRepository->findOneBy( ['title' => 'Don\'t Starve'] );
        $this->assertInstanceOf(GogProducts::class, $product4);

        $cartRepository = $this->entityManager->getRepository(GogCart::class);
        $cart = $cartRepository->findOneBy( ['idSession' => '588f895a-636e-11ed-81ce-0242ac120003'] );
        $this->assertInstanceOf(GogCart::class, $cart);

        $this->checkJsonRequest('POST', '/v1/api/cart/999999999', Response::HTTP_NOT_FOUND, [
            'id_product' => $product->getId(),
            'quantity' => 1
        ]);
        $this->checkJsonRequest('POST', '/v1/api/cart/' . $cart->getIdSession(), Response::HTTP_NOT_FOUND, [
            'id_product' => 9999999,
            'quantity' => 1
        ]);
        $this->checkJsonRequest('POST', '/v1/api/cart/' . $cart->getIdSession(), Response::HTTP_BAD_REQUEST, [
            'id_product' => 'asdf',
            'quantity' => 1
        ]);
        $this->checkJsonRequest('POST', '/v1/api/cart/' . $cart->getIdSession(), Response::HTTP_BAD_REQUEST, [
            'id_product' => $product->getId(),
            'quantity' => '1'
        ]);
        $this->checkJsonRequest('POST', '/v1/api/cart/' . $cart->getIdSession(), Response::HTTP_BAD_REQUEST, [
            'id_product' => $product->getId(),
            'quantity' => 0
        ]);
        $this->checkJsonRequest('POST', '/v1/api/cart/' . $cart->getIdSession(), Response::HTTP_BAD_REQUEST, [
            'id_product' => $product->getId(),
            'quantity' => 11
        ]);
        $this->checkJsonRequest('POST', '/v1/api/cart/' . $cart->getIdSession(), Response::HTTP_CREATED, [
            'id_product' => $product->getId(),
            'quantity' => 1
        ]);
        $this->checkJsonRequest('POST', '/v1/api/cart/' . $cart->getIdSession(), Response::HTTP_OK, [
            'id_product' => $product->getId(),
            'quantity' => 2
        ]);
        $this->checkJsonRequest('POST', '/v1/api/cart/' . $cart->getIdSession(), Response::HTTP_CREATED, [
            'id_product' => $product2->getId(),
            'quantity' => 1
        ]);
        $this->checkJsonRequest('POST', '/v1/api/cart/' . $cart->getIdSession(), Response::HTTP_CREATED, [
            'id_product' => $product3->getId(),
            'quantity' => 1
        ]);
        $this->checkJsonRequest('POST', '/v1/api/cart/' . $cart->getIdSession(), Response::HTTP_FORBIDDEN, [
            'id_product' => $product4->getId(),
            'quantity' => 1
        ]);
    }

    public function testCartList(): void
    {
        $productsRepository = $this->entityManager->getRepository(GogProducts::class);
        $product = $productsRepository->findOneBy( ['title' => 'Fallout'] );
        $this->assertInstanceOf(GogProducts::class, $product);

        $cartRepository = $this->entityManager->getRepository(GogCart::class);
        $cart = $cartRepository->findOneBy( ['idSession' => '588f895a-636e-11ed-81ce-0242ac120003'] );
        $this->assertInstanceOf(GogCart::class, $cart);

        $cartHasProductsRepository = $this->entityManager->getRepository(GogCartHasProducts::class);
        $cartHasProducts = $cartHasProductsRepository->findBy( ['cart' => $cart->getId()] );
        $this->assertCount(3, $cartHasProducts);

        $cartHasProduct = $cartHasProductsRepository->findOneBy( ['cart' => $cart->getId(), 'product' => $product->getId()] );
        $this->assertInstanceOf(GogCartHasProducts::class, $cartHasProduct);
        $this->assertSame(2, $cartHasProduct->getQuantity());

        $this->checkResponseCode('GET', '/v1/api/cart/9999999', Response::HTTP_NOT_FOUND);
        $data = json_decode(
            $this->checkResponseJson(
                'GET',
                '/v1/api/cart/' . $cart->getIdSession(),
                Response::HTTP_OK
            )->getContent(),
            associative: true
        );

        $totalUSD = 0;
        foreach ($cartHasProducts as $cartHasProduct) {
            $product = $cartHasProduct->getProduct();
            $totalUSD += $product->getPrice() * $cartHasProduct->getQuantity();
        }
        $this->assertSame($data['total']['USD'], number_format($totalUSD / 100, 2));

    }

    public function testCartRemoveProduct(): void
    {
        $cartRepository = $this->entityManager->getRepository(GogCart::class);
        $cart = $cartRepository->findOneBy( ['idSession' => '588f895a-636e-11ed-81ce-0242ac120003'] );
        $this->assertInstanceOf(GogCart::class, $cart);

        $cartHasProductsRepository = $this->entityManager->getRepository(GogCartHasProducts::class);
        $cartHasProducts = $cartHasProductsRepository->findBy( ['cart' => $cart->getId()] );
        $this->assertCount(3, $cartHasProducts);

        $this->checkResponseCode('DELETE', '/v1/api/cart/9999999', Response::HTTP_NOT_FOUND);
        $this->checkJsonRequest('DELETE', '/v1/api/cart/' . $cart->getIdSession(), Response::HTTP_NOT_FOUND, [
            'id_product' => 9999999
        ]);
        $this->checkJsonRequest('DELETE', '/v1/api/cart/' . $cart->getIdSession(), Response::HTTP_BAD_REQUEST, [
            'id_product' => 'test'
        ]);
        $this->checkJsonRequest('DELETE', '/v1/api/cart/' . $cart->getIdSession(), Response::HTTP_BAD_REQUEST, [
            'id_product' => -1
        ]);

        foreach ($cartHasProducts as $cartHasProduct) {
            $this->checkJsonRequest('DELETE', '/v1/api/cart/' . $cart->getIdSession(), Response::HTTP_OK, [
                'id_product' => $cartHasProduct->getProduct()->getId()
            ]);
            $this->checkJsonRequest('DELETE', '/v1/api/cart/' . $cart->getIdSession(), Response::HTTP_NOT_FOUND, [
                'id_product' => $cartHasProduct->getProduct()->getId()
            ]);
        }
    }

}