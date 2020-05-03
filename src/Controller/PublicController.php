<?php


namespace App\Controller;


use App\Entity\Department;
use App\Entity\Patient;
use App\Entity\Therapist;
use App\Entity\Town;
use App\Entity\User;
use App\Form\ForgotPasswordType;
use App\Form\PasswordResetType;
use App\Form\PatientRegisterType;
use App\Form\TherapistRegisterType;
use App\Repository\DepartmentRepository;
use App\Repository\PatientRepository;
use App\Repository\TherapistRepository;
use App\Repository\TownRepository;
use App\Repository\UserRepository;
use App\Services\FixturesTrait;
use App\Services\MailerFactory;
use App\Services\StatisticTrait;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PublicController extends AbstractController
{
    use FixturesTrait;

    /**
     * @Route(path="/", name="index")
     * @return Response
     */
    public function index()
    {
        return $this->render(
            'public/index.html.twig'
        );
    }

    /**
     * @Route(path="/demander-de-l-aide", name="ask_for_help")
     */
    public function askForHelpRegister(
        Request $request,
        UserPasswordEncoderInterface $encoder,
        MailerFactory $mailerFactory,
        EntityManagerInterface $entityManager,
        DepartmentRepository $departmentRepository,
        TownRepository $townRepository
    )
    {
        $patient = new Patient();
        $patientForm = $this->createForm(PatientRegisterType::class, $patient);
        $patientForm->handleRequest($request);

        if ($request->isMethod('POST') && $patientForm->isSubmitted() && $patientForm->isValid()) {
            $selectedCountry = $request->request->get('country');
            $selectedDepartment = $request->request->get('department');
            //$city = get_object_vars(json_decode($request->request->get('city')));

            $slugger = new Slugify();
            $departSlug = $slugger->slugify($selectedDepartment);
            $department = $selectedCountry === 'fr' ?
                $departmentRepository->findOneBy(['country' => $selectedCountry, 'code' => $selectedDepartment]) :
                $departmentRepository->findOneBy(['country' => $selectedCountry, 'slug' => $departSlug])
            ;

            if ($patientForm->getData() instanceof Patient) {
                /** @var Patient $user */
                $user = $patientForm->getData();
                $user->setCountry($selectedCountry);
                if ($department instanceof Department) {
                    $user->setDepartment($department);
                    $user->setScalarDepartment($departSlug);
                } else {
                    $user->setDepartment(null);
                    $user->setScalarDepartment($departSlug);
                }
                $user = $user->setUniqueEmailToken();
                $user = $user->setPassword($encoder->encodePassword($user, $user->getPassword()));
                $emailToken = $user->getEmailToken();
                $mailerFactory->createAndSend(
                    "Validation de votre inscription",
                    $user->getEmail(),
                    $this->renderView(
                        'email/patient_registration.html.twig',
                        ['email_token' => $emailToken, 'project_url' => $_ENV['PROJECT_URL']]
                    ),
                    null
                );
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash("success","Votre compte a été créé avec succès !");
                return $this->redirectToRoute('registration_waiting_for_email_validation', [], Response::HTTP_CREATED);
            }
        }

        return $this->render(
            'public/ask_for_help.html.twig',
            [
                'patient_register_form' => $patientForm->createView(),
                'https_url' => getenv('project_url')."/demander-de-l-aide"
            ]
        );
    }

    /**
     * @Route(path="/mot-de-passe/oublie", name="forgot_password")
     * @param Request $request
     * @param UserRepository $repository
     */
    public function forgotPassword(
        Request $request,
        UserRepository $repository,
        MailerFactory $mailerFactory, EntityManagerInterface $manager
    )
    {
        $form = $this->createForm(ForgotPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->getData()['email'];
            $user = $repository->findOneBy(['email' => $email]);
            if ($user instanceof User) {
                $user->setPasswordResetToken(uniqid('pwd_reset_', true));
                $manager->flush();
                $mailerFactory->createAndSend(
                    "Réinitialisation de votre mot de passe",
                    $user->getEmail(),
                    $this->renderView(
                        'email/user_reset_email.html.twig',
                        [
                            'project_url' => $_ENV['PROJECT_URL'],
                            'token' => $user->getPasswordResetToken()
                        ]
                    ),
                    null
                );
                $this->addFlash('success', "Un email vous a été envoyé, pensez à vérifier vos spams.");
                return $this->redirectToRoute('forgot_password');
            } else {
                $this->addFlash('error', "Nous n'avons pas trouvé votre adresse email.");
                return $this->redirectToRoute('forgot_password');
            }
        }
        return $this->render(
            'security/forgot_password.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route(path="/mot-de-passe/reinitialisation", name="password_reset")
     * @param Request $request
     * @param UserRepository $userRepository
     * @param MailerFactory $mailerFactory
     * @param EntityManagerInterface $manager
     * @param UserPasswordEncoderInterface $encoder
     */
    public function resetPassword(
        Request $request,
        UserRepository $userRepository,
        MailerFactory $mailerFactory,
        EntityManagerInterface $manager,
        UserPasswordEncoderInterface $encoder
    )
    {
        $token = $request->query->get('token');
        $user = $userRepository->findOneBy(['passwordResetToken' => $token]);
        if (!$user instanceof User || null === $user->getEmail()) {
            $this->addFlash('error', "Cet utilisateur n'existe pas");
            return $this->redirectToRoute('index');
        }
        $form = $this->createForm(PasswordResetType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $pass = $form->getData()['password'];
            $encoded = $encoder->encodePassword($user, $pass);
            $user->setPassword($encoded);
            $user->setPasswordResetToken(null);
            $mailerFactory->createAndSend(
                "Mot de passe réinitialisé",
                $user->getEmail(),
                $this->renderView(
                    'email/user_reset_password_success.html.twig'
                ),
                null
            );
            $manager->flush();
            $this->addFlash('success', "Vous pouvez désormais vous connecter avec votre nouveau mot de passe.");;
            return $this->redirectToRoute('app_login');
        }
        return $this->render(
            'security/reset_password.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    private function getOrCreateCity(
        string $selectedCountry,
        TownRepository $townRepository,
        Department $department,
        array $city
    ): Town
    {
        if ($selectedCountry === 'fr') {
            $existingTown = $townRepository->findOneBy(['department' => $department, 'name' => $city["nom"]]);
        } elseif ($selectedCountry === 'be') {
            $existingTown = $townRepository->findOneBy(['department' => $department, 'name' => $city["localite"]]);
        } elseif ($selectedCountry === 'lu') {
            $existingTown = $townRepository->findOneBy(['department' => $department, 'name' => $city["COMMUNE"]]);
        } elseif ($selectedCountry === 'ch') {
            $existingTown = $townRepository->findOneBy(['department' => $department, 'name' => $city["city"]]);
        }

        if (!$existingTown instanceof Town) {
            if ($selectedCountry === 'fr') {
                $town = $this->createFrCity($city);
            } elseif ($selectedCountry === 'be') {
                $town = $this->createBeCity($city);
            } elseif ($selectedCountry === 'lu') {
                $town = $this->createLuCity($city);
            } elseif ($selectedCountry === 'ch') {
                $town = $this->createChCity($city);
            }
        }
    }

    /**
     * @Route(path="/proposer-mon-aide", name="therapist_register")
     */
    public function therapistRegister(
        Request $request,
        UserPasswordEncoderInterface $encoder,
        MailerFactory $mailer,
        EntityManagerInterface $entityManager,
        DepartmentRepository $departmentRepository,
        TownRepository $townRepository
    )
    {
        $therapist = new Therapist();
        $therapistForm = $this->createForm(TherapistRegisterType::class, $therapist);
        $therapistForm->handleRequest($request);

        if ($request->isMethod('POST') && $therapistForm->isSubmitted() && $therapistForm->isValid()) {
            $selectedCountry = $request->request->get('country');
            $selectedDepartment = $request->request->get('department');
            //$city = get_object_vars(json_decode($request->request->get('city')));

            $slugger = new Slugify();
            $departSlug = $slugger->slugify($selectedDepartment);
            $department = $selectedCountry === 'fr' ?
                $departmentRepository->findOneBy(['country' => $selectedCountry, 'code' => $selectedDepartment]) :
                $departmentRepository->findOneBy(['country' => $selectedCountry, 'slug' => $departSlug])
            ;
            if ($therapistForm->getData() instanceof Therapist) {
                /** @var Therapist $user */
                $user = $therapistForm->getData();
                $user->setCountry($selectedCountry);
                if ($department instanceof Department) {
                    $user->setDepartment($department);
                    $user->setScalarDepartment($departSlug);
                } else {
                    $user->setDepartment(null);
                    $user->setScalarDepartment($departSlug);
                }
                $user = $user->setUniqueEmailToken();
                $user = $user->setPassword($encoder->encodePassword($user, $user->getPassword()));
                $emailToken = $user->getEmailToken();
                $mailer->createAndSend(
                    "Validation de votre inscription",
                    $user->getEmail(),
                    $this->renderView(
                        'email/therapist_registration.html.twig',
                        ['email_token' => $emailToken, 'project_url' => $_ENV['PROJECT_URL']]
                    ),
                    null
                );
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash("success","Votre compte a été créé avec succès !");
                return $this->redirectToRoute('registration_waiting_for_email_validation', [], Response::HTTP_CREATED);
            }
        }

        return $this->render(
            'public/therapist_register.html.twig',
            [
                'therapist_register_form' => $therapistForm->createView(),
                'https_url' => getenv('project_url')."/proposer-mon-aide"
            ]
        );
    }

    /**
     * @Route(path="/email/confirmation/{emailToken}")
     * @param Request $request
     */
    public function registrationConfirmationCheck(Request $request, RequestContext $requestContext, UserRepository $userRepository, TherapistRepository $therapistRepository, EntityManagerInterface $entityManager)
    {
        $token = substr($requestContext->getPathInfo(), 20, strlen($requestContext->getPathInfo()));
        $user = $userRepository->findOneBy(['emailToken' => $token]);
        if ($user && false === $user->isActive()) {
            $user->setEmailToken('')->setIsActive(true);
            $entityManager->flush();
            return $this->redirectToRoute('app_login');
        } else if ($user && true === $user->isActive()) {
            $this->addFlash('error', "Votre nouvelle adresse email vient d'être confirmée.");
            return $this->redirectToRoute('app_login');
        } else {
            $this->addFlash('error', "Votre code de confirmation n'est pas valide, veuillez contacter le support de la plateforme ou créer votre compte.");
            return $this->redirectToRoute('therapist_register');
        }
    }

    /**
     * @Route(path="/proposer-mon-aide/en-attente-de-validation", name="registration_waiting_for_email_validation")
     * @return Response
     */
    public function registrationSuccessfull()
    {
        return $this->render(
            'public/registration_successfull.html.twig'
        );
    }

    /**
     * @Route(path="/comment-ca-marche", name="how_it_works")
     */
    public function howItWorks()
    {
        return $this->render(
            'public/how_it_works.html.twig'
        );
    }

    /**
     * @Route(path="/la-gestalt-therapie", name="gestalt_therapy")
     */
    public function whatIsGestalt()
    {
        return $this->render(
            'public/gestalt_therapy.html.twig'
        );
    }

    /**
     * @Route(path="/numeros-d-urgence", name="emergency_numbers")
     */
    public function emergencyNumbers()
    {
        return $this->render(
            'public/emergency_numbers.html.twig'
        );
    }

    /**
     * @Route(path="/le-projet", name="the_project")
     */
    public function theProject()
    {
        return $this->render(
            'public/the_project.html.twig'
        );
    }

    /**
     * @Route(path="/qui-sommes-nous", name="who_are_we")
     */
    public function whoAreWe()
    {
        return $this->render(
            'public/who_are_we.html.twig'
        );
    }

    /**
     * @Route(path="/politique-de-protection-des-donnees", name="data_privacy_policy")
     */
    public function dataPrivacy()
    {
        return $this->render(
            'public/data_privacy_policy.html.twig'
        );
    }

    /**
     * @Route(path="/conditions-d-utilisation", name="terms_of_use")
     */
    public function termsOfUse()
    {
        return $this->render(
            'public/terms_of_use.html.twig'
        );
    }

    /**
     * @Route(path="/mentions-legales", name="legal_notices")
     */
    public function legalNotices()
    {
        return $this->render(
            'public/legal_notices.html.twig'
        );
    }

    /**
     * @Route(path="/coming-soon", name="coming_soon")
     * @return Response
     */
    public function comingSoon()
    {
        return $this->render('public/coming_soon.html.twig');
    }
}
