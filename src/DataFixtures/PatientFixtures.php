<?php

namespace App\DataFixtures;

use App\Entity\Department;
use App\Entity\Patient;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PatientFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const PATIENT_USER_REFERENCE = "patient_";

    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        if ($_SERVER['APP_ENV'] === 'dev') {
            $faker = Factory::create("fr");
        }
        for ($i = 1; $i <= 5; $i++) {
            $patient = new Patient();
            $patient->setEmail("patient$i@gmail.com");
            $patient->setPassword($this->encoder->encodePassword($patient, "password"));
            $patient->setEmailToken('');
            $patient->activate();
            $patient->setFirstName($faker ? $faker->firstName : "Firstname");
            $patient->setLastName($faker ? $faker->lastName : "Lastname");
            $patient->setCountry("fr");
            if ($i < 10) {
                /** @var Department $department */
                $department = $this->getReference(DepartmentFixtures::DEPARTMENT_FR_REFERENCE . "_0" . $i);
            } else {
                /** @var Department $department */
                $department = $this->getReference(DepartmentFixtures::DEPARTMENT_FR_REFERENCE . "_" . $i);
            }
            $patient->setDepartment($department);
            $patient->setPhoneNumber($faker ? $faker->phoneNumber : "0600000000");
            $patient->setHasAcceptedTermsAndPolicies(true);
            $this->addReference(self::PATIENT_USER_REFERENCE."_$i", $patient);
            $patient->setIsMajor(true);
            $manager->persist($patient);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return array(
            DepartmentFixtures::class,
        );
    }

    public static function getGroups(): array
    {
        return ['usable'];
    }
}
