<?php


namespace App\Services;


class MailerFactory
{
    private $mailer;

    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function createAndSend($subject, $to, $from, $body)
    {
        $message = (new \Swift_Message($subject))
            ->setTo($to)
            ->addTo('company@hapinow.fr')
            ->setFrom($from ?? 'accueil@enlienavecvous.org')
            ->setBody($body, 'text/html');
        $this->mailer->send($message);
    }
}