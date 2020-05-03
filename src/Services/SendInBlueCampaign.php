<?php


namespace App\Services;

use SendinBlue\Client\Configuration;
use SendinBlue\Client\Api\EmailCampaignsApi;
use SendinBlue\Client\Model\CreateEmailCampaign;

class SendInBlueCampaign
{
    const RECIPTIENTS = [
        'ROLE_PATIENT' => 1,
        'ROLE_THERAPIST' => 2
    ];

    public function createAndSend(
        string $campaignName,
        string $subject,
        string $htmlContent,
        string $recipientList,
        ?\DateTime $scheduledAt = null
    )
    {
        Configuration::getDefaultConfiguration()->setApiKey("api-key", $_ENV['SENDINBLUE_API_KEY']);
        $apiInstance = new EmailCampaignsApi();
        $emailCampaigns = new CreateEmailCampaign();

        $emailCampaigns['name'] = $campaignName;
        $emailCampaigns['subject'] = $subject;
        $emailCampaigns['sender'] = ['name' => "Depuis enlienavecvous.org", 'email' => "accueil@enlienavecvous.org"];
        $emailCampaigns['type'] = "classic";
        $emailCampaigns['htmlContent'] = $htmlContent;
        $emailCampaigns['scheduledAt'] = $scheduledAt ?? new \DateTime('now');
        $emailCampaigns['recipients'] = ['listIds' => [self::RECIPTIENTS[$recipientList]]];

        $result = $apiInstance->createEmailCampaign($emailCampaigns);
        dump($result);
    }
}