<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AccountController
 * @package App\Controller
 * @Route(path="/mon-compte")
 */
class AccountController extends AbstractController
{
    /**
     * @Route(path="/", name="account_dashboard", methods={"GET"})
     */
    public function dashboard()
    {
        return $this->render(
            'account/dashboard.html.twig'
        );
    }
}