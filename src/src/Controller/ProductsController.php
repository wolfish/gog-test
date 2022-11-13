<?php

namespace App\Controller;

use App\Constraints\PaginatorConstraints;
use App\Constraints\ProductConstraints;
use App\Helper\InputHelper;
use App\Helper\ListHelper;
use App\Entity\GogCurrency;
use App\Entity\GogProducts;
use App\Repository\GogCurrencyRepository;
use App\Repository\GogProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;

class ProductsController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    private ValidatorInterface $validator;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    #[Route(
        '/v1/api/product/{page}',
        name: 'v1_product_list',
        defaults: [ 'page' => 1 ],
        methods: ['GET']
    )]
    #[OA\Get(
        path: '/v1/api/product/{page}',
        description: 'Get products list page',
        parameters: [
            new OA\Parameter(
                name: 'page',
                in: 'path',
                required: true,
                description: 'Page number',
                example: '1'
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Correct response'),
            new OA\Response(response: 400, description: 'Input validation error'),
            new OA\Response(response: 500, description: 'Server error')
        ]
    )]
    #[Route(
        '/v1/api/product',
        name: 'v1_product_list_default',
        methods: ['GET']
    )]
    #[OA\Get(
        path: '/v1/api/product',
        description: 'Get products list first page',
        responses: [
            new OA\Response(response: 200, description: 'Correct response'),
            new OA\Response(response: 500, description: 'Server error')
        ]
    )]
    #[OA\Tag('Products')]
    public function productList(GogProductsRepository $productsRepository, int $page): Response
    {
        $errors = InputHelper::validateInput(
            [ 'page' => $page ],
            new Collection(PaginatorConstraints::getConstraints()),
            $this->validator
        );
        if (count($errors)) {
            return new JsonResponse(array('errors' => $errors), 400);
        }

        $productsPerPage = $this->getParameter('app.products_per_page');
        $products = $productsRepository->findBy([], limit: $productsPerPage, offset: abs($page-1)*$productsPerPage);
        return new JsonResponse(ListHelper::getProductList($products));
    }

    #[Route(
        '/v1/api/product/{id}',
        name: 'v1_product_delete',
        methods: ['DELETE']
    )]
    #[OA\Delete(
        path: '/v1/api/product/{id}',
        description: 'Delete the product',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID of a product to delete',
                example: '1'
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Product deleted'),
            new OA\Response(response: 404, description: 'Product not found')
        ]
    )]
    #[OA\Tag('Products')]
    public function productDelete(ValidatorInterface $validator, ?GogProducts $product = null): Response
    {
        $response = new Response();

        if (!$product instanceof GogProducts) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            return $response->send();
        }

        $this->entityManager->remove($product);
        $this->entityManager->flush();
        
        $response->setStatusCode(Response::HTTP_OK);
        return $response->send();
    }

    #[Route(
        '/v1/api/product/{id}',
        name: 'v1_product_put',
        methods: ['PUT']
    )]
    #[OA\Put(
        path: '/v1/api/product/{id}',
        description: 'Update the product',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID of a product to update',
                example: '1'
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Product updated'),
            new OA\Response(response: 400, description: 'Input validation error'),
            new OA\Response(response: 404, description: 'Product not found'),
            new OA\Response(response: 409, description: 'Product with same title already exists'),
            new OA\Response(response: 500, description: 'Server error')
        ]
    )]
    #[OA\RequestBody(
        required: true,
        description: 'Product data for update',
        content: [
            new OA\MediaType(
                'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'title', type: 'string', example: 'Gothic', description: 'Product title'),
                        new OA\Property(property: 'price', type: 'integer', example: '100', description: 'Product price in lowest denominator'),
                        new OA\Property(property: 'currency', type: 'string', example: 'USD', description: 'Product currency in ISO 4217')
                    ]
                )
            )
        ]
    )]
    #[OA\Tag('Products')]
    public function productUpdate(
        Request $request,
        GogCurrencyRepository $currencyRepository,
        GogProductsRepository $productsRepository,
        ?GogProducts $product = null
    ): Response
    {
        $response = new Response();

        if (!$product instanceof GogProducts) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            return $response->send();
        }

        $processed = $this->processInput($request, $currencyRepository);
        if ($processed instanceof Response) {
            return $processed;
        }

        if (strtolower($processed['title']) !== strtolower($product->getTitle())) {
            $exists = $this->checkIfProductExists($processed['title'], $productsRepository);
            if ($exists instanceof GogProducts) {
                $response->setStatusCode(Response::HTTP_CONFLICT);
                return $response->send();
            }
        }

        try {
            $this->saveProductData($product, $processed);
        } catch (\Exception $e) {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            return $response->send();
        }
        
        $response->setStatusCode(Response::HTTP_OK);
        return $response->send();
    }

    #[Route(
        '/v1/api/product',
        name: 'v1_product_post',
        methods: ['POST']
    )]
    #[OA\Post(
        path: '/v1/api/product',
        description: 'Create product',
        responses: [
            new OA\Response(response: 201, description: 'Product created'),
            new OA\Response(response: 400, description: 'Input validation error'),
            new OA\Response(response: 409, description: 'Product already exists'),
            new OA\Response(response: 500, description: 'Server error')
        ]
    )]
    #[OA\RequestBody(
        required: true,
        description: 'Product data to create',
        content: [
            new OA\MediaType(
                'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'title', type: 'string', example: 'Title', description: 'Product title'),
                        new OA\Property(property: 'price', type: 'integer', example: '100', description: 'Product price in lowest denominator'),
                        new OA\Property(property: 'currency', type: 'string', example: 'PLN', description: 'Product currency in ISO 4217')
                    ]
                )
            )
        ]
    )]
    #[OA\Tag('Products')]
    public function productCreate(
        Request $request,
        GogCurrencyRepository $currencyRepository,
        GogProductsRepository $productsRepository
    ): Response
    {
        $response = new Response();

        $processed = $this->processInput($request, $currencyRepository);
        if ($processed instanceof Response) {
            return $processed;
        }

        $exists = $this->checkIfProductExists($processed['title'], $productsRepository);
        if ($exists instanceof GogProducts) {
            $response->setStatusCode(Response::HTTP_CONFLICT);
            return $response->send();
        }

        try {
            $this->saveProductData(new GogProducts(), $processed, true);
        } catch (\Exception $e) {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            return $response->send();
        }

        $response->setStatusCode(Response::HTTP_CREATED);
        return $response->send();
    }

    private function processInput(Request $request, GogCurrencyRepository $currencyRepository): Response|array
    {
        $response = new Response();
        $parameters = InputHelper::decodeInput($request);
        if (isset($parameters['error'])) {
            return new JsonResponse(array('errors' => $parameters['error']), 400);
        }

        $errors = InputHelper::validateInput(
            $parameters,
            new Collection(ProductConstraints::getConstraints()),
            $this->validator
        );

        if (count($errors)) {
            return new JsonResponse(array('errors' => $errors), 400);
        }

        $currency = $currencyRepository->findOneBy(['shortName' => $parameters['currency']]);
        if (!$currency instanceof GogCurrency) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            return $response->send();
        }
        $parameters['currency'] = $currency;

        return $parameters;
    }

    private function saveProductData(GogProducts $product, array $data, $new = false): void
    {
        $product->setCurrency($data['currency']);
        $product->setTitle($data['title']);
        $product->setPrice($data['price']);
        if(!$new) {
            $product->setUpdated(new \DateTime());
        }
        $this->entityManager->persist($product);
        $this->entityManager->flush();
    }

    private function checkIfProductExists(string $title, GogProductsRepository $productsRepository): ?GogProducts
    {
        return $productsRepository->findOneBy([ 'title' => $title ]);
    }
}
