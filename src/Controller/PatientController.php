<?php


namespace App\Controller;


use App\Entity\Appointment;
use App\Entity\Department;
use App\Entity\History;
use App\Entity\Patient;
use App\Entity\Therapist;
use App\Form\ChangePasswordType;
use App\Form\PatientSettingsType;
use App\Repository\AppointmentRepository;
use App\Repository\DepartmentRepository;
use App\Repository\HistoryRepository;
use App\Repository\PatientRepository;
use App\Services\HistoryHelper;
use App\Services\MailerFactory;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class PatientController
 * @package App\Controller
 * @Route(path="/patient")
 */
class PatientController extends AbstractController
{
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
    public function appointments(AppointmentRepository $appointmentRepository)
    {
        $currentPatient = $this->getCurrentPatient();
        $appointsAndHistory = $appointmentRepository->findBy(
            ['patient' => $currentPatient, 'status' => Appointment::STATUS_BOOKED]
        );
        $appoints = array_filter($appointsAndHistory, function ($a, $k) {
            return !$a instanceof History;
        }, ARRAY_FILTER_USE_BOTH);
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
        HistoryHelper $historyHelper
    )
    {
        if ($appointment instanceof Appointment && $appointment->getStatus() === Appointment::STATUS_BOOKED) {
            $appointment->setBooked(false);
            $appointment->setCancelled(true);
            // add booking cancel history
            $historyHelper->addHistoryItem(History::ACTION_CANCELLED_BY_PATIENT, $appointment);
            $mailerFactory->createAndSend(
                "Annulation du rendez-vous",
                $appointment->getTherapist()->getEmail(),
                null,
                $this->renderView('email/appointment_cancelled_from_patient.html.twig', ['appointment' => $appointment])
            );
            $appointment->setPatient(null);
            $appointment->setStatus(Appointment::STATUS_AVAILABLE);
            $entityManager->flush();
            $this->addFlash('info', "Rendez-vous annulé. Vous allez recevoir un mail de confirmation de l'annulation.");
            return $this->redirectToRoute('patient_appointments');
        } else {
            $this->addFlash('error', "Erreur lors de l'annulation.");
            return $this->redirectToRoute('patient_appointments');
        }
    }

