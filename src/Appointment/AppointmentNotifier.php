<?php


namespace App\Appointment;


use SplSubject;

class AppointmentNotifier implements \SplObserver
{
    public function update(SplSubject $subject)
    {
        dump('SplSubject',$subject);
        echo "AppointmentNotifier update ! ";
    }

}