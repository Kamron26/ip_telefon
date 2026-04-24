<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ExtensionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ExtensionRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['extension:read']],
    denormalizationContext: ['groups' => ['extension:write']]
)]
class Extension
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['extension:read', 'call_log:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 20, unique: true)]
    #[Groups(['extension:read', 'extension:write', 'call_log:read'])]
    private ?string $number = null;

    #[ORM\Column(length: 255)]
    #[Groups(['extension:write'])]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['extension:read', 'extension:write', 'call_log:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['extension:read', 'extension:write'])]
    private ?string $callerId = null;

    #[ORM\Column(length: 30)]
    #[Groups(['extension:read'])]
    private string $status = 'offline';

    #[ORM\Column]
    #[Groups(['extension:read', 'extension:write'])]
    private bool $isActive = true;

    #[ORM\Column]
    #[Groups(['extension:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getNumber(): ?string { return $this->number; }
    public function setNumber(string $number): static
    {
        $this->number = $number;
        return $this;
    }

    public function getPassword(): ?string { return $this->password; }
    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getName(): ?string { return $this->name; }
    public function setName(?string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getCallerId(): ?string { return $this->callerId; }
    public function setCallerId(?string $callerId): static
    {
        $this->callerId = $callerId;
        return $this;
    }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
