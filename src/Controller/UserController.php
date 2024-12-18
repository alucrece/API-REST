<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\TextUI\XmlConfiguration\Group;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }
    #[Route('/api/users', name: 'app_users_list')]
    public function getAllUsers(UserRepository $userRepository, SerializerInterface $serializerInterface): JsonResponse
    {
        $userList = $userRepository->findAll();
        $json = $serializerInterface->serialize($userList, 'json', ['groups' => 'getUser']);
        return new JsonResponse(
            $json,
            Response::HTTP_OK,
            [],
            true
        );
    }
    #[Route('/api/user/{id}', name: 'app_user_by_id', methods: ['GET']),]
    public function getUserById(int $id, UserRepository $userRepository, SerializerInterface $serializerInterface): JsonResponse
    {
        $user = $userRepository->find($id);
        if ($user) {
            $json = $serializerInterface->serialize($user, 'json', ['groups' => 'getUser']);
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
    #[Route('/api/user/{id}', name: 'app_delete_user_by_id', methods: ['DELETE']),]
    public function deleteUserById(int $id, UserRepository $userRepository, EntityManagerInterface $em): JsonResponse
    {
        $user = $userRepository->find($id);
        if ($user) {
            $em->remove($user);
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
    #[Route('/api/register', name: 'register', methods: ['POST']),]
    public function register(Request $req, SerializerInterface $serializerInterface, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, UserPasswordHasherInterface $userPasswordHasher): JsonResponse
    {
        $user = $serializerInterface->deserialize($req->getContent(), User::class, 'json');
        $user->setPassword($userPasswordHasher->hashPassword($user, $user->getPassword()));
        $em->persist($user);
        $cart = new Cart();
        $cart->setConfirmed(false);
        $cart->setUserId($user);
        $em->persist($cart);
        $em->flush();
        $json = $serializerInterface->serialize($user, 'json');
        $location = $urlGenerator->generate('app_user_by_id', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse(
            $json,
            Response::HTTP_CREATED,
            ["Location" => $location],
            true
        );
    }
    #[Route('/api/user/{id}', name: "update_User_By_Id", methods: ['PUT'])]

    public function updateuser(Request $req, SerializerInterface $serializer, User $currentUser, EntityManagerInterface $em): JsonResponse
    {
        $updatedUser = $serializer->deserialize(
            $req->getContent(),
            User::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentUser]
        );
        $em->persist($updatedUser);
        $em->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
