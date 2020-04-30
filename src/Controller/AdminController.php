<?php


namespace App\Controller;


use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AdminController
 * @package App\Controller
 * @Route(path="/admin")
 */
class AdminController extends AbstractController
{
    /**
     * @Route(path="/dashboard", name="admin_dashboard")
     * @return Response
     */
    public function dashboard()
    {
        return $this->render('admin/dashboard.html.twig');
    }

    public function changeUserNameCase(UserRepository $userRepository, EntityManagerInterface $manager)
    {
        $allUsers = $userRepository->findAll();
        foreach ($allUsers as $singleUser) {
            $roles = $singleUser->getRoles();
            dump(end($roles));
            $singleUser->setFirstName(strtolower($singleUser->getFirstName()));
            $singleUser->setLastName(strtolower($singleUser->getLastName()));
            $singleUser->setDisplayName($singleUser->getFirstName(). " " .$singleUser->getLastName());
        }
        $manager->flush();
        $this->addFlash('success', "Nom, prénom et display name de tous les utilisateurs enregistrés en lower case");
        return $this->redirectToRoute('admin_dashboard');
    }
}