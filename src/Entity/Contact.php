<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ContactRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
#[ORM\Table(name: 'contact')]
#[ORM\Index(columns: ['last_name'], name: 'idx_contact_last_name')]
class Contact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $firstName;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $lastName;

    #[ORM\Column(type: Types::STRING, length: 45)]
    private string $ip;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $country;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    /** @var Collection<int, PhoneNumber> */
    #[ORM\OneToMany(
        targetEntity: PhoneNumber::class,
        mappedBy: 'contact',
        cascade: ['persist'],
        orphanRemoval: true,
    )]
    private Collection $phoneNumbers;

    public function __construct(
        string $firstName,
        string $lastName,
        string $ip,
        ?string $country = null,
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->ip = $ip;
        $this->country = $country;
        $this->createdAt = new \DateTimeImmutable();
        $this->phoneNumbers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /** @return Collection<int, PhoneNumber> */
    public function getPhoneNumbers(): Collection
    {
        return $this->phoneNumbers;
    }

    public function addPhoneNumber(PhoneNumber $phoneNumber): self
    {
        if (!$this->phoneNumbers->contains($phoneNumber)) {
            $this->phoneNumbers->add($phoneNumber);
            $phoneNumber->setContact($this);
        }

        return $this;
    }
}
