<?php


namespace App\Appointment;


use Symfony\Contracts\EventDispatcher\Event;

class AppointmentEvent extends Event
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}