<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CityRepository;
use JMS\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Hateoas\Configuration\Annotation as Hateoas;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Hateoas\Relation(
 *      "self",
 *      href=@Hateoas\Route(
 *          "cities.get",
 *          parameters = {"idCity" = "expr(object.getId())"}
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups={"getAllCities", "getCity"})
 * )
 * @Hateoas\Relation(
 *      "collection",
 *      href= @Hateoas\Route(
 *          "cities.getAll",
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups={"getAllCities", "getCity"})
 * )
 * @Hateoas\Relation(
 *      "create",
 *      href= @Hateoas\Route(
 *          "cities.create"
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups={"getAllCities", "getCity"})
 * )
 * @Hateoas\Relation(
 *      "update",
 *      href= @Hateoas\Route(
 *          "cities.update",
 *          parameters = {"idCity" = "expr(object.getId())"}
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups={"getAllCities", "getCity"})
 * )
 * @Hateoas\Relation(
 *      "remove",
 *      href= @Hateoas\Route(
 *          "cities.delete",
 *          parameters = {"idCity" = "expr(object.getId())"}
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups={"getAllCities", "getCity"})
 * )
 */
#[ORM\Entity(repositoryClass: CityRepository::class)]
class City
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getAllCities', 'getCity', 'getCountry', 'getAllCountries', 'getAllPlaces', 'getPlace'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getAllCities', 'getCity', 'getCountry', 'getAllCountries', 'getAllPlaces', 'getPlace'])]
    #[Assert\Sequentially([
        new Assert\NotBlank(message: 'You must give the city a name.'),
        new Assert\Type('string'),
        new Assert\Length(min: 1, max: 255)
    ])]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'cities')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    #[Groups(['getAllCities', 'getCity', 'getPlace'])]
    private ?Country $country = null;

    #[ORM\Column]
    #[Groups(['getAllCities', 'getCity', 'getCountry', 'getPlace'])]
    #[Assert\Type('integer')]
    private ?int $population = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['getAllCities', 'getCity', 'getCountry', 'getPlace'])]
    #[Assert\Type('string')]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $status = null;

    #[ORM\OneToMany(mappedBy: 'city', targetEntity: Place::class)]
    #[Groups(['getCity'])]
    private Collection $places;

    #[ORM\Column(length: 6, nullable: true)]
    #[Assert\Sequentially([
        new Assert\Type('string'),
        new Assert\Length(max: 6)
    ])]
    private ?string $time_zone = null;

    public function __construct()
    {
        $this->places = new ArrayCollection();
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

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getPopulation(): ?int
    {
        return $this->population;
    }

    public function setPopulation(int $population): self
    {
        $this->population = $population;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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
     * @return Collection<int, Place>
     */
    public function getPlaces(): Collection
    {
        return $this->places;
    }

    public function addPlace(Place $place): self
    {
        if (!$this->places->contains($place)) {
            $this->places->add($place);
            $place->setCity($this);
        }

        return $this;
    }

    public function removePlace(Place $place): self
    {
        if ($this->places->removeElement($place)) {
            // set the owning side to null (unless already changed)
            if ($place->getCity() === $this) {
                $place->setCity(null);
            }
        }

        return $this;
    }

    public function getTimeZone(): ?string
    {
        return $this->time_zone;
    }

    public function setTimeZone(?string $time_zone): self
    {
        $this->time_zone = $time_zone;

        return $this;
    }
}
