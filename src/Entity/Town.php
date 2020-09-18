<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TownRepository")
 */
class Town
{
    const TOWN_JSON_FILE = [
        'fr' => __DIR__ . "./../../public/data/communes/communes_fr.json",
        'be' => __DIR__ . "./../../public/data/communes/communes_be.json",
        'lu' => __DIR__ . "./../../public/data/communes/communes_lu.json",
        'ch' => __DIR__ . "./../../public/data/communes/communes_ch.json"
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $code;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $zipCodes = [];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Department", inversedBy="towns")
     * @ORM\JoinColumn(nullable=true)
     */
    private $department;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $scalarDepart;

    public function __construct()
    {
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getZipCodes(): ?array
    {
        return $this->zipCodes;
    }

    public function setZipCodes(?array $zipCodes): self
    {
        $this->zipCodes = $zipCodes;

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

    public function getScalarDepart(): ?string
    {
        return $this->scalarDepart;
    }

    public function setScalarDepart(string $scalarDepart): self
    {
        $this->scalarDepart = $scalarDepart;

        return $this;
    }
}
