<?php
// src/Controller/RegistrationController.php
namespace App\Controller;

use App\Entity\UpcomingEvent;
use App\Entity\Club;
use App\Repository\ClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class EventsController
{
    private EntityManagerInterface $entityManager;
    private Security $security;
    private ClubRepository $clubRepository;


    public function __construct(
        EntityManagerInterface $entityManager,
        Security $security,
        ClubRepository $clubRepository,
    ) {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->clubRepository = $clubRepository;


    }
    public function __invoke(Request $request): UpcomingEvent | JsonResponse
    {
        $word = explode("/" , $request->request->get('club'));
        $id = end($word);
        $club = $this->clubRepository->find(intval($id));

        $authenticatedUser = $this->security->getUser();
        if (!$this->isUserAdminOfClub($authenticatedUser, $club)) {
            return new JsonResponse(['code' => JsonResponse::HTTP_FORBIDDEN, 'message' => 'you are not authorized to update this club'], JsonResponse::HTTP_FORBIDDEN);
        }

        $event = new UpcomingEvent();

        if (!$this->entityManager->getRepository(Club::class)->find($id)) {
            return new JsonResponse(['code' => JsonResponse::HTTP_NOT_FOUND, 'message' => 'club not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        if ($request->files->has('imageFile')) {
            $event->setImageFile($request->files->get('imageFile'));
        } else {
            return new JsonResponse(['code' => JsonResponse::HTTP_BAD_REQUEST, 'message' => 'you must provide a picture to complete posting'], JsonResponse::HTTP_BAD_REQUEST);
        }

        
        $event->setTitle($request->request->get('title'));
        $event->setClub($this->entityManager->getRepository(Club::class)->find($id));
        $event->setDescription($request->request->get('description'));;

        return $event;
    }
    private function isUserAdminOfClub($user, Club $club): bool
    {
        return $user->getId() === $club->getAdmin()->getId();
    }
}
