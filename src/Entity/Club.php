<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ClubRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use App\Controller\ClubController;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ClubRepository::class)]
#[Vich\Uploadable]
#[ApiResource(
    normalizationContext:['groups' => 'read:club'],
    collectionOperations: [
        // 'get_clubs' => [
        //     'method' => 'GET',
        //     'path' => '/example',
        //     'controller' => 'App\Controller\GetClubsController::getAllClubs',
        //     'deserialize' => false
        // ],
        'get' => [
            // 'formats' => 'jsonld'
        ],
        'post' => [
            'controller' => ClubController::class,
            'deserialize' => false
        ]
    ],
    itemOperations: [
        'get',
        'delete',
        'update_club' => [
            'method' => 'POST',
            'path' => '/clubs/{id}/update_club',
            'controller' => 'App\Controller\ClubUpdateController::updateClub',
            'deserialize' => false,
            'security' => 'is_granted("ROLE_ADMIN")'
        ],
        'notification' => [
            'method' => 'POST',
            'path' => '/clubs/{id}/notification',
            'controller' => 'App\Controller\ClubController::notifyMembers',
            'deserialize' => false,
            'security' => 'is_granted("ROLE_ADMIN")'
        ],

    ]
)]
class Club
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read:club'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:club'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:club'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: "create")]
    #[Groups(['read:club'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: "update")]
    #[Groups(['read:club'])]
    private ?\DateTimeInterface $updatedAt = null;

    // NOTE: This is not a mapped field of entity metadata, just a simple property.
    #[Vich\UploadableField(mapping: 'club_images', fileNameProperty: 'imageName', size: 'imageSize')]
    private ?File $imageFile = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['read:club'])]
    private ?string $imageName = null;


    #[ORM\Column(nullable: true)]
    #[Groups(['read:club'])]
    private ?int $imageSize = null;

    #[ORM\OneToMany(mappedBy: 'club', targetEntity: Cellule::class, orphanRemoval: true)]
    #[Groups(['read:club'])]
    private Collection $cellules;

    #[ORM\ManyToOne(inversedBy: 'clubs')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read:club'])]
    private ?User $admin = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'memberIn_clubs')]
    #[Groups(['read:club'])]
    private Collection $members;

    #[ORM\OneToMany(mappedBy: 'club', targetEntity: Post::class ,orphanRemoval:true)]
    #[Groups(['read:club'])]
    private Collection $posts;

    #[ORM\OneToMany(mappedBy: 'club', targetEntity: Request::class)]
    #[Groups(['read:club'])]
    private Collection $requests;

    #[ORM\OneToMany(mappedBy: 'club', targetEntity: Notification::class, orphanRemoval: true)]
    #[Groups(['read:club'])]
    private Collection $notifications;

    #[ORM\OneToMany(mappedBy: 'club', targetEntity: UpcomingEvent::class, orphanRemoval: true)]
    private Collection $upcomingEvents;

    #[ORM\OneToMany(mappedBy: 'club', targetEntity: Project::class, orphanRemoval: true)]
    private Collection $projects;

    public function __construct()
    {
        $this->cellules = new ArrayCollection();
        $this->members = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->requests = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->upcomingEvents = new ArrayCollection();
        $this->projects = new ArrayCollection();
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
            $cellule->setClub($this);
        }

        return $this;
    }

    public function removeCellule(Cellule $cellule): static
    {
        if ($this->cellules->removeElement($cellule)) {
            // set the owning side to null (unless already changed)
            if ($cellule->getClub() === $this) {
                $cellule->setClub(null);
            }
        }

        return $this;
    }

    public function getAdmin(): ?User
    {
        return $this->admin;
    }

    public function setAdmin(?User $admin): static
    {
        $this->admin = $admin;

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
            $post->setClub($this);
        }

        return $this;
    }

    public function removePost(Post $post): static
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getClub() === $this) {
                $post->setClub(null);
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
            $request->setClub($this);
        }

        return $this;
    }

    public function removeRequest(Request $request): static
    {
        if ($this->requests->removeElement($request)) {
            // set the owning side to null (unless already changed)
            if ($request->getClub() === $this) {
                $request->setClub(null);
            }
        }

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
            $notification->setClub($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getClub() === $this) {
                $notification->setClub(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UpcomingEvent>
     */
    public function getUpcomingEvents(): Collection
    {
        return $this->upcomingEvents;
    }

    public function addUpcomingEvent(UpcomingEvent $upcomingEvent): static
    {
        if (!$this->upcomingEvents->contains($upcomingEvent)) {
            $this->upcomingEvents->add($upcomingEvent);
            $upcomingEvent->setClub($this);
        }

        return $this;
    }

    public function removeUpcomingEvent(UpcomingEvent $upcomingEvent): static
    {
        if ($this->upcomingEvents->removeElement($upcomingEvent)) {
            // set the owning side to null (unless already changed)
            if ($upcomingEvent->getClub() === $this) {
                $upcomingEvent->setClub(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Project>
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): static
    {
        if (!$this->projects->contains($project)) {
            $this->projects->add($project);
            $project->setClub($this);
        }

        return $this;
    }

    public function removeProject(Project $project): static
    {
        if ($this->projects->removeElement($project)) {
            // set the owning side to null (unless already changed)
            if ($project->getClub() === $this) {
                $project->setClub(null);
            }
        }

        return $this;
    }
}
