<?php

namespace App\Entity;

use App\Repository\PlaceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\DBAL\Types\Types;
# use Symfony\Component\Validator\Constraints as Asserts;

#[ORM\Entity(repositoryClass: PlaceRepository::class)]
class Place
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getAllPlaces'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getAllPlaces'])]
    private ?string $name = null;

    #[Groups(['getAllPlaces'])]
    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $open_hour = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $closed_hour = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $open_days = null;

    #[ORM\Column(nullable: true)]
    private ?int $pricing = null;

    #[ORM\ManyToOne(inversedBy: 'places')]
    #[ORM\JoinColumn(nullable: true, onDelete: "CASCADE")]
    #[Groups(['getAllPlaces'])]
    private ?City $city = null;

    #[ORM\Column]
    private ?bool $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getOpenHour(): ?\DateTimeInterface
    {
        return $this->open_hour;
    }

    public function setOpenHour(?\DateTimeInterface $open_hour): self
    {
        $this->open_hour = $open_hour;

        return $this;
    }

    public function getClosedHour(): ?\DateTimeInterface
    {
        return $this->closed_hour;
    }

    public function setClosedHour(?\DateTimeInterface $closed_hour): self
    {
        $this->closed_hour = $closed_hour;

        return $this;
    }

    public function getOpenDays(): ?string
    {
        return $this->open_days;
    }

    public function setOpenDays(?string $open_days): self
    {
        $this->open_days = $open_days;

        return $this;
    }

    public function getPricing(): ?int
    {
        return $this->pricing;
    }

    public function setPricing(?int $pricing): self
    {
        $this->pricing = $pricing;

        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }
}
