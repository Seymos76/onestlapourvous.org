<?php


namespace App\Tests\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PublicControllerTest extends WebTestCase
{
    /**
     * @dataProvider provideUrls
     * @param $url
     */
    public function testStaticPages($url)
    {
        $client = self::createClient();
        $client->request('GET', $url);
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertPageTitleContains("On est là pour vous !");
        $this->assertResponseStatusCodeSame(200);
    }

    public function provideUrls()
    {
        return [
            ['/'],
            ['/demander-de-l-aide'],
            ['/proposer-mon-aide'],
            ['/proposer-mon-aide/en-attente-de-validation'],
            ['/comment-ca-marche'],
            ['/le-projet'],
            ['/qui-sommes-nous'],
            ['/politique-de-protection-des-donnees'],
            ['/conditions-d-utilisation'],
            ['/mentions-legales']
        ];
    }

    public function testTherapistRegister()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/proposer-mon-aide');
        $buttonCrawlerNode = $crawler->selectButton('submit');
        $form = $buttonCrawlerNode->form();
        $form = $buttonCrawlerNode->form([
            'therapist_register_form[firstName]' => "Jean",
            'therapist_register_form[lastName]' => "Dupont",
            'therapist_register_form[country]' => "France",
            'therapist_register_form[zipCode]' => "01500",
            'therapist_register_form[phoneNumber]' => "0665546677",
            'therapist_register_form[email]' => "jean@gmail.com",
            'therapist_register_form[password]' => "password",
            'therapist_register_form[ethicEntityCodeLabel]' => "Gestalt",
            'therapist_register_form[schoolEntityLabel]' => "Gestalt",
            'therapist_register_form[hasAcceptedTermsAndPolicies]' => true,
            'therapist_register_form[hasCertification]' => true,
            'therapist_register_form[isSupervised]' => true,
            'therapist_register_form[isRespectingEthicalFrameWork]' => true,
        ]);
        $client->submit($form);
        $this->assertContains(
            'jean@gmail.com',
            $client->getResponse()->getContent()
        );
    }
}