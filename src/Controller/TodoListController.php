<?php

namespace App\Controller;

use App\Entity\Cellule;
use App\Entity\TodoList;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class TodoListController
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->entityManager = $entityManager;
    }
    public function __invoke(Request $request): TodoList | JsonResponse
    {
        $todoList = new TodoList();
        $data = json_decode($request->getContent() ,true);
        $todoList->setTitle($data['title']);
        $todoList->setDescription($data['description']);
        $todoList->setDueDate(new DateTime($data['dueDate']));
        $userUrlParts = explode('/', $data['user']);
        $userId = end($userUrlParts);

        $celluleUrlParts = explode('/', $data['cellule']);
        $celluleId = end($celluleUrlParts);

        if (!$this->entityManager->getRepository(User::class)->find($userId)) {
            return new JsonResponse(['code' => JsonResponse::HTTP_NOT_FOUND, 'message' => 'user not found'], JsonResponse::HTTP_NOT_FOUND);
        }
        if (!$this->entityManager->getRepository(Cellule::class)->find($celluleId)) {
            return new JsonResponse(['code' => JsonResponse::HTTP_NOT_FOUND, 'message' => 'cellule not found'], JsonResponse::HTTP_NOT_FOUND);
        }
        $todoList->setUser($this->entityManager->getRepository(User::class)->find($userId));
        $todoList->setCellule($this->entityManager->getRepository(Cellule::class)->find($celluleId));

        return $todoList;
    }
}
