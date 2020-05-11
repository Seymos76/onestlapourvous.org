<?php


namespace App\DataFixtures;


use App\Entity\Department;
use App\Entity\Therapist;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class TherapistFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const THERAPIST_USER_REFERENCE = "therapist_user";

    /** @var UserPasswordEncoderInterface $encoder */
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

        for ($i = 1; $i <= 10; $i++) {
            $therapist = new Therapist();
            if ($i === 1) {
                $therapist->upgradeToManager();
            }
            $therapist->setEthicEntityCodeLabel("ethic label code $i");
            $therapist->setSchoolEntityLabel("school label $i");
            $therapist->setHasCertification(true);
            $therapist->setIsSupervised(true);
            $therapist->setIsRespectingEthicalFrameWork(true);
            $therapist->setEmail("therapist$i@gmail.com");
            $therapist->setPassword($this->encoder->encodePassword($therapist, "password"));
            $therapist->setEmailToken('');
            $therapist->setIsActive(true);
            $therapist->setFirstName($faker ? $faker->firstName : "Firstname");
            $therapist->setLastName($faker ? $faker->lastName : "Lastname");
            $therapist->setCountry("fr");
            /** @var Department $department */
            $departId = random_int(1, 19);
            if ($departId < 10) {
                $department = $this->getReference(DepartmentFixtures::DEPARTMENT_FR_REFERENCE . "_0" . $departId);
            } else {
                $department = $this->getReference(DepartmentFixtures::DEPARTMENT_FR_REFERENCE . "_" . $departId);
            }
            $therapist->setDepartment($department);
            $therapist->setPhoneNumber($faker ? $faker->phoneNumber : "0600000001");
            $therapist->setHasAcceptedTermsAndPolicies(true);
            $this->addReference(self::THERAPIST_USER_REFERENCE."_$i", $therapist);
            $manager->persist($therapist);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return array(
            DepartmentFixtures::class
        );
    }

    public static function getGroups(): array
    {
        return ['usable'];
    }
}