<?php


namespace App\Controller;


use App\Appointment\AppointmentAccessor;
use App\Appointment\AppointmentCanceller;
use App\Appointment\AppointmentConfirmation;
use App\Appointment\AppointmentManager;
use App\Entity\Appointment;
use App\Entity\EmailReport;
use App\Entity\History;
use App\Entity\Patient;
use App\Entity\Therapist;
use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\PatientSettingsType;
use App\History\HistoryHelper;
use App\Localisation\UserLocalisation;
use App\Password\ChangePassword;
use App\Registration\Registration;
use App\Repository\AppointmentRepository;
use App\Repository\HistoryRepository;
use App\Repository\PatientRepository;
use App\Services\MailerFactory;
use App\User\UserAccessor;
use App\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class PatientController
 * @package App\Controller
 * @Route(path="/patient")
 */
class PatientController extends AbstractController
{
    const USER_ROLE = Patient::class;

    private $patientRepository;

    public function __construct(PatientRepository $patientRepository)
    {
        $this->patientRepository = $patientRepository;
    }

    /**
     * @Route(path="/dashboard", name="patient_dashboard")
     * @return Response
     */
    public function dashboard()
    {
        return $this->render(
            'patient/dashboard.html.twig'
        );
    }

    /**
     * @Route(path="/rendez-vous", name="patient_appointments")
     * @return Response
     */
    public function appointments(
        UserManager $userManager,
        AppointmentAccessor $appointmentAccessor
    )
    {
        $currentPatient = $userManager->getCurrentUser($this->getUser()->getUsername(), self::USER_ROLE);
        $appointsAndHistory = $appointmentAccessor->getBookingsByPatient($currentPatient);
        $appoints = $appointmentAccessor->filterAppointments($appointsAndHistory);
        return $this->render(
            'patient/appointments.html.twig',
            [
                'appoints' => $appoints
            ]
        );
    }

    /**
     * @Route(path="/rendez-vous/annuler/{id}", name="patient_appointment_cancel")
     * @ParamConverter(name="id", class="App\Entity\Appointment")
     * @return Response
     */
    public function appointmentCancel(
        Appointment $appointment,
        EntityManagerInterface $entityManager,
        MailerFactory $mailerFactory,
        HistoryHelper $historyHelper,
        AppointmentCanceller $canceller
    )
    {
        if(!$appointment instanceof Appointment) {
            $this->addFlash('error', "Erreur lors de l'annulation.");
            return $this->redirectToRoute('patient_appointments');
        }
        if (Appointment::STATUS_BOOKED !== $appointment->getStatus()) {
            $this->addFlash('error', "Erreur lors de l'annulation.");
            return $this->redirectToRoute('patient_appointments');
        }
        $canceller->cancel($appointment);
        // add booking cancel history
        $historyHelper->addHistoryItem(History::ACTION_CANCELLED_BY_PATIENT, $appointment);
        $mailerFactory->createAndSend(
            [
                "Annulation du rendez-vous",
                $appointment->getTherapist()->getEmail(),
                $this->renderView('email/appointment_cancelled_from_patient.html.twig', ['appointment' => $appointment]),
                null
            ],
            EmailReport::TYPE_BOOKING_CANCELLATION_BY_PATIENT
        );
        $entityManager->flush();
        $this->addFlash('info', "Rendez-vous annulé. Vous allez recevoir un mail de confirmation de l'annulation.");
        return $this->redirectToRoute('patient_appointments');
    }

    /**
     * @Route(path="/recherche", name="patient_research")
     * @return Response
     */
    public function research(UserManager $userManager)
    {
        return $this->render(
            'patient/research.html.twig',
            [
                'current_user' => $userManager->getCurrentUser($this->getUser()->getUsername(), Patient::class)
            ]
        );
    }

    /**
     * @Route(path="/recherche/therapeute/{id}", name="patient_research_by_therapist")
     * @ParamConverter(name="id", class="App\Entity\Therapist")
     * @return Response
     */
    public function researchByTherapist(
        Therapist $therapist,
        AppointmentRepository $appointmentRepository,
        Request $request,
        EntityManagerInterface $entityManager,
        UserAccessor $userAccessor,
        AppointmentAccessor $appointmentAccessor,
        AppointmentManager $appointmentManager
    )
    {
        $currentUser = $this->getUser();
        $patient = $userAccessor->getPatientByEmail($currentUser->getUsername());
        $appoints = $appointmentRepository->getAppointmentsByTherapist($therapist);

        if ($request->isMethod("POST")) {
            $appointment = $appointmentAccessor->getById($request->request->get('booking_id'));
            if (!$appointment instanceof Appointment) {
                throw new \Exception("Ce créneau n'est pas valide.");
            }
            $appointmentManager->book($appointment, $patient);
            $entityManager->flush();
            return $this->redirectToRoute('patient_confirm_booking', ['id' => $appoint->getId()]);
        }
        return $this->render(
            'patient/research_by_therapist.html.twig',
            [
                'appoints' => $appoints,
                'patient_id' => $patient->getId()
            ]
        );
    }

