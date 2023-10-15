<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\NotificationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ApiResource(
    itemOperations:[
        'get'
    ]
)]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read:user'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:user'])]
    private ?string $content = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: "create")]
    #[Groups(['read:user'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'sentNotifications')]
    // #[Groups(['read:user'])]
    private ?User $sender = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'receivedNotifications')]
    private Collection $receivers;

    #[ORM\ManyToOne(inversedBy: 'notifications')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read:user'])]
    private ?Club $club = null;

    #[ORM\ManyToOne(inversedBy: 'notifications')]
    #[Groups(['read:user'])]
    private ?Cellule $cellule = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:user'])]
    private ?string $status = null;

    #[ORM\OneToOne(inversedBy: 'notification', cascade: ['persist', 'remove'])]
    #[Groups(['read:user'])]
    private ?Request $request = null;

    public function __construct()
    {
        $this->receivers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(?User $sender): static
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getReceiver(): Collection
    {
        return $this->receivers;
    }

    public function addReceiver(User $receiver): static
    {
        if (!$this->receivers->contains($receiver)) {
            $this->receivers->add($receiver);
        }

        return $this;
    }

    public function removeReceiver(User $receiver): static
    {
        $this->receivers->removeElement($receiver);

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

    public function getCellule(): ?Cellule
    {
        return $this->cellule;
    }

    public function setCellule(?Cellule $cellule): static
    {
        $this->cellule = $cellule;

        return $this;
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

    public function getRequest(): ?Request
    {
        return $this->request;
    }

    public function setRequest(?Request $request): static
    {
        $this->request = $request;

        return $this;
    }
}