    /**
     * @Route(path="/recherche", name="patient_research")
     * @return Response
     */
    public function research()
    {
        return $this->render(
            'patient/research.html.twig',
            [
                'current_user' => $this->getCurrentPatient()
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
        EntityManagerInterface $entityManager
    )
    {
        $patient = $this->getCurrentPatient();
        $patientId = $patient->getId();
        $appoints = $appointmentRepository->getAppointmentsByTherapist($therapist);

        if ($request->isMethod("POST")) {
            $appoint = $appointmentRepository->find($request->request->get('booking_id'));
            if ($appoint instanceof Appointment) {
                $appoint->setPatient($patient);
                $appoint->setStatus(Appointment::STATUS_BOOKING);
                $entityManager->flush();
                return $this->redirectToRoute('patient_confirm_booking', ['id' => $appoint->getId()]);
            } else {
                $this->addFlash('error', "Un problème est survenu");
                return $this->redirectToRoute('patient_research_by_therapist', ['id' => $therapist->getId()]);
            }
        }
        return $this->render(
            'patient/research_by_therapist.html.twig',
            [
                'appoints' => $appoints,
                'patient_id' => $patientId
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
        AppointmentRepository $appointmentRepository
    )
    {
        if ($request->isMethod("POST")) {
            $appointment = $appointmentRepository->find($request->request->get('booking_id'));
            $appointment->setBooked(true);
            $appointment->setStatus(Appointment::STATUS_BOOKED);
            $historyHelper->addHistoryItem(History::ACTION_BOOKED, $appointment);

            $mailerFactory->createAndSend(
                "Confirmation de rendez-vous",
                $appointment->getPatient()->getEmail(),
                null,
                $this->renderView('email/appointment_booked_patient.html.twig', ['appointment' => $appointment])
            );
            dump($appointment);

            $mailerFactory->createAndSend(
                "Confirmation de rendez-vous",
                $appointment->getTherapist()->getEmail(),
                null,
                $this->renderView('email/appointment_booked_therapist.html.twig', ['appointment' => $appointment])
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
    public function history(HistoryRepository $historyRepository)
    {
        $currentUser = $this->getCurrentPatient();
        $history = $historyRepository->findByPatient($currentUser);
        return $this->render(
            'patient/history.html.twig',
            [
                'history' => $history
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
        DepartmentRepository $departmentRepository,
        MailerFactory $mailerFactory
    )
    {
        $currentUser = $this->getCurrentPatient();
        $prevEmail = $currentUser->getEmail();
        $settingsType = $this->createForm(PatientSettingsType::class, $currentUser);
        $settingsType->handleRequest($request);
        if ($request->isMethod('POST') && $settingsType->isSubmitted() && $settingsType->isValid()) {
            /** @var Patient $user */
            $user = $settingsType->getData();
            if ($request->request->get('country') !== null) {
                $user->setCountry($request->request->get('country'));
            }
            if ($request->request->get('department') !== null) {
                $department = $departmentRepository->find($request->request->get('department'));
                if ($department instanceof Department) {
                    $user->setDepartment($department);
                } else {
                    $user->setScalarDepartment($department);
                }
            }
            if ($user->getEmail() !== $prevEmail) {
                $user->setUniqueEmailToken();
                $mailerFactory->createAndSend(
                    "Changement de votre adresse email",
                    $user->getEmail(),
                    null,
                    $this->renderView(
                        'email/user_change_email.html.twig',
                        ['email_token' => $user->getEmailToken(), 'project_url' => $_ENV['PROJECT_URL']]
                    )
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
        UserPasswordEncoderInterface $encoder,
        EntityManagerInterface $manager
    )
    {
        $user = $this->getCurrentPatient();
        $changePasswordForm = $this->createForm(ChangePasswordType::class, $user);
        $changePasswordForm->handleRequest($request);
        if ($request->isMethod('POST') && $changePasswordForm->isSubmitted() && $changePasswordForm->isValid()) {
            $newPassword = $changePasswordForm->getData()->getPassword();
            $encoded = $encoder->encodePassword($user, $newPassword);
            $user->setPassword($encoded);
            $manager->flush();
            $this->addFlash('success',"Votre mot de passe a été mis à jour !");
            return $this->redirectToRoute('patient_security');
        }
        return $this->render(
            'patient/security.html.twig',
            [
                'change_password_form' => $changePasswordForm->createView(),
                'appointments' => $user->getAppointments()
            ]
        );
    }

    /**
     * @Route(path="/account/delete", name="patient_account_delete")
     */
    public function deleteAccount(
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordEncoderInterface $encoder,
        MailerFactory $mailerFactory
    )
    {
        $user = $this->getCurrentPatient();
        if ($user instanceof Patient) {
            $userPassword = $request->request->get('password');
            if ($encoder->isPasswordValid($user, $userPassword)) {
                // send email account deletion
                $mailerFactory->createAndSend(
                    "Suppression de votre compte",
                    $user->getEmail(),
                    null,
                    $this->renderView('email/user_delete_account.html.twig')
                );
                // delete user
                $manager->remove($user);
                $manager->flush();
                $session = new Session();
                $session->invalidate();
                $this->addFlash('success', "Votre compte a été correctement supprimé.");
                return $this->redirectToRoute('app_logout');
            } else {
                $this->addFlash('error', "Votre mot de passe est invalide.");
                return $this->redirectToRoute('patient_security');
            }
        }
        return $this->redirectToRoute('patient_security');
    }

    private function getCurrentPatient(): Patient
    {
        return $this->patientRepository->findOneBy(['email' => $this->getUser()->getUsername()]);
    }
}
