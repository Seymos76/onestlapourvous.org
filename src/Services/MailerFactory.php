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

    public function createAndSend(array $emailParams, string $type = null): EmailReport
    {
        $message = (new \Swift_Message($emailParams['subject']))
            ->setTo($emailParams['to'])
            //->addCc('company@hapinow.fr')
            ->setFrom($emailParams['from'] ?? 'accueil@enlienavecvous.org')
            ->setBody($emailParams['body'], 'text/html');
        $success = $this->mailer->send($message);
        return $this->createEmailReport($emailParams['to'], $emailParams['from'] ?? 'accueil@enlienavecvous.org', $emailParams['body'], $success, $type);
    }

    private function createEmailReport(string $to, string $from, string $body, bool $success, string $type = null): EmailReport
    {
        $report = new EmailReport();
        $report->setSender($from)->setRecipient($to)->setMessage($body)->setSentAt(new \DateTime('now'));
        $report->setSuccess($success);
        $report->setType($type);
        $this->entityManager->persist($report);
        $this->entityManager->flush();
        return $report;
    }
}