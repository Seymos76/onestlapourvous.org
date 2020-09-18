<?php


namespace App\Localisation;


use App\Entity\Department;
use Symfony\Component\Security\Core\User\UserInterface;

class UserLocalisation
{
    public function relocalizeUser(UserInterface $user, array $localisation)
    {
        $user->setCountry($localisation['selectedCountry'] ? $localisation['selectedCountry'] : 'fr');
        if ($localisation['department'] instanceof Department) {
            $user->setDepartment($localisation['department']);
            $user->setScalarDepartment($localisation['departSlug']);
        } else {
            $user->setDepartment(null);
            $user->setScalarDepartment($localisation['departSlug']);
        }
    }
}