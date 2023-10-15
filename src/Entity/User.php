<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\RegistrationController;
use App\Controller\UserUpdateController;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Ramsey\Uuid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[Vich\Uploadable]
#[ApiResource(
    normalizationContext:['groups' => 'read:user'],
    collectionOperations:[
        'get',
        'post' => [
            'controller' => RegistrationController::class,
            'deserialize' => false
        ]
    ],
    itemOperations:[
        'get',
        'delete',
        'update_user' => [
            'method' => 'POST',
            'path' => '/users/{id}/update_user',
            'controller' => 'App\Controller\UserUpdateController::updateUser',
            'deserialize' => false
        ],

    ]
    
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read:user'])]
    private ?int $id = null;

    #[Groups(['read:user', 'read:post'])]
    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[Groups(['read:user'])]
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[Groups(['read:user'])]
    #[ORM\Column(length: 255)]
    private ?string $phoneNumber = null;

    #[Groups(['read:user', 'read:post'])]
    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[Groups(['read:user', 'read:post'])]
    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[Groups(['read:user'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: "create")]
    private ?\DateTimeInterface $createdAt = null;

    #[Groups(['read:user'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: "update")]
    private ?\DateTimeInterface $updatedAt = null;

    // NOTE: This is not a mapped field of entity metadata, just a simple property.
    #[Vich\UploadableField(mapping: 'user_images', fileNameProperty: 'imageName', size: 'imageSize')]
    // #[Groups(['read:user'])]
    private ?File $imageFile = null;

    #[Groups(['read:user', 'read:post'])]
    #[ORM\Column(nullable: true)]
    private ?string $imageName = null;

    #[Groups(['read:user'])]
    #[ORM\Column(nullable: true)]
    private ?int $imageSize = null;
    
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: TodoList::class, orphanRemoval: true)]
    #[Groups(['read:user'])]
    private Collection $todoLists;

    #[ORM\OneToMany(mappedBy: 'respo', targetEntity: Cellule::class)]
    #[Groups(['read:user'])]
    private Collection $cellules;

    #[ORM\ManyToMany(targetEntity: Cellule::class, mappedBy: 'members')]
    #[Groups(['read:user'])]
    private Collection $memberIn_cellules;

    #[ORM\OneToMany(mappedBy: 'admin', targetEntity: Club::class)]
    #[Groups(['read:user'])]
    private Collection $clubs;

    #[ORM\ManyToMany(targetEntity: Club::class, mappedBy: 'members')]
    #[Groups(['read:user'])]
    private Collection $memberIn_clubs;

    #[ORM\OneToMany(mappedBy: 'publisher', targetEntity: Post::class)]
    #[Groups(['read:user'])]
    private Collection $posts;

    #[ORM\OneToMany(mappedBy: 'commenter', targetEntity: Comments::class)]
    #[Groups(['read:user'])]
    private Collection $comments;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Request::class)]
    #[Groups(['read:user'])]
    private Collection $requests;

    #[ORM\OneToMany(mappedBy: 'sender', targetEntity: Notification::class)]
    #[Groups(['read:user'])]
    private Collection $sentNotifications;

    #[ORM\ManyToMany(targetEntity: Notification::class, mappedBy: 'receivers')]
    #[Groups(['read:user'])]
    private Collection $receivedNotifications;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bio = null;




    public function __construct()
    {
        $this->todoLists = new ArrayCollection();
        $this->cellules = new ArrayCollection();
        $this->memberIn_cellules = new ArrayCollection();
        $this->clubs = new ArrayCollection();
        $this->memberIn_clubs = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->requests = new ArrayCollection();
        $this->sentNotifications = new ArrayCollection();
        $this->receivedNotifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

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
            $todoList->setUser($this);
        }

        return $this;
    }

    public function removeTodoList(TodoList $todoList): static
    {
        if ($this->todoLists->removeElement($todoList)) {
            // set the owning side to null (unless already changed)
            if ($todoList->getUser() === $this) {
                $todoList->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Cellule>
     */
    public function getCellules(): Collection
    {
        return $this->cellules;
    }

    public function addCellule(Cellule $cellule): static
    {
        if (!$this->cellules->contains($cellule)) {
            $this->cellules->add($cellule);
            $cellule->setRespo($this);
        }

        return $this;
    }

    public function removeCellule(Cellule $cellule): static
    {
        if ($this->cellules->removeElement($cellule)) {
            // set the owning side to null (unless already changed)
            if ($cellule->getRespo() === $this) {
                $cellule->setRespo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Cellule>
     */
    public function getMemberInCellules(): Collection
    {
        return $this->memberIn_cellules;
    }

    public function addMemberInCellule(Cellule $memberInCellule): static
    {
        if (!$this->memberIn_cellules->contains($memberInCellule)) {
            $this->memberIn_cellules->add($memberInCellule);
            $memberInCellule->addMember($this);
        }

        return $this;
    }

    public function removeMemberInCellule(Cellule $memberInCellule): static
    {
        if ($this->memberIn_cellules->removeElement($memberInCellule)) {
            $memberInCellule->removeMember($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Club>
     */
    public function getClubs(): Collection
    {
        return $this->clubs;
    }

    public function addClub(Club $club): static
    {
        if (!$this->clubs->contains($club)) {
            $this->clubs->add($club);
            $club->setAdmin($this);
        }

        return $this;
    }

    public function removeClub(Club $club): static
    {
        if ($this->clubs->removeElement($club)) {
            // set the owning side to null (unless already changed)
            if ($club->getAdmin() === $this) {
                $club->setAdmin(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Club>
     */
    public function getMemberInClubs(): Collection
    {
        return $this->memberIn_clubs;
    }

    public function addMemberInClub(Club $memberInClub): static
    {
        if (!$this->memberIn_clubs->contains($memberInClub)) {
            $this->memberIn_clubs->add($memberInClub);
            $memberInClub->addMember($this);
        }

        return $this;
    }

    public function removeMemberInClub(Club $memberInClub): static
    {
        if ($this->memberIn_clubs->removeElement($memberInClub)) {
            $memberInClub->removeMember($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setPublisher($this);
        }

        return $this;
    }

    public function removePost(Post $post): static
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getPublisher() === $this) {
                $post->setPublisher(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comments>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comments $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setCommenter($this);
        }

        return $this;
    }

    public function removeComment(Comments $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getCommenter() === $this) {
                $comment->setCommenter(null);
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
            $request->setOwner($this);
        }

        return $this;
    }

    public function removeRequest(Request $request): static
    {
        if ($this->requests->removeElement($request)) {
            // set the owning side to null (unless already changed)
            if ($request->getOwner() === $this) {
                $request->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getSentNotifications(): Collection
    {
        return $this->sentNotifications;
    }

    public function addSentNotification(Notification $sentNotification): static
    {
        if (!$this->sentNotifications->contains($sentNotification)) {
            $this->sentNotifications->add($sentNotification);
            $sentNotification->setSender($this);
        }

        return $this;
    }

    public function removeSentNotification(Notification $sentNotification): static
    {
        if ($this->sentNotifications->removeElement($sentNotification)) {
            // set the owning side to null (unless already changed)
            if ($sentNotification->getSender() === $this) {
                $sentNotification->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getReceivedNotifications(): Collection
    {
        return $this->receivedNotifications;
    }

    public function addReceivedNotification(Notification $receivedNotification): static
    {
        if (!$this->receivedNotifications->contains($receivedNotification)) {
            $this->receivedNotifications->add($receivedNotification);
            $receivedNotification->addReceiver($this);
        }

        return $this;
    }

    public function removeReceivedNotification(Notification $receivedNotification): static
    {
        if ($this->receivedNotifications->removeElement($receivedNotification)) {
            $receivedNotification->removeReceiver($this);
        }

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $bio;

        return $this;
    }


}
