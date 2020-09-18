<?php


namespace App\Command;


use App\Repository\AppointmentRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanWaitingToDeleteStatusAppointments extends Command
{
    protected $appointmentRepository;

    public function __construct(AppointmentRepository $appointmentRepository, string $name = null)
    {
        parent::__construct($name);
        $this->appointmentRepository = $appointmentRepository;
    }

    protected function configure()
    {
        $this->setName('bookings:clean:status:delete');
        $this->setDescription("Permet de supprimer chaque jour les disponibilites en attente de suppression");
        $this->setHelp("De l'aide si besoin");
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("Suppression des disponibilites en attente de suppression...");
        $query = $this->appointmentRepository->cleanDailyWaitingToDeleteStatus();
        if (null !== $query && is_integer($query)) {
            $output->writeln("$query entrees supprimees");
        } elseif (null !== $query && !is_integer($query)) {
            $output->writeln("Un soucis est survenu: $query");
        }
        $output->writeln("Suppression ok");
        return 0;
    }
}