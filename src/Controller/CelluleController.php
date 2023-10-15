<?php
// src/Controller/RegistrationController.php
namespace App\Controller;

use App\Entity\Cellule;
use App\Entity\Club;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CelluleController
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->entityManager = $entityManager;
    }
    public function __invoke(Request $request): Cellule | JsonResponse
    {
        $cellule = new Cellule();
        $cellule->setName($request->request->get('name'));
        if (!$this->entityManager->getRepository(User::class)->find($request->request->get('respo'))) {
            return new JsonResponse(['code' => JsonResponse::HTTP_NOT_FOUND, 'message' => 'user not found'], JsonResponse::HTTP_NOT_FOUND);
        }
        $cellule->setRespo($this->entityManager->getRepository(User::class)->find($request->request->get('respo')));
        if (!$this->entityManager->getRepository(Club::class)->find($request->request->get('club'))) {
            return new JsonResponse(['code' => JsonResponse::HTTP_NOT_FOUND, 'message' => 'club not found'], JsonResponse::HTTP_NOT_FOUND);
        }
        $cellule->setClub($this->entityManager->getRepository(Club::class)->find($request->request->get('club')));
        $cellule->setDescription($request->request->get('description'));;
        if ($request->files->has('imageFile')) {
            $cellule->setImageFile($request->files->get('imageFile'));
        } else {
            return new JsonResponse(['code' => JsonResponse::HTTP_BAD_REQUEST, 'message' => 'you must provide a picture to complete posting'], JsonResponse::HTTP_BAD_REQUEST);
        }

        return $cellule;
    }
}
