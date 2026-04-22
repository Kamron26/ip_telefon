<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Controller\RecordingDownloadController;
use App\Repository\RecordingRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: RecordingRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Get(
            uriTemplate: '/recordings/{id}/download',
            controller: RecordingDownloadController::class,
            read: true
        ),
    ],
    normalizationContext: ['groups' => ['recording:read']]
)]
class Recording
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['recording:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['recording:read'])]
    private ?CallLog $cal = null;

    #[ORM\Column(length: 255)]
    #[Groups(['recording:read'])]
    private ?string $filePath = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['recording:read'])]
    private ?int $fileSize = null;

    #[ORM\Column]
    #[Groups(['recording:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getCal(): ?CallLog
    {
        return $this->cal;
    }

    public function setCal(?CallLog $cal): static
    {
        $this->cal = $cal;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): static
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    public function setFileSize(?int $fileSize): static
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
