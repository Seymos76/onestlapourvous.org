<?php


namespace App\DataFixtures;


use App\Entity\Appointment;
use App\Entity\Patient;
use App\Entity\Therapist;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppointmentFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function load(ObjectManager $manager)
    {
        if ($_SERVER['APP_ENV'] === 'dev') {
            $faker = Factory::create("fr");
        }

        for ($i = 1; $i <= 80; $i++) {
            $therapistId = random_int(1,10);
            /** @var Therapist $therapist */
            $therapist = $this->getReference(TherapistFixtures::THERAPIST_USER_REFERENCE."_$therapistId");
            $appointment = new Appointment();
            $appointment->setTherapist($therapist);
            $randomDate = $this->getRandomDate();
            $date = $randomDate['start'];
            $start = new \DateTime($date);
            $interval = new \DateInterval('PT1H');
            $end = $start->add($interval);
            $appointment->setBookingDate($start);
            $appointment->setBookingStart($start);
            $appointment->setBookingEnd($end);
            $manager->persist($appointment);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            TherapistFixtures::class,
        ];
    }

    private function getRandomDate(): array
    {
        $day = random_int(11,31);
        $hour = random_int(9,20);
        $minute = random_int(0,59);
        if ($day < 10) {
            $day = "0$day";
        }
        if ($hour < 10) {
            $hour = "0$hour";
        }
        if ($minute < 10) {
            $minute = "0$minute";
        }
        return [
            'start' => "$day-05-2020 $hour:$minute:00",
        ];
    }

    public static function getGroups(): array
    {
        return ['usable'];
    }
}