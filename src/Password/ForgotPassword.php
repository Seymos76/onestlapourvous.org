<?php


namespace App\Password;


use App\Entity\EmailReport;
use App\Entity\User;
use App\Services\MailerFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ForgotPassword
{
    private $entityManager;
    private $mailerFactory;

    public function __construct(EntityManagerInterface $entityManager, MailerFactory $mailerFactory)
    {
        $this->entityManager = $entityManager;
        $this->mailerFactory = $mailerFactory;
    }

    public function resetPasswordFor(string $email): UserInterface
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$user instanceof UserInterface) {
            throw new \Exception("L'utilisateur récupéré n'est pas un UserInterface");
        }
        $user->setPasswordResetToken(uniqid('pwd_reset_', true));
        $this->entityManager->flush();
        $this->mailerFactory->createAndSend(
            [
                "Réinitialisation de votre mot de passe",
                $user->getEmail(),
                $this->renderView(
                    'email/user_reset_email.html.twig', [
                        'project_url' => $_ENV['PROJECT_URL'],
                        'token' => $user->getPasswordResetToken()
                    ]
                ),
                null
            ],
            EmailReport::TYPE_PASSWORD_RESET_REQUEST
        );
        return [
            'message' => [
                'type' => 'success',
                'message' => "Un email vous a été envoyé, pensez à vérifier vos spams."
            ],
            'route' => 'forgot_password'
        ];
    }
}