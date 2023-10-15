<?php

namespace App\Controller;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Security;

class UserUpdateController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private ValidatorInterface $validator;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
        )
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->validator = $validator;

    }

    #[Route('/users/{id}/update_user', name: 'update_user', methods: ['POST'])]
    public function updateUser(Request $request, int $id , Security $security): Response
    {
        // Find the user by ID from the repository
        $user = $this->userRepository->find($id);
        // dd($user);
        // Check if the user exists
        if (!$user) {
            return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $authenticatedUser = $security->getUser();
        if ($authenticatedUser !== $user) {
            return $this->json(['code' => JsonResponse::HTTP_FORBIDDEN , 'message' => 'You are not authorized to update this user'], Response::HTTP_FORBIDDEN);
        }
        // Check and update user fields that are present in the request
        if ($request->request->has('email')) {
            
            $email = $request->request->get('email');
            if($this->userRepository->findOneBy(['email' => $email])){
                return new JsonResponse(["code" => JsonResponse::HTTP_CONFLICT, "message" => "email already exists :("], JsonResponse::HTTP_CONFLICT);

            }
            $user->setEmail($email);
        }
        if ($request->request->has('password')) {
            
            $password = $request->request->get('password');
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $password
            );
            $user->setPassword($hashedPassword);
        }
        if ($request->request->has('firstName')) {
            
            $firstName = $request->request->get('firstName');
            $user->setFirstName($firstName);
        }
        if ($request->request->has('lastName')) {

            $lastName = $request->request->get('lastName');
            $user->setLastName($lastName);
        }
        if ($request->request->has('bio')) {

            $bio = $request->request->get('bio');
            $user->setBio($bio);
        }
        if ($request->request->has('phoneNumber')) {

            $phoneNumber = $request->request->get('phoneNumber');
            $user->setPhoneNumber($phoneNumber);
        }


        // Handle image upload if an image file is included in the request
        if ($request->files->has('imageFile')) {
            $imageFile = $request->files->get('imageFile');
            $user->setImageFile($imageFile);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json(["code" => JsonResponse::HTTP_OK, 'message' => 'User updated successfully'], Response::HTTP_OK);
    }
}
