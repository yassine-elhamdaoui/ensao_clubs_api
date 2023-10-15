<?php
namespace App\Controller;
use App\Entity\Club;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\RedisTagAwareAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class GetClubsController extends AbstractController
{
    /**
     * @Route("/clubs/get_all_clubs", name="get_all_clubs", methods={"GET"})
     */
    public function getAllClubs(EntityManagerInterface $entityManager )
    {
        $client = RedisAdapter::createConnection('redis://localhost:6379');
        $cache = new RedisTagAwareAdapter($client);

        $clubs = $cache->get('find_all_clubs', function (ItemInterface $item) use ($entityManager) {
            // Retrieve data if not found in cache
            var_dump('miss');
            return $entityManager->getRepository(Club::class)->findAll();
        });
        // Serialize clubs to JSON
        $clubsArray = [];
        foreach ($clubs as $club) {
            $clubsArray[] = [
                'id' => $club->getId(),
                'name' => $club->getName(),
                'description' => $club->getDescription(),
            ];
        }

        return new JsonResponse($clubsArray , JsonResponse::HTTP_OK);
    }
}
