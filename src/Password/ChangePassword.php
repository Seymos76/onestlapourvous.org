<?php


namespace App\Password;


use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ChangePassword
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function change(UserInterface $user, string $password)
    {
        $encoded = $this->encoder->encodePassword($user, $password);
        $user->setPassword($encoded);
    }
}