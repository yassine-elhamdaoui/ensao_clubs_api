<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\RequestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Controller\RequestController;


#[ORM\Entity(repositoryClass: RequestRepository::class)]
#[ApiResource(
    collectionOperations:[
        'get',
        'post' => [
            'controller' => RequestController::class,
            'deserialize' => false
        ] 
    ],
    itemOperations:[
        'get',
        'delete',
        'accept_member' => [
            'method' => 'POST',
            'path' => '/requests/{id}/accept_member',
            'controller' => 'App\Controller\RequestController::acceptUser',
            'deserialize' => false,
            'security' => 'is_granted("ROLE_ADMIN")'
        ],
        'eject_member' => [
            'method' => 'POST',
            'path' => '/requests/{id}/eject_member',
            'controller' => 'App\Controller\RequestController::ejectUser',
            'deserialize' => false,
            'security' => 'is_granted("ROLE_ADMIN")'
        ]
    ]
)]
class Request
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'requests')]
    private ?User $owner = null;

    #[ORM\ManyToOne(inversedBy: 'requests')]
    private ?Club $club = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: "create")]
    private ?\DateTimeInterface $sentAt = null;

    #[ORM\Column(length: 255)]
    private ?string $status = "pending";

    #[ORM\ManyToOne(inversedBy: 'requests')]
    private ?Cellule $cellule = null;

    #[ORM\OneToOne(mappedBy: 'request', cascade: ['persist', 'remove'])]
    private ?Notification $notification = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getClub(): ?Club
    {
        return $this->club;
    }

    public function setClub(?Club $club): static
    {
        $this->club = $club;

        return $this;
    }

    public function getSentAt(): ?\DateTimeInterface
    {
        return $this->sentAt;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCellule(): ?Cellule
    {
        return $this->cellule;
    }

    public function setCellule(?Cellule $cellule): static
    {
        $this->cellule = $cellule;

        return $this;
    }

    public function getNotification(): ?Notification
    {
        return $this->notification;
    }

    public function setNotification(?Notification $notification): static
    {
        // unset the owning side of the relation if necessary
        if ($notification === null && $this->notification !== null) {
            $this->notification->setRequest(null);
        }

        // set the owning side of the relation if necessary
        if ($notification !== null && $notification->getRequest() !== $this) {
            $notification->setRequest($this);
        }

        $this->notification = $notification;

        return $this;
    }



}