    /**
     * @Route(path="/patient/confirm-booking/{id}", name="patient_confirm_booking")
     * @ParamConverter(name="id", class="App\Entity\Appointment")
     * @param Appointment $appointment
     */
    public function confirmBookingWithTherapist(
        Appointment $appointment,
        Request $request,
        HistoryHelper $historyHelper,
        EntityManagerInterface $entityManager,
        MailerFactory $mailerFactory,
        AppointmentAccessor $accessor,
        AppointmentConfirmation $appointmentConfirmation
    )
    {
        if ($request->isMethod("POST")) {
            $appointment = $accessor->getById($request->request->get('booking_id'));
            $appointmentConfirmation->confirm($appointment);
            $historyHelper->addHistoryItem(History::ACTION_BOOKED, $appointment);

            $mailerFactory->createAndSend([
                "Confirmation de rendez-vous",
                $appointment->getPatient()->getEmail(),
                $this->renderView('email/appointment_booked_patient.html.twig', ['appointment' => $appointment,]),
                null],
                EmailReport::TYPE_BOOKING_CONFIRMATION
            );
            $mailerFactory->createAndSend([
                "Confirmation de rendez-vous",
                $appointment->getTherapist()->getEmail(),
                $this->renderView('email/appointment_booked_therapist.html.twig', ['appointment' => $appointment]),
                null],
                EmailReport::TYPE_BOOKING_CONFIRMATION
            );

            $entityManager->flush();
            $this->addFlash('success', "Votre rendez-vous est confirmé, un mail de confirmation vous a été envoyé !");
            return $this->redirectToRoute('patient_appointments');
        }
        return $this->render(
            'patient/appointment_by_therapist_confirm.html.twig',
            [
                'booking' => $appointment
            ]
        );
    }

    /**
     * @Route(path="/historique", name="patient_history")
     * @return Response
     */
    public function history(
        HistoryRepository $historyRepository,
        Request $request,
        PaginatorInterface $paginator,
        UserAccessor $userAccessor
    )
    {
        $currentUser = $this->getUser();
        $patient = $userAccessor->getPatientByEmail($currentUser->getUsername());
        $history = $historyRepository->findByPatient($patient);

        $paginated = $paginator->paginate(
            $history,
            $request->query->getInt('page', 1),
            10
        );
        return $this->render(
            'patient/history.html.twig',
            [
                'history' => $paginated
            ]
        );
    }

    /**
     * @Route(path="/parametres", name="patient_settings")
     * @return Response
     */
    public function settings(
        Request $request,
        EntityManagerInterface $manager,
        MailerFactory $mailerFactory,
        Registration $registration,
        UserLocalisation $userLocalisation
    )
    {
        /** @var UserInterface $currentUser */
        $currentUser = $this->getUser();
        $prevEmail = $currentUser->getEmail();
        $settingsType = $this->createForm(
            PatientSettingsType::class,
            $currentUser
        );
        $settingsType->handleRequest($request);
        if ($request->isMethod('POST') && $settingsType->isSubmitted() && $settingsType->isValid()) {
            $localisation = $registration->getDepartment($request);
            /** @var Patient $user */
            $user = $settingsType->getData();

            $userLocalisation->relocalizeUser($user, $localisation);

            if ($user->getEmail() !== $prevEmail) {
                $user->setUniqueEmailToken();
                $mailerFactory->createAndSend(
                    ["Changement de votre adresse email",
                    $user->getEmail(),
                    $this->renderView(
                        'email/user_change_email.html.twig',
                        ['email_token' => $user->getEmailToken(), 'project_url' => $_ENV['PROJECT_URL']]
                    ),
                    null],
                    EmailReport::TYPE_CHANGE_EMAIL_ADDR
                );
                $manager->flush();
                $this->addFlash('success', "Vous allez recevoir un mail pour confirmer votre nouvelle adresse email.");
                return $this->redirectToRoute('therapist_settings');
            }
            $manager->flush();
            $this->addFlash('success',"Informations mises à jour !");
            return $this->redirectToRoute('patient_settings');
        }
        return $this->render(
            'patient/settings.html.twig',
            [
                'settings_form' => $settingsType->createView()
            ]
        );
    }

    /**
     * @Route(path="/securite", name="patient_security")
     * @return Response
     */
    public function security(
        Request $request,
        EntityManagerInterface $manager,
        AppointmentRepository $appointmentRepository,
        ChangePassword $changePassword
    )
    {
        $user = $this->getUser();
        $changePasswordForm = $this->createForm(ChangePasswordType::class, $user);
        $changePasswordForm->handleRequest($request);
        $appointments = $appointmentRepository->findBy(['patient' => $user, 'status' => Appointment::STATUS_BOOKED]);
        if ($request->isMethod('POST') && $changePasswordForm->isSubmitted() && $changePasswordForm->isValid()) {
            $changePassword->change($user, $changePasswordForm->getData()->getPassword());
            $manager->flush();
            $this->addFlash('success',"Votre mot de passe a été mis à jour !");
            return $this->redirectToRoute('patient_security');
        }
        return $this->render(
            'patient/security.html.twig',
            [
                'change_password_form' => $changePasswordForm->createView(),
                'appointments' => $appointments
            ]
        );
    }

    /**
     * @Route(path="/account/delete/{id}", name="patient_account_delete")
     */
    public function deleteAccount(
        Request $request,
        UserAccessor $userAccessor,
        UserManager $userManager,
        MailerFactory $mailerFactory
    )
    {
        $user = $userAccessor->getUserById($request->attributes->get('id'));
        if (!$user instanceof User) {
            $this->addFlash('error', "La suppression de votre compte a échoué.");
            return $this->redirectToRoute('patient_security');
        }
        $mailerFactory->createAndSend(
            ["Suppression de votre compte",
                $user->getEmail(),
                $this->renderView('email/user_delete_account.html.twig'),
                null],
            EmailReport::TYPE_ACCOUNT_DELETION
        );
        // delete user
        $userManager->deleteAccount($user);
        $this->addFlash('success', "Votre compte a été correctement supprimé.");
        return $this->redirectToRoute('app_logout');
    }
}
