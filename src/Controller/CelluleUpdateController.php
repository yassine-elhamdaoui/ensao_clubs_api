<?php
// src/Controller/ImageUpdateController.php

namespace App\Controller;

use App\Entity\Cellule;
use App\Repository\CelluleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;

class CelluleUpdateController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private CelluleRepository $celluleRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        CelluleRepository $celluleRepository,
    ) {
        $this->entityManager = $entityManager;
        $this->celluleRepository = $celluleRepository;
    }

    #[Route('/cellules/{id}/update_cellule', name: 'update_cellule', methods: ['POST'])]
    public function updateCellule(Request $request, int $id , Security $security): Response
    {
        // Find the cellule by ID from the repository
        $cellule = $this->celluleRepository->find($id);
        // Check if the cellule exists
        if (!$cellule) {
            return $this->json(['message' => 'cellule not found'], Response::HTTP_NOT_FOUND);
        }
        $authenticatedUser = $security->getUser();
        if (!$this->isUserAuthorized($authenticatedUser, $cellule)) {
            return new JsonResponse(['code' => JsonResponse::HTTP_FORBIDDEN, 'message' => 'you are not authorized to update this cellule'], JsonResponse::HTTP_FORBIDDEN);
        }
        // Check and update user fields that are present in the request
        if ($request->request->has('name')) {
            $cellule->setName($request->request->get('name'));
        }
        if ($request->request->has('description')) {
            $cellule->setDescription($request->request->get('description'));
        }

        // Handle image upload if an image file is included in the request
        if ($request->files->has('imageFile')) {
            $imageFile = $request->files->get('imageFile');
            // Handle image upload here and update $user->setImageFile(...)
            $cellule->setImageFile($imageFile);
        }
        // dd($user);
        // Save the updated user entity to the database
        $this->entityManager->persist($cellule);
        $this->entityManager->flush();

        return $this->json(["code" => JsonResponse::HTTP_OK, 'message' => 'Cellule updated successfully'], Response::HTTP_OK);
    }
    private function isUserAuthorized($user, Cellule $cellule): bool
    {
        return ($user->getId() === $cellule->getRespo()->getId() || $user->getId() === $cellule->getClub()->getAdmin()->getId());
    }
}
