<?php


namespace App\Services;


use App\Entity\EmailReport;
use Doctrine\ORM\EntityManagerInterface;

class MailerFactory
{
    private $mailer;
    private $entityManager;

    public function __construct(\Swift_Mailer $mailer, EntityManagerInterface $entityManager)
    {
        $this->mailer = $mailer;
        $this->entityManager = $entityManager;
    }

    public function createAndSend(string $subject, string $to, string $body, string $from = null, string $type = null)
    {
        $message = (new \Swift_Message($subject))
            ->setTo($to)
            //->addCc('company@hapinow.fr')
            ->setFrom($from ?? 'accueil@enlienavecvous.org')
            ->setBody($body, 'text/html');
        $success = $this->mailer->send($message);
        $this->createEmailReport($to, $from ?? 'accueil@enlienavecvous.org', $body, $success, $type);
    }

    private function createEmailReport(string $to, string $from, string $body, bool $success, string $type = null)
    {
        $report = new EmailReport();
        $report->setSender($from)->setRecipient($to)->setMessage($body)->setSentAt(new \DateTime('now'));
        $report->setSuccess($success);
        $report->setType($type);
        $this->entityManager->persist($report);
        $this->entityManager->flush();
    }
}