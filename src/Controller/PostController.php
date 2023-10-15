<?php
// src/Controller/RegistrationController.php
namespace App\Controller;

use App\Entity\Post;
use App\Entity\Club;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class PostController
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->entityManager = $entityManager;
    }
    public function __invoke(Request $request): Post | JsonResponse
    {
        $post = new Post();
        $club = $this->entityManager->getRepository(Club::class)->find($request->request->get('club'));
        $publisher = $this->entityManager->getRepository(User::class)->find($request->request->get('publisher'));
        if (!$publisher) {
            return new JsonResponse(['code' => JsonResponse::HTTP_NOT_FOUND , 'message' => 'user not found'] ,JsonResponse::HTTP_NOT_FOUND);
        }
        if (!$club) {
            return new JsonResponse(['code' => JsonResponse::HTTP_NOT_FOUND, 'message' => 'club not found'], JsonResponse::HTTP_NOT_FOUND);
        }
        
        if ($publisher != $club->getAdmin()) {
            return new JsonResponse(['code' => JsonResponse::HTTP_UNAUTHORIZED, 'message' => 'just the admin of the club could publish a post !!'], JsonResponse::HTTP_UNAUTHORIZED);
        }
        if ($request->files->has('imageFile')) {
            $post->setImageFile($request->files->get('imageFile'));
        }else{
            return new JsonResponse(['code' => JsonResponse::HTTP_BAD_REQUEST, 'message' => 'you must provide a picture to complete posting'], JsonResponse::HTTP_BAD_REQUEST);
        }
        
        $post->setTitle($request->request->get('title'));
        $post->setCaption($request->request->get('caption'));
        $post->setPublisher($publisher);
        $post->setClub($club);

        return $post;
    }
}
