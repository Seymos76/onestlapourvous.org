<?php


namespace App\Localisation;


use App\Entity\Department;
use App\Entity\Town;
use App\Repository\TownRepository;

trait CityTrait
{
    private function getOrCreateCity(
        string $selectedCountry,
        TownRepository $townRepository,
        Department $department,
        array $city
    ): Town
    {
        if ($selectedCountry === 'fr') {
            $existingTown = $townRepository->findOneBy(['department' => $department, 'name' => $city["nom"]]);
        } elseif ($selectedCountry === 'be') {
            $existingTown = $townRepository->findOneBy(['department' => $department, 'name' => $city["localite"]]);
        } elseif ($selectedCountry === 'lu') {
            $existingTown = $townRepository->findOneBy(['department' => $department, 'name' => $city["COMMUNE"]]);
        } elseif ($selectedCountry === 'ch') {
            $existingTown = $townRepository->findOneBy(['department' => $department, 'name' => $city["city"]]);
        }

        if (!$existingTown instanceof Town) {
            if ($selectedCountry === 'fr') {
                $town = $this->createFrCity($city);
            } elseif ($selectedCountry === 'be') {
                $town = $this->createBeCity($city);
            } elseif ($selectedCountry === 'lu') {
                $town = $this->createLuCity($city);
            } elseif ($selectedCountry === 'ch') {
                $town = $this->createChCity($city);
            }
        }
        return $existingTown ?? $town;
    }
}