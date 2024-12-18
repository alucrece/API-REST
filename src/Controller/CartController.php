<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Product;
use App\Entity\Order;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(): Response
    {
        return $this->render('cart/index.html.twig', [
            'controller_name' => 'CartController',
        ]);
    }
    #[Route('/api/carts', name: 'app_carts_list')]
    public function getAllCarts(CartRepository $cartRepository, SerializerInterface $serializerInterface): JsonResponse
    {
        $cartList = $cartRepository->findAll();
        $json = $serializerInterface->serialize($cartList, 'json', ['groups' => 'getProduct']);
        return new JsonResponse(
            $json,
            Response::HTTP_OK,
            [],
            true
        );
    }
    #[Route('/api/cart/{id}', name: 'app_cart_by_id', methods: ['GET']),]
    public function getCartById(int $id, CartRepository $cartRepository, SerializerInterface $serializerInterface): JsonResponse
    {
        $cart = $cartRepository->find($id);
        if ($cart) {
            $json = $serializerInterface->serialize($cart, 'json', ['groups' => 'getProduct']);
            return new JsonResponse(
                $json,
                Response::HTTP_OK,
                [],
                true
            );
        }
        return new JsonResponse(
            null,
            Response::HTTP_NOT_FOUND
        );
    }
    #[Route('/api/cart/{id}', name: 'app_delete_cart_by_id', methods: ['DELETE']),]
    public function deleteCartById(int $id, CartRepository $cartRepository, EntityManagerInterface $em): JsonResponse
    {
        $cart = $cartRepository->find($id);
        if ($cart) {
            $em->remove($cart);
            $em->flush();
            return new JsonResponse(
                null,
                Response::HTTP_NO_CONTENT
            );
        }
        return new JsonResponse(
            null,
            Response::HTTP_NOT_FOUND
        );
    }
    #[Route('/api/cart', name: 'app_create_cart_by_id', methods: ['POST']),]
    public function createCartById(Request $req, SerializerInterface $serializerInterface, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $cart = $serializerInterface->deserialize($req->getContent(), Cart::class, 'json');
        $em->persist($cart);
        $em->flush();
        $json = $serializerInterface->serialize($cart, 'json', ["groups" => 'getProduct']);
        $location = $urlGenerator->generate('app_cart_by_id', ['id' => $cart->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse(
            $json,
            Response::HTTP_CREATED,
            ["Location" => $location],
            true
        );
    }
    #[Route('/api/cart/{id}', name: "update_Cart_By_Id", methods: ['PUT'])]

    public function updateCart(Request $req, SerializerInterface $serializer, Cart $currentCart, EntityManagerInterface $em): JsonResponse
    {
        $updatedCart = $serializer->deserialize(
            $req->getContent(),
            Cart::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentCart]
        );
        $em->persist($updatedCart);
        $em->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
    #[Route('/api/cart/validate/{id}', name: "validate", methods: ['PATCH'])]

    public function validateCart(Request $req, SerializerInterface $serializer, Cart $currentCart, EntityManagerInterface $em): JsonResponse
    {
        $updatedCart = $serializer->deserialize(
            $req->getContent(),
            Cart::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentCart]
        );
        $em->persist($updatedCart);
        $em->flush();
        $updatedCart->setConfirmed(true);
        $em->flush();

        $order = new Order();
        $totalPrice = 0;
        foreach ($updatedCart->getProducts() as $product) {
            $totalPrice += $product->getPrice();
        }
        $order->setTotalPrice($totalPrice);
        $order->setCreationDate(new \DateTime());
        $order->setCartId($updatedCart);
        $updatedCart->setOrderId($order);

        $em->persist($order);
        $cart = new Cart();
        $cart->setConfirmed(false);
        $cart->setUserId($currentCart->getUserId());
        $em->persist($cart);
        $em->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route('/api/cart/{productId}', name: 'app_create_cart_by_id', methods: ['POST']),]
    public function addToCart(Request $request, int $productId, EntityManagerInterface $em, CartRepository $cartRepository): JsonResponse
    {

        $cartId = null;

        // Vérifier si la requête est au format JSON
        if ($request->getContentType() === 'json') {
            // Décoder le contenu JSON en objet Cart
            $data = json_decode($request->getContent(), true);
            $cartId = $data['id'];
        } else {
            // La requête est probablement un formulaire
            $cartId = $request->request->get('id');
        }
        if (!$cartId) {
            return new JsonResponse(['message' => 'Cart ID is required.'], 400);
        }

        $product = $em->getRepository(Product::class)->find($productId);
        if (!$product) {
            return new JsonResponse(['message' => 'Product not found.'], 404);
        }

        /** @var Cart|null $cart */
        $cart = $cartRepository->find($cartId);
        if (!$cart) {
            return new JsonResponse(['message' => 'Cart not found.'], 404);
        }

        $cart->addProduct($product);
        $em->flush();

        return new JsonResponse(['message' => 'Product added to cart.'], 200);
    }
    #[Route('/api/cart/{productId}', name: 'app_create_cart_by_id', methods: ['DELETE']),]
    public function DeleteToCart(Request $request, int $productId, EntityManagerInterface $em, CartRepository $cartRepository): JsonResponse
    {

        $cartId = null;

        // Vérifier si la requête est au format JSON
        if ($request->getContentType() === 'json') {
            // Décoder le contenu JSON en objet Cart
            $data = json_decode($request->getContent(), true);
            $cartId = $data['id'];
        } else {
            // La requête est probablement un formulaire
            $cartId = $request->request->get('id');
        }
        if (!$cartId) {
            return new JsonResponse(['message' => 'Cart ID is required.'], 400);
        }

        $product = $em->getRepository(Product::class)->find($productId);
        if (!$product) {
            return new JsonResponse(['message' => 'Product not found.'], 404);
        }

        /** @var Cart|null $cart */
        $cart = $cartRepository->find($cartId);
        if (!$cart) {
            return new JsonResponse(['message' => 'Cart not found.'], 404);
        }

        $cart->removeProduct($product);
        $em->flush();

        return new JsonResponse(['message' => 'Product removed  to cart.'], 200);
    }
}
