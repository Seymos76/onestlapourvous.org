<?php


namespace App\DataFixtures;


use Cron\CronBundle\Entity\CronJob;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CronFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function load(ObjectManager $manager)
    {
        $cronDeletePastAppointments = new CronJob();
        $cronDeletePastAppointments->setName("Delete past appointments");
        $cronDeletePastAppointments->setDescription("Daily delete past appointments");
        $cronDeletePastAppointments->setCommand("bookings:clean:past");
        $cronDeletePastAppointments->setSchedule("30 * * * *");
        $cronDeletePastAppointments->setEnabled(true);
        $manager->persist($cronDeletePastAppointments);

        $cronDeleteAppointmentsByWaitingStatus = new CronJob();
        $cronDeleteAppointmentsByWaitingStatus->setName("Delete appointments");
        $cronDeleteAppointmentsByWaitingStatus->setDescription("Daily delete waiting to delete appointments");
        $cronDeleteAppointmentsByWaitingStatus->setCommand("bookings:clean:status:delete");
        $cronDeleteAppointmentsByWaitingStatus->setSchedule("30 * * * *");
        $cronDeleteAppointmentsByWaitingStatus->setEnabled(true);
        $manager->persist($cronDeleteAppointmentsByWaitingStatus);

        $manager->flush();
    }

    public function getDependencies()
    {
        return array(
            AppointmentFixtures::class,
        );
    }

    public static function getGroups(): array
    {
        return ['usable'];
    }
}