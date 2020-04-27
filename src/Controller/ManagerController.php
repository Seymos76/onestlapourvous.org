<?php


namespace App\Controller;


use App\Entity\Appointment;
use App\Entity\Department;
use App\Entity\Therapist;
use App\Entity\Town;
use App\Entity\User;
use App\Repository\AppointmentRepository;
use App\Repository\DepartmentRepository;
use App\Repository\TherapistRepository;
use App\Repository\TownRepository;
use App\Repository\UserRepository;
use App\Services\MailerFactory;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class ManagerController
 * @package App\Controller
 * @Route(path="/manager")
 */
class ManagerController extends AbstractController
{
    private $therapistRepository;

    public function __construct(TherapistRepository $therapistRepository)
    {
        $this->therapistRepository = $therapistRepository;
    }

    /**
     * @Route(path="/new-users", name="manager_new_users", defaults={"page"=1})
     * @param UserRepository $userRepository
     * @return Response
     */
    public function newUsers(
        UserRepository $userRepository,
        Request $request,
        PaginatorInterface $paginator
    )
    {
        $this->denyAccessUnlessGranted("ROLE_THERAPIST", null, "Vous n'avez pas accès à cette page.");
        if ($request->isMethod("POST")) {
            $from = $request->request->get('date_from');
            $to = $request->request->get('date_to');
            $newUsers = $userRepository->findRecentlyRegistered($from, $to);
            $paginated = $paginator->paginate(
                $newUsers,
                $request->query->getInt('page', 1),
                10
            );
            return $this->render(
                'manager/new_users.html.twig',
                [
                    'new_users' => $paginated
                ]
            );
        }
        $newUsers = $userRepository->findAll();
        $paginated = $paginator->paginate(
            $newUsers,
            $request->query->getInt('page', 1),
            10
        );
        return $this->render(
            'manager/new_users.html.twig',
            [
                'new_users' => $paginated
            ]
        );
    }

