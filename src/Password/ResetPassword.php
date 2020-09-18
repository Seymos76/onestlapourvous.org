<?php


namespace App\Password;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ResetPassword
{
    private UserPasswordEncoderInterface $encoder;
    private EntityManagerInterface $entityManager;

    public function __construct(UserPasswordEncoderInterface $encoder, EntityManagerInterface $entityManager)
    {
        $this->encoder = $encoder;
        $this->entityManager = $entityManager;
    }

    public function reset(UserInterface $user, string $password = null)
    {
        $encoded = $this->encoder->encodePassword($user, $password);
        $user->setPassword($encoded)->setPasswordResetToken(null);
        $this->entityManager->flush();
    }
}