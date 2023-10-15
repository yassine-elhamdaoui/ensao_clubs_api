<?php 
namespace App\Controller;

use App\Entity\Cellule;
use App\Entity\Club;
use App\Entity\Notification;
use App\Entity\Request as EntityRequest;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RequestController 
{
    private  EntityManagerInterface $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    public function  __invoke(Request $request) : EntityRequest | JsonResponse
    {
        $data = json_decode($request->getContent() ,true);
        $owner = $data['owner'];
        $club = $data['club'];
        
        $word = explode('/', $club);
        $clubId = end($word);
        
        $word = explode('/', $owner);
        $ownerId = end($word);
        
        $clubEntity = $this->em->getRepository(Club::class)->find(intval($clubId));
        $ownerEntity = $this->em->getRepository(User::class)->find(intval($ownerId));
        
        if (!$ownerEntity) {
            return new JsonResponse(["code" => JsonResponse::HTTP_NOT_FOUND , "message" => "user not found"]);
        }
        
        if (!$clubEntity) {
            return new JsonResponse(["code" => JsonResponse::HTTP_NOT_FOUND , "message" => "club not found"]);
        }
        
        $joinRequest = new EntityRequest();
        $joinRequest->setClub($clubEntity);
        $joinRequest->setOwner($ownerEntity);
        
        $notification = new Notification();
        $notification->setClub($clubEntity);
        $notification->setContent("a user has sent u a request");
        $notification->setSender($ownerEntity);
        $notification->setStatus("unread");
        $notification->addReceiver($this->em->getRepository(User::class)->find($clubEntity->getAdmin()));

        $this->em->persist($notification);
        $this->em->flush();

        return $joinRequest;



    }

    #[Route('/api/requests/{id}/accept_member', name: 'accept_member' ,methods: ['POST'])]
    public function acceptUser(int $id) : JsonResponse
    {
        $requestEntity = $this->em->getRepository(EntityRequest::class)->find(intval($id));
        $newMemberId = $requestEntity->getOwner()->getId();
        $clubId = $requestEntity->getClub()->getId();

        $newMember = $this->em->getRepository(User::class)->find(intval($newMemberId));

        if (!$newMember) {
            return new JsonResponse(["code" => JsonResponse::HTTP_NOT_FOUND, "message" => "user not found"]);
        }
        $clubEntity = $this->em->getRepository(Club::class)->find(intval($clubId));

        if (!$clubEntity) {
            return new JsonResponse(["code" => JsonResponse::HTTP_NOT_FOUND, "message" => "club not found"]);
        }

        $clubEntity->addMember($newMember);
        $this->em->persist($clubEntity);
        $this->em->flush();

        return new JsonResponse(['code'=> JsonResponse::HTTP_ACCEPTED , 'message' => 'the user has been added as a member to the club'] , JsonResponse::HTTP_ACCEPTED);


    }

    #[Route('/api/requests/{id}/eject_member', name: 'eject_member' ,methods: ['POST'])]
    public function ejectUser(int $id) : JsonResponse
    {
        $requestEntity = $this->em->getRepository(EntityRequest::class)->find(intval($id));


        $this->em->remove($requestEntity);
        $this->em->remove($requestEntity->getNotification());
        $this->em->flush();

        return new JsonResponse(['code'=> JsonResponse::HTTP_ACCEPTED , 'message' => 'request has been denied'] , JsonResponse::HTTP_ACCEPTED);

    }
}