    /**
     * @Route(path="/resend-email-confirmation/{id}", name="manager_resend_email_confirmation")
     * @ParamConverter(name="id", class="App\Entity\User")
     * @return RedirectResponse
     */
    public function resendEmailValidation(User $user, MailerFactory $mailerFactory)
    {
        $this->denyAccessUnlessGranted("ROLE_MANAGER", null, "Vous n'avez pas accès à cette fonctionnalité.");
        if ($user instanceof User && $user->getEmailToken() !== '' && !$user->isActive()) {
            if (in_array("ROLE_THERAPIST", $user->getRoles())) {
                $mailerFactory->createAndSend(
                    "Validation de votre inscription",
                    $user->getEmail(),
                    'accueil@enlienavecvous.org',
                    $this->renderView(
                        'email/therapist_registration.html.twig',
                        ['email_token' => $user->getEmailToken(), 'project_url' => $_ENV['PROJECT_URL']]
                    )
                );
            } else {
                $mailerFactory->createAndSend(
                    "Validation de votre inscription",
                    $user->getEmail(),
                    'accueil@enlienavecvous.org',
                    $this->renderView(
                        'email/patient_registration.html.twig',
                        ['email_token' => $user->getEmailToken(), 'project_url' => $_ENV['PROJECT_URL']]
                    )
                );
            }

            $this->addFlash('success', "Email de validation réenvoyé.");
        } else {
            $this->addFlash('error', "Utilisateur non trouvé ou déjà actif.");
        }

        return $this->redirectToRoute('manager_users_waiting');
    }
    /**
     * @Route(path="/manage-users", name="manager_manage_users", defaults={"page"=1})
     * @param UserRepository $userRepository
     * @return Response
     */
    public function manageUsers(
        UserRepository $userRepository,
        Request $request,
        EntityManagerInterface $manager,
        PaginatorInterface $paginator
    )
    {
        $this->denyAccessUnlessGranted("ROLE_MANAGER", null, "Vous n'avez pas accès à cette page.");

        if ($request->isMethod("POST")) {
            $role = $request->request->get('user_role');
            $userId = $request->request->get('user_id');
            $selectedUser = $userRepository->find($userId);
            if ($selectedUser instanceof User && !in_array($role, $selectedUser->getRoles())) {
                $existentRoles = $selectedUser->getRoles();
                array_push($existentRoles, $role);
                $selectedUser->setRoles($existentRoles);
                $manager->flush();
                $firstName = $selectedUser->getFirstName();
                $lastName = $selectedUser->getLastName();
                $this->addFlash('success', "Role ajouté à $firstName $lastName !");
                return $this->redirectToRoute('manager_manage_users');
            } else {
                $this->addFlash('error', "Ce membre a déjà le role sélectionné.");
                return $this->redirectToRoute('manager_manage_users');
            }
        }

        $params = [];
        foreach ($request->query as $key => $value) {
            if ($value !== "") {
                $params[$key] = $value;
            }
        }

        if (count($params) === 0) {
            $users = $userRepository->findByParams();
        } else {
            $users = $userRepository->findByParams($params);
        }

        $paginated = $paginator->paginate(
            $users,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render(
            'manager/manage_members.html.twig',
            [
                'users' => $paginated
            ]
        );
    }

    /**
     * @Route(path="/en-attente", name="manager_users_waiting", defaults={"page"=1})
     * @param UserRepository $userRepository
     * @return Response
     */
    public function usersWaitingForActivation(UserRepository $userRepository, Request $request, PaginatorInterface $paginator)
    {
        $waitingUsers = $userRepository->findBy(['isActive' => false]);
        $paginated = $paginator->paginate(
            $waitingUsers,
            $request->query->getInt('page', 1),
            10
        );
        return $this->render(
            'manager/users_waiting_for_activation.html.twig',
            [
                'waiting_users' => $paginated
            ]
        );
    }

    /**
     * @Route(path="/zones", name="manager_zones", defaults={"page"=1})
     * @return Response
     */
    public function geolocalisation(
        Request $request,
        DepartmentRepository $departmentRepository,
        TownRepository $townRepository,
        EntityManagerInterface $entityManager,
        PaginatorInterface $paginator
    )
    {
        $params = [];
        foreach ($request->query as $key => $value) {
            if ($value !== "") {
                $params[$key] = $value;
            }
        }

        $countries = [
            'fr' => "France",
            'be' => "Belgique",
            'lu' => "Luxembourg",
            'ch' => "Suisse"
        ];

        if ($request->isMethod("POST")) {
            if ($request->request->get('action')) {
                $action = $request->request->get('action');
                // convert in switch case
                if ($action === 'delete') {
                    $code = $request->request->get('code');
                    $deparment = $departmentRepository->findOneBy(['code' => $code]);
                    $departName = $deparment->getName();
                    $cities = $townRepository->findBy(['department' => $deparment]);
                    foreach ($cities as $city) {
                        $entityManager->remove($city);
                    }
                    $entityManager->flush();
                    $this->addFlash('success', "Les villes du département $departName ont été correctement supprimées.");
                    if (count($request->query) > 0) {
                        return $this->redirectToRoute('manager_zones', ['country_filter' => $request->query->get("country_filter")]);
                    } else {
                        return $this->redirectToRoute('manager_zones');
                    }
                }
            }
            $subcode = substr($request->request->get('code'), 0, 2);
            $code = $request->request->get('code');
            $country = $request->request->get('country');
            $deparment = $departmentRepository->findOneBy(['code' => $code]);
            $departName = $deparment->getName();
            $client = HttpClient::create();
            $url = "http://www.citysearch-api.com/$country/city?login=onestlapourvous&apikey=so4c0d00de65b6aae5842f3e6f4a32040c0f5f7058&dp=$code";
            $response = $client->request('GET', $url);
            $statusCode = $response->getStatusCode();
            if ($statusCode === 200) {
                $cities = $response->toArray();
                if ($cities["results"]) {
                    foreach ($cities["results"] as $city) {
                        $town = new Town();
                        $town->setDepartment($deparment);
                        $town->setScalarDepart($code);
                        $town->setCode($city["cp"]);
                        $town->setName($city["ville"]);
                        $town->setZipCodes([$city["cp"]]);
                        $entityManager->persist($town);
                    }
                    $entityManager->flush();
                    $this->addFlash('success', "Villes chargées pour le département $departName.");
                    if (count($request->query) > 0) {
                        return $this->redirectToRoute('manager_zones', ['country_filter' => $request->query->get("country_filter")]);
                    } else {
                        return $this->redirectToRoute('manager_zones');
                    }
                } else {
                    $this->addFlash('success', "Le département $code constitue une ville en lui-meme");
                    return $this->redirectToRoute('manager_zones');
                }

            } else {
                $this->addFlash('success', "La récupération des villes pour le département $departName a échoué.");
            }
        }

        if (count($params) === 0) {
            $departments = $departmentRepository->findBy(['country' => 'fr']);
        } else {
            $departments = $departmentRepository->findByParams($params);
        }

        $paginated = $paginator->paginate(
            $departments,
            $request->query->getInt('page', 1),
            15
        );

        return $this->render(
            'manager/geolocalisation.html.twig',
            [
                'departments' => $paginated,
                'countries' => $countries
            ]
        );
    }

    /**
     * @Route(path="/zones/department/{id}", name="manager_zones_by_department", defaults={"page"=1})
     * @ParamConverter(name="id", class="App\Entity\Department")
     * @return Response
     */
    public function geolocTownsByDepartment(Department $department, PaginatorInterface $paginator, Request $request)
    {
        $towns = $department->getTowns();

        $paginated = $paginator->paginate(
            $towns,
            $request->query->getInt('page', 1),
            15
        );

        return $this->render(
            'manager/geoloc_towns_by_department.html.twig',
            [
                'department' => $department,
                'towns' => $paginated
            ]
        );
    }

    /**
     * @Route(path="/appoints-to-delete", name="manager_appoints_to_delete")
     * @param AppointmentRepository $appointmentRepository
     * @param EntityManagerInterface $entityManager
     */
    public function bookingsWaitingToBeDeleted(AppointmentRepository $appointmentRepository, EntityManagerInterface $entityManager)
    {
        $appoints = $appointmentRepository->findBy(['status' => Appointment::STATUS_TO_DELETE]);
        $i = 0;
        foreach ($appoints as $appoint) {
            $i++;
            $entityManager->remove($appoint);
        }
        $entityManager->flush();
        $this->addFlash('success', "$i Rendez-vous en attente de suppression correctement supprimés.");
        return $this->redirectToRoute('manager_new_users');
    }

    /**
     * @Route(path="/account/delete/{id}", name="manager_delete_account_by_id")
     * @ParamConverter(name="id", class="App\Entity\User")
     */
    public function deleteAccount(
        User $user,
        EntityManagerInterface $manager,
        MailerFactory $mailerFactory
    )
    {
        $this->denyAccessUnlessGranted("ROLE_MANAGER", null, "Vous n'avez pas accès à cette fonctionnalité.");
        if ($user->getId() === $this->getCurrentUser()->getId()) {
            $this->addFlash('info', "Vous avez essayé de supprimer votre compte...");
            return $this->redirectToRoute('manager_manage_users');
        }
        if ($user instanceof User) {
            // send email account deletion
            $mailerFactory->createAndSend(
                "Suppression de votre compte",
                $user->getEmail(),
                'no-reply@onestlapourvous.org',
                $this->renderView('email/user_delete_account.html.twig')
            );
            // delete user
            $manager->remove($user);
            $manager->flush();
            $this->addFlash('success', "Ce compte a été correctement supprimé.");
            return $this->redirectToRoute('manager_manage_users');
        } else {
            $this->addFlash('error', "Cet utilisateur n'existe pas.");
            return $this->redirectToRoute('manager_manage_users');
        }
    }

    private function getCurrentUser(): Therapist
    {
        return $this->therapistRepository->findOneBy(['email' => $this->getUser()->getUsername()]);
    }
}
