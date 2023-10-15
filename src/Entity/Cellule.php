<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\CelluleController;
use App\Repository\CelluleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;


#[ORM\Entity(repositoryClass: CelluleRepository::class)]
#[Vich\Uploadable]
#[ApiResource(
    normalizationContext: ['groups' => 'read:cellule'],
    collectionOperations: [
        'get',
        'post' => [
            'controller' => CelluleController::class,
            'deserialize' => false
        ]
    ],
    itemOperations:[
        'get',
        'delete',
        'update_cellule' => [
            'method' => 'POST',
            'path' => '/cellules/{id}/update_cellule',
            'controller' => 'App\Controller\CelluleUpdateController::updateCellule',
            'deserialize' => false
        ],
    ]
)]
class Cellule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read:cellule'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:cellule'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:cellule'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: "create")]
    #[Groups(['read:cellule'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: "update")]
    #[Groups(['read:cellule'])]
    private ?\DateTimeInterface $updatedAt = null;

    // NOTE: This is not a mapped field of entity metadata, just a simple property.
    #[Vich\UploadableField(mapping: 'cellule_images', fileNameProperty: 'imageName', size: 'imageSize')]
    private ?File $imageFile = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['read:cellule'])]
    private ?string $imageName = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['read:cellule'])]
    private ?int $imageSize = null;

    #[ORM\ManyToOne(inversedBy: 'cellules')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read:cellule'])]
    private ?Club $club = null;

    #[ORM\OneToMany(mappedBy: 'cellule', targetEntity: TodoList::class, orphanRemoval: true)]
    #[Groups(['read:cellule'])]
    private Collection $todoLists;

    #[ORM\ManyToOne(inversedBy: 'cellules')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read:cellule'])]
    private ?User $respo = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'memberIn_cellules')]
    #[Groups(['read:cellule'])]
    private Collection $members;

    #[ORM\OneToMany(mappedBy: 'cellule', targetEntity: Notification::class)]
    private Collection $notifications;

    #[ORM\OneToMany(mappedBy: 'cellule', targetEntity: Request::class)]
    private Collection $requests;

    public function __construct()
    {
        $this->todoLists = new ArrayCollection();
        $this->members = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->requests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }


    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }


    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageSize(?int $imageSize): void
    {
        $this->imageSize = $imageSize;
    }

    public function getImageSize(): ?int
    {
        return $this->imageSize;
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

    /**
     * @return Collection<int, TodoList>
     */
    public function getTodoLists(): Collection
    {
        return $this->todoLists;
    }

    public function addTodoList(TodoList $todoList): static
    {
        if (!$this->todoLists->contains($todoList)) {
            $this->todoLists->add($todoList);
            $todoList->setCellule($this);
        }

        return $this;
    }

    public function removeTodoList(TodoList $todoList): static
    {
        if ($this->todoLists->removeElement($todoList)) {
            // set the owning side to null (unless already changed)
            if ($todoList->getCellule() === $this) {
                $todoList->setCellule(null);
            }
        }

        return $this;
    }

    public function getRespo(): ?User
    {
        return $this->respo;
    }

    public function setRespo(?User $respo): static
    {
        $this->respo = $respo;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(User $member): static
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
        }

        return $this;
    }

    public function removeMember(User $member): static
    {
        $this->members->removeElement($member);

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setCellule($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getCellule() === $this) {
                $notification->setCellule(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Request>
     */
    public function getRequests(): Collection
    {
        return $this->requests;
    }

    public function addRequest(Request $request): static
    {
        if (!$this->requests->contains($request)) {
            $this->requests->add($request);
            $request->setCellule($this);
        }

        return $this;
    }

    public function removeRequest(Request $request): static
    {
        if ($this->requests->removeElement($request)) {
            // set the owning side to null (unless already changed)
            if ($request->getCellule() === $this) {
                $request->setCellule(null);
            }
        }

        return $this;
    }
}
