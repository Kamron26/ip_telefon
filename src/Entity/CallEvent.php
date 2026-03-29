<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\CallEventRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CallEventRepository::class)]
#[ApiResource]
class CallEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?CallLog $cal = null;

    #[ORM\Column(length: 50)]
    private ?string $eventType = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $eventTime = null;

    #[ORM\Column(nullable: true)]
    private ?array $rowData = null;

    #[ORM\Column]
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

    public function getEventType(): ?string
    {
        return $this->eventType;
    }

    public function setEventType(string $eventType): static
    {
        $this->eventType = $eventType;

        return $this;
    }

    public function getEventTime(): ?\DateTime
    {
        return $this->eventTime;
    }

    public function setEventTime(?\DateTime $eventTime): static
    {
        $this->eventTime = $eventTime;

        return $this;
    }

    public function getRowData(): ?array
    {
        return $this->rowData;
    }

    public function setRowData(?array $rowData): static
    {
        $this->rowData = $rowData;

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
