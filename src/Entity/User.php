<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discriminator", type="string")
 * @ORM\DiscriminatorMap({"user" = "User", "therapist" = "Therapist", "patient" = "Patient"})
 * @UniqueEntity(fields={"email"}, message="Cette adresse est déjà utilisée.")
 */
class User implements UserInterface
{
    const USER_ROLE = [
        'ROLE_PATIENT' => "Demandeur.se",
        'ROLE_THERAPIST' => "Praticien.ne",
        'ROLE_MANAGER' => "Manager"
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups({"confirm_booking", "csv_export"})
     */
    protected $email;

    /**
     * @ORM\Column(type="array")
     */
    protected $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    protected $password;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isActive;

    /**
     * @ORM\Column(type="string")
     */
    protected $emailToken;

    /**
     * @ORM\Column(type="string")
     * @Groups({"create_booking", "get_bookings", "patient_research", "csv_export"})
     */
    protected $firstName;

    /**
     * @ORM\Column(type="string")
     * @Groups({"create_booking", "get_bookings", "patient_research", "csv_export"})
     */
    protected $lastName;

    /**
     * @ORM\Column(type="string")
     */
    protected $country;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $zipCode;

    /**
     * @ORM\Column(type="string")
     */
    protected $phoneNumber;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $hasAcceptedTermsAndPolicies;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"create_booking", "get_bookings"})
     */
    protected $displayName;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $scalarTown;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $scalarDepartment;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Department", inversedBy="users")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"get_bookings"})
     */
    protected $department;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $passwordResetToken;

    public function __construct()
    {
        $this->createdAt = new \DateTime('now');
        $this->roles = ['ROLE_USER'];
        $this->isActive = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): ?string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getEmailToken(): ?string
    {
        return $this->emailToken;
    }

    public function setEmailToken(string $emailToken): self
    {
        $this->emailToken = $emailToken;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): void
    {
        $this->zipCode = $zipCode;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function getHasAcceptedTermsAndPolicies(): ?bool
    {
        return $this->hasAcceptedTermsAndPolicies;
    }

    public function setHasAcceptedTermsAndPolicies(bool $hasAcceptedTermsAndPolicies): void
    {
        $this->hasAcceptedTermsAndPolicies = $hasAcceptedTermsAndPolicies;
    }

    public function setUniqueEmailToken(): self
    {
        $this->emailToken = uniqid("", true);
        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(?string $displayName): self
    {
        $this->displayName = $displayName ?? $this->getFirstName() . " " . $this->getLastName();

        return $this;
    }

    public function getScalarTown(): ?string
    {
        return $this->scalarTown;
    }

    public function setScalarTown(?string $scalarTown): self
    {
        $this->scalarTown = $scalarTown;

        return $this;
    }

    public function getScalarDepartment(): ?string
    {
        return $this->scalarDepartment;
    }

    public function setScalarDepartment(?string $scalarDepartment): self
    {
        $this->scalarDepartment = $scalarDepartment;

        return $this;
    }

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): self
    {
        $this->department = $department;

        return $this;
    }

    public function getPasswordResetToken(): ?string
    {
        return $this->passwordResetToken;
    }

    public function setPasswordResetToken(?string $passwordResetToken): self
    {
        $this->passwordResetToken = $passwordResetToken;

        return $this;
    }
}
