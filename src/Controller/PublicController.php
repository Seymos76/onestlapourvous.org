<?php


namespace App\Controller;


use App\Entity\EmailReport;
use App\Entity\Patient;
use App\Entity\Therapist;
use App\Entity\User;
use App\Form\ForgotPasswordType;
use App\Form\PasswordResetType;
use App\Form\PatientRegisterType;
use App\Form\TherapistRegisterType;
use App\Password\ForgotPassword;
use App\Password\ResetPassword;
use App\Registration\Registration;
use App\Repository\DepartmentRepository;
use App\Repository\UserRepository;
use App\Services\FixturesTrait;
use App\Services\MailerFactory;
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
        Registration $registration
    )
    {
        $patient = new Patient();
        $patientForm = $this->createForm(PatientRegisterType::class, $patient);
        $patientForm->handleRequest($request);

        if ($request->isMethod('POST') && $patientForm->isSubmitted() && $patientForm->isValid()) {
            if (!$patientForm->getData() instanceof Patient) {
                throw new \Exception("Les données envoyées ne correspondent pas à ce qui est attendu.");
            }
            $localisation = $registration->getDepartment($request);
            $user = $registration->registerUser($patientForm->getData(), $localisation);
            $registration->sendRegistrationEmail($user, Patient::class);
            $this->addFlash("success","Votre compte a été créé avec succès !");
            return $this->redirectToRoute('registration_waiting_for_email_validation', [], Response::HTTP_CREATED);
        }

        return $this->render(
            'public/ask_for_help.html.twig',
            [
                'patient_register_form' => $patientForm->createView(),
                'https_url' => $_ENV['PROJECT_URL']."demander-de-l-aide"
            ]
        );
    }

    /**
     * @Route(path="/proposer-mon-aide", name="therapist_register")
     */
    public function therapistRegister(
        Request $request,
        UserPasswordEncoderInterface $encoder,
        MailerFactory $mailerFactory,
        EntityManagerInterface $entityManager,
        DepartmentRepository $departmentRepository,
        Registration $registration
    )
    {
        $therapist = new Therapist();
        $therapistForm = $this->createForm(TherapistRegisterType::class, $therapist);
        $therapistForm->handleRequest($request);

        if ($request->isMethod('POST') && $therapistForm->isSubmitted() && $therapistForm->isValid()) {
            if (!$therapistForm->getData() instanceof Therapist) {
                throw new \Exception("Les données envoyées ne correspondent pas à ce qui est attendu.");
            }
            $localisation = $registration->getDepartment($request);
            $user = $registration->registerUser($therapistForm->getData(), $localisation);
            $registration->sendRegistrationEmail($user, Therapist::class);
            $this->addFlash("success","Votre compte a été créé avec succès !");
            return $this->redirectToRoute('registration_waiting_for_email_validation', [], Response::HTTP_CREATED);
        }

        return $this->render(
            'public/therapist_register.html.twig',
            [
                'therapist_register_form' => $therapistForm->createView(),
                'https_url' => $_ENV['PROJECT_URL']."proposer-mon-aide"
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
        ForgotPassword $forgotPassword
    )
    {
        $form = $this->createForm(ForgotPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->getData()['email'];
            try {
                $response = $forgotPassword->resetPasswordFor($email);
            } catch (\Exception $exception) {
                echo "Un problème lors de la réinitialisation du mot de passe: ".$exception->getMessage();
            }
            $this->addFlash($response['message']['type'], $response['message']['message']);
            return $this->redirectToRoute($response['route']);
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
        UserPasswordEncoderInterface $encoder,
        ResetPassword $resetPassword
    )
    {
        $token = $request->query->get('token');
        $user = $userRepository->findOneBy(['passwordResetToken' => $token]);
        if (!$user instanceof User) {
            $this->addFlash('error', "Cet utilisateur n'existe pas");
            return $this->redirectToRoute('forgot_password');
        }
        $form = $this->createForm(PasswordResetType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $resetPassword->reset($user, $form->getData()['password']);
            $mailerFactory->createAndSend(
                [
                    "Mot de passe réinitialisé",
                    $user->getEmail(),
                    $this->renderView(
                        'email/user_reset_password_success.html.twig'
                    ),
                    null
                ],
                EmailReport::TYPE_PASSWORD_RESET_SUCCESS
            );
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

    /**
     * @Route(path="/email/confirmation/{emailToken}")
     */
    public function registrationConfirmationCheck(
        RequestContext $requestContext,
        Registration $registration
    )
    {
        $token = Registration::getToken($requestContext);
        try {
            $arr = $registration->activateUserOrNot($token);
        } catch (\Exception $exception) {
            echo "Erreur lors de l'activation d'une adresse email nouvellement inscrite: ".$exception->getMessage();
        }
        $this->addFlash($arr['message']['type'], $arr['message']['message']);
        return $this->redirectToRoute($arr['route']);
    }

    /**
     * @Route(path="/en-attente-de-validation", name="registration_waiting_for_email_validation")
     * @return Response
     */
    public function registrationSuccessful()
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
