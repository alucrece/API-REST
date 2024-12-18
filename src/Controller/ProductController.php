<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_product')]
    public function index(): Response
    {
        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
        ]);
    }
    #[Route('/api/products', name: 'app_products_list')]
    public function getAllProducts(ProductRepository $productRepository, SerializerInterface $serializerInterface): JsonResponse
    {
        $productList = $productRepository->findAll();
        $json = $serializerInterface->serialize($productList, 'json', ['groups' => 'getProduct']);
        return new JsonResponse(
            $json,
            Response::HTTP_OK,
            [],
            true
        );
    }
    #[Route('/api/product/{id}', name: 'app_product_by_id', methods: ['GET']),]
    public function getProductById(int $id, ProductRepository $productRepository, SerializerInterface $serializerInterface): JsonResponse
    {
        $product = $productRepository->find($id);
        if ($product) {
            $json = $serializerInterface->serialize($product, 'json', ['groups' => 'getProduct']);
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
    #[Route('/api/product/{id}', name: 'app_delete_product_by_id', methods: ['DELETE']),]
    public function deleteProductById(int $id, ProductRepository $productRepository, EntityManagerInterface $em): JsonResponse
    {
        $product = $productRepository->find($id);
        if ($product) {
            $em->remove($product);
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
    #[Route('/api/product', name: 'app_create_product_by_id', methods: ['POST']),]
    public function createProductById(Request $req, SerializerInterface $serializerInterface, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $product = $serializerInterface->deserialize($req->getContent(), Product::class, 'json');
        $em->persist($product);
        $em->flush();
        $json = $serializerInterface->serialize($product, 'json', ["groups" => 'getProduct']);
        $location = $urlGenerator->generate('app_product_by_id', ['id' => $product->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse(
            $json,
            Response::HTTP_CREATED,
            ["Location" => $location],
            true
        );
    }
    #[Route('/api/product/{id}', name: "update_Product_By_Id", methods: ['PUT'])]

    public function updateproduct(Request $req, SerializerInterface $serializer, Product $currentProduct, EntityManagerInterface $em): JsonResponse
    {
        $updatedProduct = $serializer->deserialize(
            $req->getContent(),
            Product::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentProduct]
        );
        $em->persist($updatedProduct);
        $em->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
