<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\CountryRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use Hateoas\Configuration\Annotation as Hateoas;
use OpenApi\Attributes;
use OpenApi\Annotations as OA;
use OA\Property;

/**
 * @Hateoas\Relation(
 *      "self",
 *      href= @Hateoas\Route(
 *          "countries.get",
 *          parameters = {"idCountry" = "expr(object.getId())"}
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups=" ")
 * )
 */
#[ORM\Entity(repositoryClass: CountryRepository::class)]
class Country
{   
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getAllCountries', 'getCountry', 'getCity', 'getAllCities'])]
    private ?int $id = null;
    
    #[ORM\Column(length: 255)]
    #[Groups(['getAllCountries', 'getCountry', 'getCity'])]
    #[Assert\Sequentially([
        new Assert\NotBlank(message: 'You must give the country a name.'),
        new Assert\Type('string'),
        new Assert\Length(min: 1, max: 255)
    ])]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['getAllCountries', 'getCountry', 'getCity'])]
    #[Assert\Sequentially([
        new Assert\Type('string'),
        new Assert\Length(min: 1, max: 255)
    ])]
    private ?string $languages = null;

    #[ORM\Column]
    #[Groups(['getAllCountries', 'getCountry', 'getCity'])]
    #[Assert\NotNull(message: 'You must say if the country is part of EU.')]
    #[Assert\Type('boolean')]
    #[Property(type: 'boolean')]
    private ?bool $european = null;

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

    public function getLanguages(): ?string
    {
        return $this->languages;
    }

    public function setLanguages(?string $languages): self
    {
        $this->languages = $languages;

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
