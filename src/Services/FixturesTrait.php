<?php


namespace App\Services;


use App\Entity\Town;
use Cocur\Slugify\Slugify;

trait FixturesTrait
{
    public function getDecodedArrayFromFile(string $file): array
    {
        $file = file_get_contents($file);
        // parse regions_fr.json to array
        $array = json_decode($file, true);
        return $array;
    }

    public function getSlug(string $string): string
    {
        $slug = new Slugify();
        return $slug->slugify($string);
    }

    public function createFrCity(array $city): Town
    {
        $town = new Town();
        $town->setName($city["nom"]);
        $town->setCode($city["code"]);
        $town->setScalarDepart($city["codeDepartement"]);
        $town->setZipCodes($city["codesPostaux"]);
        return $town;
    }

    public function createBeCity(array $city): Town
    {
        $town = new Town();
        $town->setName($city["localite"]);
        $town->setCode($city["code_postal"]);
        $town->setScalarDepart($city["province"]);
        $town->setZipCodes([$city["code_postal"]]);
        return $town;
    }

    public function createLuCity(array $city): Town
    {
        $town = new Town();
        $town->setName($city["COMMUNE"]);
        $town->setCode($city["LAU2"]);
        $town->setScalarDepart($city["CANTON"]);
        $town->setZipCodes([$city["LAU2"]]);
        return $town;
    }

    public function createChCity(array $city): Town
    {
        $town = new Town();
        $town->setName($city["city"]);
        $town->setScalarDepart($city["admin"]);
        return $town;
    }
}