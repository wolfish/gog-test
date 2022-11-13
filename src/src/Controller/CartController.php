<?php

namespace App\Controller;

use App\Constraints\CartConstraints;
use App\Constraints\CartProductConstraints;
use App\Constraints\CartRemoveProductConstraints;
use App\Entity\GogCart;
use App\Entity\GogCartHasProducts;
use App\Entity\GogProducts;
use App\Helper\InputHelper;
use App\Helper\ListHelper;
use App\Repository\GogCartHasProductsRepository;
use App\Repository\GogCartRepository;
use App\Repository\GogProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CartController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    private ValidatorInterface $validator;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    #[Route(
        '/v1/api/cart/{id_session}',
        name: 'v1_cart_list',
        methods: ['GET']
    )]
    #[OA\Get(
        path: '/v1/api/cart/{id_session}',
        description: 'Get products from cart',
        responses: [
            new OA\Response(response: 200, description: 'Correct response'),
            new OA\Response(response: 404, description: 'Cart not found'),
            new OA\Response(response: 500, description: 'Server error')
        ]
    )]
    #[OA\Tag('Cart')]
    public function productList(
        GogCartRepository $cartRepository,
        GogCartHasProductsRepository $cartHasProductsRepository,
        string $id_session
    ): Response
    {
        $response = new Response();

        $cart = $cartRepository->findOneBy([ 'idSession' => $id_session ]);
        if (!$cart instanceof GogCart) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            return $response->send();
        }

        $products = $cartHasProductsRepository->findBy( ['cart' => $cart->getId()] );
        return new JsonResponse(ListHelper::getCartProductsList($products));
    }

    #[Route('/v1/api/cart', name: 'v1_cart_post', methods: ['POST'])]
    #[OA\Post(
        path: '/v1/api/cart',
        description: 'Create cart',
        responses: [
            new OA\Response(response: 201, description: 'Cart created'),
            new OA\Response(response: 400, description: 'Input validation error'),
            new OA\Response(response: 409, description: 'Cart already exists'),
            new OA\Response(response: 500, description: 'Server error')
        ]
    )]
    #[OA\RequestBody(
        required: true,
        description: 'Cart data to create',
        content: [
            new OA\MediaType(
                'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'id_session',
                            type: 'string',
                            description: 'Cart user session id',
                            example: '588f895a-636e-11ed-81ce-0242ac120002'
                        )
                    ]
                )
            )
        ]
    )]
    #[OA\Tag('Cart')]
    public function cartCreate(
        Request $request,
        GogCartRepository $cartRepository
    ): Response
    {
        $response = new Response();
        $processed = $this->processInput($request, CartConstraints::getConstraints());
        if ($processed instanceof Response) {
            return $processed;
        }

        $exists = $cartRepository->findOneBy([ 'idSession' => $processed['id_session'] ]);
        if ($exists instanceof GogCart) {
            $response->setStatusCode(Response::HTTP_CONFLICT);
            return $response->send();
        }

        try {
            $this->saveCartData(new GogCart(), $processed);
        } catch (\Exception $e) {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            return $response->send();
        }

        $response->setStatusCode(Response::HTTP_CREATED);
        return $response->send();
    }

    #[Route('/v1/api/cart/{id_session}', name: 'v1_cart_product_post', methods: ['POST'])]
    #[OA\Post(
        path: '/v1/api/cart/{id_session}',
        description: 'Add product to existing cart',
        parameters: [
            new OA\Parameter(
                name: 'id_session',
                in: 'path',
                required: true,
                description: 'Session ID of existing cart',
                example: '588f895a-636e-11ed-81ce-0242ac120002'
            )
        ],
        responses: [
            new OA\Response(response: 201, description: 'Product added to cart'),
            new OA\Response(response: 200, description: 'Quantity of existing product updated'),
            new OA\Response(response: 400, description: 'Input validation error'),
            new OA\Response(response: 403, description: 'Maximum product count in cart exceeded'),
            new OA\Response(response: 404, description: 'Cart or product not found'),
            new OA\Response(response: 500, description: 'Server error')
        ]
    )]
    #[OA\RequestBody(
        required: true,
        description: 'Existing product added to cart in quantity',
        content: [
            new OA\MediaType(
                'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'id_product',
                            type: 'integer',
                            description: 'Product ID',
                            example: 1
                        ),
                        new OA\Property(
                            property: 'quantity',
                            type: 'integer',
                            description: 'Product quantity in cart',
                            example: 1
                        )
                    ]
                )
            )
        ]
    )]
    #[OA\Tag('Cart')]
    public function cartAddProduct(
        Request $request,
        GogCartRepository $cartRepository,
        GogProductsRepository $productsRepository,
        string $id_session
    ): Response
    {
        $response = new Response();

        $cart = $cartRepository->findOneBy(['idSession' => $id_session]);
        if (!$cart instanceof GogCart) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            return $response->send();
        }

        $processed = $this->processInput($request, CartProductConstraints::getConstraints());
        if ($processed instanceof Response) {
            return $processed;
        }

        $product = $productsRepository->find($processed['id_product']);
        if (!$product instanceof GogProducts) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            return $response->send();
        }

        try {
            $code = $this->addProductToCart($product, $cart, $processed['quantity']);
        } catch (\Exception $e) {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            return $response->send();
        }

        $response->setStatusCode($code);
        return $response->send();
    }

    #[Route(
        '/v1/api/cart/{id_session}',
        name: 'v1_cart_product_delete',
        methods: ['DELETE']
    )]
    #[OA\Delete(
        path: '/v1/api/cart/{id_session}',
        description: 'Remove product from existing cart',
        parameters: [
            new OA\Parameter(
                name: 'id_session',
                in: 'path',
                required: true,
                description: 'Session ID of existing cart',
                example: '588f895a-636e-11ed-81ce-0242ac120002'
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Product removed from cart'),
            new OA\Response(response: 400, description: 'Input validation error'),
            new OA\Response(response: 404, description: 'Cart or product not found'),
            new OA\Response(response: 500, description: 'Server error')
        ]
    )]
    #[OA\RequestBody(
        required: true,
        description: 'Existing product to remove from cart',
        content: [
            new OA\MediaType(
                'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'id_product',
                            type: 'integer',
                            description: 'Product ID',
                            example: 1
                        )
                    ]
                )
            )
        ]
    )]
    #[OA\Tag('Cart')]
    public function cartRemoveProduct(
        Request $request,
        GogCartRepository $cartRepository,
        string $id_session
    ): Response
    {
        $response = new Response();

        $cart = $cartRepository->findOneBy(['idSession' => $id_session]);
        if (!$cart instanceof GogCart) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            return $response->send();
        }

        $processed = $this->processInput($request, CartRemoveProductConstraints::getConstraints());
        if ($processed instanceof Response) {
            return $processed;
        }

        try {
            $code = $this->removeProductFromCart($processed['id_product'], $cart);
        } catch (\Exception $e) {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            return $response->send();
        }

        $response->setStatusCode($code);
        return $response->send();
    }

    private function processInput(Request $request, array $constraints): Response|array
    {
        $parameters = InputHelper::decodeInput($request);
        if (isset($parameters['error'])) {
            return new JsonResponse(array('errors' => $parameters['error']), 400);
        }

        $errors = InputHelper::validateInput(
            $parameters,
            new Collection($constraints),
            $this->validator
        );

        if (count($errors)) {
            return new JsonResponse(array('errors' => $errors), 400);
        }

        return $parameters;
    }

    private function saveCartData(GogCart $cart, array $data): void
    {
        $cart->setIdSession($data['id_session']);
        $this->entityManager->persist($cart);
        $this->entityManager->flush();
    }

    private function addProductToCart(GogProducts $product, GogCart $cart, int $quantity): int
    {
        $new = false;
        $code = Response::HTTP_OK;
        $cartHasProductsRepository = $this->entityManager->getRepository(GogCartHasProducts::class);
        $cartProduct = $cartHasProductsRepository->findOneBy([
            'cart' => $cart->getId(),
            'product' => $product->getId()
        ]);
        if (!$cartProduct instanceof GogCartHasProducts) {
            $productsAdded = $cartHasProductsRepository->findBy(['cart' => $cart->getId()]);
            if (count($productsAdded) === 3) {
                return Response::HTTP_FORBIDDEN;
            }

            $cartProduct = new GogCartHasProducts();
            $new = true;
            $code = Response::HTTP_CREATED;
        }

        $cartProduct->setQuantity($quantity);
        if ($new) {
            $cartProduct->setCart($cart);
            $cartProduct->setProduct($product);
        } else {
            $cartProduct->setUpdated(new \DateTime());
        }
        $this->entityManager->persist($cartProduct);
        $this->entityManager->flush();

        return $code;
    }

    private function removeProductFromCart(int $idProduct, GogCart $cart): int
    {
        $cartHasProductsRepository = $this->entityManager->getRepository(GogCartHasProducts::class);
        $cartProduct = $cartHasProductsRepository->findOneBy([
            'cart' => $cart->getId(),
            'product' => $idProduct
        ]);

        if (!$cartProduct instanceof GogCartHasProducts) {
            return Response::HTTP_NOT_FOUND;
        }

        $this->entityManager->remove($cartProduct);
        $this->entityManager->flush();

        return Response::HTTP_OK;
    }
}
