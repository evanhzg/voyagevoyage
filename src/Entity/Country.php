<?php

namespace App\Entity;

use App\Repository\CountryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CountryRepository::class)]
class Country
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getAllCountries', 'getCountry'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getAllCountries', 'getCountry'])]
    private ?string $name = null;

    #[ORM\Column(length: 5, nullable: true)]
    private ?string $language = null;

    #[ORM\Column]
    private ?bool $european = null;

    #[ORM\Column(length: 6)]
    private ?string $time_zone = null;

    #[ORM\Column]
    private ?bool $status = null;

    #[ORM\OneToMany(mappedBy: 'country', targetEntity: City::class)]
    private Collection $cities;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true, onDelete: "CASCADE")]
    #[Groups(['getAllCountries', 'getCountry'])]
    private ?City $capital = null;

    public function __construct()
    {
        $this->cities = new ArrayCollection();
    }

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

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function isEuropean(): ?bool
    {
        return $this->european;
    }

    public function setEuropean(bool $european): self
    {
        $this->european = $european;

        return $this;
    }

    public function getTimeZone(): ?string
    {
        return $this->time_zone;
    }

    public function setTimeZone(string $time_zone): self
    {
        $this->time_zone = $time_zone;

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

    /**
     * @return Collection<int, City>
     */
    public function getCities(): Collection
    {
        return $this->cities;
    }

    public function addCity(City $city): self
    {
        if (!$this->cities->contains($city)) {
            $this->cities->add($city);
            $city->setCountry($this);
        }

        return $this;
    }

    public function removeCity(City $city): self
    {
        if ($this->cities->removeElement($city)) {
            // set the owning side to null (unless already changed)
            if ($city->getCountry() === $this) {
                $city->setCountry(null);
            }
        }

        return $this;
    }

    public function getCapital(): ?City
    {
        return $this->capital;
    }

    public function setCapital(?City $capital): self
    {
        $this->capital = $capital;

        return $this;
    }
}
