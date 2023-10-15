<?php

namespace App\Controller;

use App\Entity\Club;
use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ClubController
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->entityManager = $entityManager;
    }
    public function __invoke(Request $request): Club
    {
        $club = new Club();
        // dd($request->request->get('admin'));
        $club->setName($request->request->get('name'));
        $club->setDescription($request->request->get('description'));
        $club->setAdmin($this->entityManager->getRepository(User::class)->find($request->request->get('admin')));
        $club->setImageFile($request->files->get('imageFile'));
        return $club;
    }

    #[Route('/api/clubs/{id}/notification' , methods:['POST'] , name: 'notify_members')]
    public function notifyMembers(Request $request ,int $id) : JsonResponse
    {
        $club = $this->entityManager->getRepository(Club::class)->find($id);
        $message = json_decode($request->getContent(),true)['message'];

        $notification = new Notification();
        $notification->setClub($club);
        $notification->setContent($message); 
        $notification->setSender($club->getAdmin());
        $notification->setStatus('unread');

        $this->entityManager->persist($notification);

        foreach ($club->getMembers() as  $member){
            $member->addReceivedNotification($notification);
            $this->entityManager->persist($member);
        }
        $this->entityManager->flush();

        return new JsonResponse(['code'=> JsonResponse::HTTP_OK , 'message'=>'your message has been successfully delivered to all club members '],JsonResponse::HTTP_OK);

    }

}
