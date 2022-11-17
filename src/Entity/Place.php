<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\PlaceRepository;
use JMS\Serializer\Annotation\Groups;
use Hateoas\Configuration\Annotation as Hateoas;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Hateoas\Relation(
 *      "self",
 *      href=@Hateoas\Route(
 *          "countries.get",
 *          parameters = {"idCountry" = "expr(object.getId())"}
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups={"getAllPlaces", "getPlace"})
 * )
 * @Hateoas\Relation(
 *      "collection",
 *      href= @Hateoas\Route(
 *          "countries.getAll",
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups={"getAllPlaces", "getPlace"})
 * )
 * @Hateoas\Relation(
 *      "create",
 *      href= @Hateoas\Route(
 *          "countries.create"
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups={"getAllPlaces", "getPlace"})
 * )
 * @Hateoas\Relation(
 *      "update",
 *      href= @Hateoas\Route(
 *          "countries.update",
 *          parameters = {"idCountry" = "expr(object.getId())"}
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups={"getAllPlaces", "getPlace"})
 * )
 * @Hateoas\Relation(
 *      "remove",
 *      href= @Hateoas\Route(
 *          "countries.delete",
 *          parameters = {"idCountry" = "expr(object.getId())"}
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups={"getAllPlaces", "getPlace"})
 * )
 */
#[ORM\Entity(repositoryClass: PlaceRepository::class)]
class Place
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getAllPlaces', 'getPlace', 'getCity'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getAllPlaces', 'getPlace', 'getCity'])]
    #[Assert\Sequentially([
        new Assert\NotBlank(message: 'You must give the place a name.'),
        new Assert\Type('string'),
        new Assert\Length(max: 255)
    ])]
    private ?string $name = null;

    #[Groups(['getAllPlaces', 'getPlace', 'getCity'])]
    #[ORM\Column(length: 255)]
    #[Assert\Sequentially([
        new Assert\NotBlank(message: 'You must give the place a type.'),
        new Assert\Type('string'),
        new Assert\Length(min: 1, max: 255)
    ])]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['getAllPlaces', 'getPlace', 'getCity'])]
    #[Assert\Sequentially([
        new Assert\Type('string'),
        new Assert\Length(max: 255)
    ])]
    private ?string $address = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['getAllPlaces', 'getPlace', 'getCity'])]
    #[Assert\Sequentially([
        new Assert\Type("string"),
        new Assert\Length(5, exactMessage: "Should be in hh:mm format")
    ])]
    private ?string $open_hour = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['getAllPlaces', 'getPlace', 'getCity'])]
    #[Assert\Sequentially([
        new Assert\Type("string"),
        new Assert\Length(5, exactMessage: "Should be in hh:mm format")
    ])]
    private ?string $closed_hour = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['getAllPlaces', 'getPlace', 'getCity'])]
    #[Assert\Sequentially([
        new Assert\Type('string'),
        new Assert\Length(max: 255)
    ])]
    private ?string $open_days = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['getAllPlaces', 'getPlace', 'getCity'])]
    #[Assert\Sequentially([
        new Assert\Type('integer'),
        new Assert\Length(min: 1, max: 3)
    ])]
    private ?int $pricing = null;

    #[ORM\ManyToOne(inversedBy: 'places')]
    #[ORM\JoinColumn(nullable: true, onDelete: "CASCADE")]
    #[Groups(['getAllPlaces', 'getPlace'])]
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

    public function getOpenHour(): ?string
    {
        return $this->open_hour;
    }

    public function setOpenHour(?string $open_hour): self
    {
        $this->open_hour = $open_hour;

        return $this;
    }

    public function getClosedHour(): ?string
    {
        return $this->closed_hour;
    }

    public function setClosedHour(?string $closed_hour): self
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
