<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ReviewRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ReviewRepository::class)
 * @ApiResource(
 *     normalizationContext={"groups"={"review:read:list"}},
 *     denormalizationContext={"groups"={"review:write:data"}},
 *     collectionOperations={
 *      "get"={},
 *      "post"={}
 *     }
 * )
 */
class Review
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Restaurant::class, inversedBy="reviews")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank (message="Le restaurant est obligatoire!")
     * @Groups({"review:read:list","review:write:data"})
     */
    private $restaurant;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="reviews")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank (message="L'utilisateur est obligatoire!")
     * @Groups({"review:read:list","review:write:data"})
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank (message="Message est obligatoire!")
     * @Groups({"review:read:list","review:write:data"})
     */
    private $message;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups({"review:read:list","review:write:data"})
     */
    private $note;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $created_at;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"review:read:list","review:write:data"})
     */
    private $resp;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRestaurant(): ?Restaurant
    {
        return $this->restaurant;
    }

    public function setRestaurant(?Restaurant $restaurant): self
    {
        $this->restaurant = $restaurant;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(?int $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getResp(): ?string
    {
        return $this->resp;
    }

    public function setResp(?string $resp): self
    {
        $this->resp = $resp;

        return $this;
    }
}
