<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;


class OrderController extends AbstractController
{
    #[Route('/order', name: 'app_order')]
    public function index(): Response
    {
        return $this->render('order/index.html.twig', [
            'controller_name' => 'OrderController',
        ]);
    }

    #[Route('/api/orders', name: 'app_orders_list')]
    public function getAllOrders(OrderRepository $orderRepository, SerializerInterface $serializerInterface): JsonResponse
    {
        $orderList = $orderRepository->findAll();
        $json = $serializerInterface->serialize($orderList, 'json', ['groups' => 'getProduct']);
        return new JsonResponse(
            $json,
            Response::HTTP_OK,
            [],
            true
        );
    }
    #[Route('/api/orders/{id}', name: 'app_order_by_id', methods: ['GET']),]
    public function getOrderById(int $id, OrderRepository $orderRepository, SerializerInterface $serializerInterface): JsonResponse
    {
        $order = $orderRepository->find($id);
        if ($order) {
            $json = $serializerInterface->serialize($order, 'json', ['groups' => 'getProduct']);
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
    #[Route('/api/order/{id}', name: 'app_delete_order_by_id', methods: ['DELETE']),]
    public function deleteOrderById(int $id, OrderRepository $orderRepository, EntityManagerInterface $em): JsonResponse
    {
        $order = $orderRepository->find($id);
        if ($order) {
            $em->remove($order);
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
    #[Route('/api/order', name: 'app_create_order_by_id', methods: ['POST']),]
    public function createOrderById(Request $req, SerializerInterface $serializerInterface, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $order = $serializerInterface->deserialize($req->getContent(), Order::class, 'json');
        $em->persist($order);
        $em->flush();
        $json = $serializerInterface->serialize($order, 'json', ["groups" => 'getProduct']);
        $location = $urlGenerator->generate('app_order_by_id', ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse(
            $json,
            Response::HTTP_CREATED,
            ["Location" => $location],
            true
        );
    }
    #[Route('/api/order/{id}', name: "update_Order_By_Id", methods: ['PUT'])]

    public function updateOrder(Request $req, SerializerInterface $serializer, Order $currentOrder, EntityManagerInterface $em): JsonResponse
    {
        $updatedOrder = $serializer->deserialize(
            $req->getContent(),
            Order::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentOrder]
        );
        $em->persist($updatedOrder);
        $em->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
