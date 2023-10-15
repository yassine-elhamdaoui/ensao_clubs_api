<?php
// src/Controller/ImageUpdateController.php

namespace App\Controller;

use App\Entity\Club;
use App\Entity\User;
use App\Repository\ClubRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;

class ClubUpdateController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ClubRepository $clubRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ClubRepository $clubRepository,
    ) {
        $this->entityManager = $entityManager;
        $this->clubRepository = $clubRepository;
    }

    #[Route('/clubs/{id}/update_club', name: 'update_club', methods: ['POST'])]
    public function updateClub(Request $request, int $id ,Security $security): JsonResponse
    {
        
        // Find the club by ID from the repository
        $club = $this->clubRepository->find($id);

        // Check if the authenticated user is the admin of the club
        $authenticatedUser = $security->getUser();
        if (!$this->isUserAdminOfClub($authenticatedUser, $club)) {
            return new JsonResponse(['code' => JsonResponse::HTTP_FORBIDDEN , 'message' => 'you are not authorized to update this club'] , JsonResponse::HTTP_FORBIDDEN);
        }
        // Check and update user fields that are present in the request
        if ($request->request->has('name')) {
            $club->setName($request->request->get('name'));
        }
        if ($request->request->has('description')) {
            $club->setDescription($request->request->get('description'));
        }
        if ($request->request->has('members')) {
            // dd($request->request->get('members'));
            $word = explode('/', $request->request->get('members'));
            $memberId = end($word);
            $member_user = $this->entityManager->getRepository(User::class)->find($memberId);
            // dd($member_user);
            $club->addMember($member_user);
        }

        // Handle image upload if an image file is included in the request
        if ($request->files->has('imageFile')) {
            $imageFile = $request->files->get('imageFile');
            // Handle image upload here and update $user->setImageFile(...)
            $club->setImageFile($imageFile);
        }
        // dd($user);
        // Save the updated user entity to the database
        $this->entityManager->persist($club);
        $this->entityManager->flush();

        return $this->json(["code" => JsonResponse::HTTP_OK, 'message' => 'Club updated successfully'], Response::HTTP_OK);
    }

    
    private function isUserAdminOfClub($user, Club $club): bool
    {
        return $user->getId() === $club->getAdmin()->getId();
    }
}
