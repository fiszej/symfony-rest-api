<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class UserController extends AbstractController
{
    private $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @Route("/api/user/{id}", name="user", methods={"GET"})
     */
    public function show($id): Response
    {
        $user = $this->repository->find($id);
       
        
        if ($user == null) {
            return $this->json([
                'error' => 'not found'], JsonResponse::HTTP_NO_CONTENT);
        }

        $normalizer = new ObjectNormalizer();
        $encoder = new JsonEncoder();
        $serializer = new Serializer([$normalizer], [$encoder]);

        $userJson = $serializer->serialize($user, 'json');
       
        $response = new Response($userJson, JsonResponse::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    /**
     * @Route("/api/user", name="create_user", methods={"POST"})
     * 
     */
    public function create(Request $request): JsonResponse
    {

       
        $data = json_decode($request->getContent());

        if (!$data) {
            return $this->json([
                'error' => 'Cannot create user'], JsonResponse::HTTP_NO_CONTENT);
        }

        $email = $this->repository->findOneBy(['email' => $data->email]);
        if ($email !== null) {
            return $this->json([
                'error' => 'User already exists in DB']);
        }
        $user = new User();
        $entityManager = $this->getDoctrine()->getManager();
        $user->setFirstname($data->firstname);
        $user->setEmail($data->email);
        $user->setLastname($data->lastname);
        $user->setPassword($data->password);
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'The user was created.', 
            'user' => $data
        ], JsonResponse::HTTP_CREATED);
    }
    /**
     * @Route("/api/user/{id}", name="update_user", methods={"PUT"})
     */
    public function update($id, Request $request): JsonResponse
    {
        $user = $this->repository->find($id);

        if (!$user) {
            return $this->json([
                'error' => 'Cannot find user'], JsonResponse::HTTP_NO_CONTENT);
        }
        $data = json_decode($request->getContent());

        $entityManager = $this->getDoctrine()->getManager();
        $user->setFirstname($data->firstname);
        $user->setLastname($data->lastname);
        $user->setEmail($data->email);
        $user->setPassword($data->password);
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'The user was updated.', 
            'user' => $data
        ], JsonResponse::HTTP_OK);
    }

    /**
     * @Route("/api/user/{id}", name="delete_user", methods={"DELETE"} )
     */
    public function delete($id): JsonResponse
    {
        $user = $this->repository->find($id);

        if (!$user) {
            return $this->json([
                'error' => 'Cannot find user'], JsonResponse::HTTP_NO_CONTENT);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'The user was deleted.', 
        ], JsonResponse::HTTP_OK);
    }

     /**
     * @Route("/api/user", name="all_users", methods={"GET"})
     */
    public function all(): Response
    {
        $users = $this->repository->findAll();
        if (!$users) {
            return $this->json([
                'error' => 'not found'], JsonResponse::HTTP_NO_CONTENT);
        }

        $normalizer = new ObjectNormalizer();
        $encoder = new JsonEncoder();
        $serializer = new Serializer([$normalizer], [$encoder]);

        $userJson = $serializer->serialize($users, 'json');
       
        $response = new Response($userJson, JsonResponse::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    
}
