<?php


namespace App\Controller;


use App\Entity\Appointment;
use App\Entity\Department;
use App\Entity\EmailReport;
use App\Entity\History;
use App\Entity\Patient;
use App\Entity\Therapist;
use App\Entity\User;
use App\Form\AppointmentType;
use App\Form\ChangePasswordType;
use App\Form\TherapistAppointmentCancellationMessageType;
use App\Form\TherapistSettingsType;
use App\Repository\AppointmentRepository;
use App\Repository\DepartmentRepository;
use App\Repository\HistoryRepository;
use App\Repository\TherapistRepository;
use App\Repository\UserRepository;
use App\Services\HistoryHelper;
use App\Services\MailerFactory;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class TherapistController
 * @package App\Controller
 * @Route(path="/therapeute")
 */
class TherapistController extends AbstractController
{
    private $therapistRepository;

    public function __construct(TherapistRepository $therapistRepository)
    {
        $this->therapistRepository = $therapistRepository;
    }

    /**
     * @Route(path="/dashboard", name="therapist_dashboard")
     * @return Response
     */
    public function dashboard()
    {
        return $this->render(
            'therapist/dashboard.html.twig'
        );
    }

    /**
     * @Route(path="/bookings", name="therapist_bookings")
     * @return Response
     */
    public function bookings(AppointmentRepository $appointmentRepository)
    {
        $currentUser = $this->getCurrentTherapist();

        return $this->render(
            'therapist/bookings.html.twig',
            [
                'bookings' => $appointmentRepository->findBy(
                    [
                        'therapist' => $currentUser,
                        'status' => Appointment::STATUS_BOOKED
                    ]
                ),
            ]
        );
    }

    /**
     * @Route(path="/booking/{id}", name="therapist_booking_status")
     * @ParamConverter(name="id", class="App\Entity\Appointment")
     */
    public function bookingStatus(
        Appointment $appointment,
        Request $request,
        EntityManagerInterface $entityManager,
        HistoryHelper $historyHelper,
        MailerFactory $mailerFactory
    )
    {
        $status = $request->query->get('status');
        $patient = $appointment->getPatient();
        if ($status === Appointment::STATUS_DISHONORED) {
            if (null === $patient->getMalus()) {
                $malus = 1;
                $patient->setMalus($malus);
            } else {
                $malus = $patient->getMalus() + 1;
                $patient->setMalus($malus);
            }
            $historyHelper->addHistoryItem(History::ACTION_DISHONORED, $appointment);
        } else {
            $historyHelper->addHistoryItem(History::ACTION_HONORED, $appointment);
        }
        if ($patient->getMalus() >= 3) {
            $mailerFactory->createAndSend(
                "3 rdv non honorés...",
                $appointment->getTherapist()->getEmail(),
                $this->renderView('email/patient_malus.html.twig', ['patient' => $patient]),
                null,
                EmailReport::TYPE_ALERT_DISHONORED
            );
        }
        $appointment->setStatus($status);
        $entityManager->flush();
        $this->addFlash('success', "Statut de la réservation mis à jour, disponible dans l'historique !");
        return $this->redirectToRoute('therapist_bookings');
    }

    /**
     * @Route(path="/booking/cancel/{id}", name="therapist_booking_cancel", methods={"POST","GET"})
     * @ParamConverter(name="id", class="App\Entity\Appointment")
     * @param Appointment $appointment
     * @return Response
     */
    public function bookingCancellation(
        Appointment $appointment,
        Request $request,
        EntityManagerInterface $entityManager,
        MailerFactory $mailer,
        HistoryHelper $historyHelper
    )
    {
        $currentUser = $this->getCurrentTherapist();
        if (!$appointment instanceof Appointment) {
            $this->addFlash('error',"Réservation introuvable...");
            return $this->redirectToRoute('therapist_bookings');
        }
        if ($appointment->getStatus() !== Appointment::STATUS_BOOKED && !$appointment->getPatient() instanceof Patient) {
            $this->addFlash('error', "Ce créneau n'est pas réservé...");
            return $this->redirectToRoute('therapist_bookings');
        }
        if ($appointment->getStatus() !== Appointment::STATUS_BOOKED || !$appointment->getPatient() instanceof Patient) {
            $this->addFlash('error', "Ce créneau n'a pas été réservé correctement...");
            return $this->redirectToRoute('therapist_bookings');
        }

        $form = $this->createForm(TherapistAppointmentCancellationMessageType::class, $appointment);
        $form->handleRequest($request);

        if ($request->isMethod("POST") && $form->isSubmitted() && $form->isValid()) {
            $appointment->setBooked(false);
            $patientEmail = $appointment->getPatient()->getEmail();
            $appointment->setStatus(Appointment::STATUS_CANCELLED);
            $appointment->setPatient(null);
            $appointment->setStatus(Appointment::STATUS_TO_DELETE);
            $mailer->createAndSend(
                "Annulation du rendez-vous",
                $patientEmail,
                $this->renderView(
                    'email/appointment_cancelled_from_therapist.html.twig',
                    [
                        'appointment' => $appointment,
                        'project_url' => getenv('project_url')
                            ? getenv('project_url')
                            : $this->getParameter('project_url')
                    ]
                ),
                null,
                EmailReport::TYPE_BOOKING_CANCELLATION_BY_THERAPIST
            );
            $currentUser->removeAppointment($appointment);
            $entityManager->remove($appointment);
            $entityManager->flush();
            $this->addFlash('info', "Rendez-vous annulé, créneau supprimé et message envoyé.");
            return $this->redirectToRoute('therapist_bookings');
        }

        return $this->render(
            'therapist/booking_cancellation.html.twig',
            [
                'booking_cancellation_form' => $form->createView()
            ]
        );
    }

    /**
     * @Route(path="/availabilities", name="therapist_availabilities", defaults={"page"=1})
     * @return Response
     */
    public function availabilities(
        AppointmentRepository $appointmentRepository,
        Request $request,
        EntityManagerInterface $manager,
        PaginatorInterface $paginator
    )
    {
        $currentUser = $this->getCurrentTherapist();
        $appointment = new Appointment();
        $appointment->setTherapist($currentUser);
        $appointmentForm = $this->createForm(AppointmentType::class, $appointment);
        $appointmentForm->handleRequest($request);
        if ($appointmentForm->isSubmitted() && $appointmentForm->isValid()) {
            $manager->persist($appointment);
            $manager->flush();
            $this->addFlash('success',"Créneau ajouté !");
            return $this->redirectToRoute('therapist_availabilities');
        }

        $params = [];
        foreach ($request->query as $key => $value) {
            if ($value !== "") {
                $params[$key] = $value;
            }
        }

        if (count($params) === 0) {
            $appointments = $appointmentRepository->findAvailableBookingsByParams($params, $currentUser);
        } else {
            $appointments = $appointmentRepository->findAvailableBookingsByParams($params, $currentUser);
        }

        $paginated = $paginator->paginate(
            $appointments,
            $request->query->getInt('page', 1),
            10
        );

        $targetDate = new \DateTime('22-04-2020');
        $nowDate = new \DateTime();

        return $this->render(
            'therapist/availabilities.html.twig',
            [
                'appointment_form' => $appointmentForm->createView(),
                'availabilities' => $paginated ?? [],
                'total_appointments' => count($appointments),
                'filters' => $params,
                'permission' => ($targetDate >= $nowDate) ? false : true
            ]
        );
    }

    /**
     * @Route(path="/availabilities/{id}/edit", name="therapist_availability_edit")
     * @ParamConverter(name="id", class="App\Entity\Appointment")
     * @return Response
     */
    public function availabilitiesEdit(Appointment $appointment, Request $request, EntityManagerInterface $manager)
    {
        if ($appointment->getPatient() instanceof Patient) {
            $this->addFlash('error',"Ce créneau a été réservé, impossible de le modifier.");
            return $this->redirectToRoute('therapist_availabilities');
        }
        $appointmentForm = $this->createForm(AppointmentType::class, $appointment);
        $appointmentForm->handleRequest($request);
        if ($request->isMethod('POST') && $appointmentForm->isSubmitted() && $appointmentForm->isValid()) {
            $manager->flush();
            $this->addFlash('success',"Créneau modifié !");
            return $this->redirectToRoute('therapist_availabilities');
        }

        return $this->render(
            'therapist/availability_edit.html.twig',
            [
                'appointment_form' => $appointmentForm->createView(),
            ]
        );
    }

    /**
     * @Route(path="/availabilities/{id}/delete", name="therapist_availability_delete")
     * @return RedirectResponse
     */
    public function availabilitiesDelete(
        Request $request,
        AppointmentRepository $appointmentRepository,
        EntityManagerInterface $manager
    ): RedirectResponse
    {
        $appointId = $request->attributes->get('id');
        $appointment = $appointmentRepository->find((int)$appointId);
        if (!$appointment || !$appointment instanceof Appointment) {
            $this->addFlash('error', "Créneau introuvable...");
            return $this->redirectToRoute('therapist_availabilities');
        }
        if ($appointment->getPatient() instanceof Patient) {
            $this->addFlash('error',"Ce créneau a été réservé... veuillez l'annuler avant de le supprimer.");
            return $this->redirectToRoute('therapist_availabilities');
        } else {
            $manager->remove($appointment);
            $manager->flush();
            $this->addFlash('success',"Créneau supprimé avec succès !");
            return $this->redirectToRoute('therapist_availabilities');
        }
    }

    /**
     * @Route(path="/history", name="therapist_history")
     * @return Response
     */
    public function history(HistoryRepository $historyRepository, Request $request, PaginatorInterface $paginator)
    {
        $currentUser = $this->getCurrentTherapist();
        $histories = $historyRepository->findByTherapist($currentUser);

        $paginated = $paginator->paginate(
            $histories,
            $request->query->getInt('page', 1),
            10
        );
        return $this->render(
            'therapist/history.html.twig',
            [
                'history' => $paginated
            ]
        );
    }

    /**
     * @Route(path="/patients", name="therapist_patients")
     * @return Response
     */
    public function patients()
    {
        return $this->render(
            'therapist/patients.html.twig'
        );
    }

    /**
     * @Route(path="/settings", name="therapist_settings")
     * @return Response
     */
    public function settings(
        Request $request,
        EntityManagerInterface $manager,
        DepartmentRepository $departmentRepository,
        MailerFactory $mailerFactory
    )
    {
        $currentUser = $this->getCurrentTherapist();
        $prevEmail = $currentUser->getEmail();
        $settingsType = $this->createForm(
            TherapistSettingsType::class,
            $currentUser,
            ['data' => $currentUser]
        );
        $settingsType->handleRequest($request);
        if ($request->isMethod('POST') && $settingsType->isSubmitted() && $settingsType->isValid()) {
            /** @var Therapist $user */
            $user = $settingsType->getData();
            if ($user->getEmail() !== $prevEmail) {
                $user->setUniqueEmailToken();
                $mailerFactory->createAndSend(
                    "Changement de votre adresse email",
                    $user->getEmail(),
                    $this->renderView(
                        'email/user_change_email.html.twig',
                        ['email_token' => $user->getEmailToken(), 'project_url' => $_ENV['PROJECT_URL']]
                    ),
                    null,
                    EmailReport::TYPE_CHANGE_EMAIL_ADDR
                );
                $manager->flush();
                $this->addFlash('success', "Vous allez recevoir un mail pour confirmer votre nouvelle adresse email.");
                return $this->redirectToRoute('therapist_settings');
            }
            $manager->flush();
            $this->addFlash('success',"Informations mises à jour !");
            return $this->redirectToRoute('therapist_settings');
        }

        return $this->render(
            'therapist/settings.html.twig',
            [
                'settings_form' => $settingsType->createView()
            ]
        );
    }

    /**
     * @Route(path="/security", name="therapist_security")
     * @return Response
     */
    public function security(
        Request $request,
        UserPasswordEncoderInterface $encoder,
        AppointmentRepository $appointmentRepository,
        EntityManagerInterface $manager
    )
    {
        $user = $this->getCurrentTherapist();
        $changePasswordForm = $this->createForm(ChangePasswordType::class, $user);
        $changePasswordForm->handleRequest($request);
        if ($request->isMethod('POST') && $changePasswordForm->isSubmitted() && $changePasswordForm->isValid()) {
            $newPassword = $changePasswordForm->getData()->getPassword();
            $encoded = $encoder->encodePassword($user, $newPassword);
            $user->setPassword($encoded);
            $manager->flush();
            $this->addFlash('success',"Votre mot de passe a été mis à jour !");
            return $this->redirectToRoute('therapist_security');
        }
        return $this->render(
            'therapist/security.html.twig',
            [
                'change_password_form' => $changePasswordForm->createView(),
                'therapistId' => $user->getId(),
                'appointments' => $appointmentRepository->findBy(['therapist' => $user, 'status' => Appointment::STATUS_BOOKED])
            ]
        );
    }

    /**
     * @Route(path="/account/delete/{id}", name="therapist_account_delete")
     */
    public function deleteAccount(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $manager,
        MailerFactory $mailerFactory
    )
    {
        $user = $userRepository->find($request->attributes->get('id'));
        if ($user instanceof User) {
            $mailerFactory->createAndSend(
                "Suppression de votre compte",
                $user->getEmail(),
                $this->renderView('email/user_delete_account.html.twig'),
                null,
                EmailReport::TYPE_ACCOUNT_DELETION
            );
            // delete user
            $manager->remove($user);
            $manager->flush();
            // redirect
            $session = new Session();
            $session->invalidate();
            $this->addFlash('success', "Votre compte a été correctement supprimé.");
            return $this->redirectToRoute('app_logout');
        }
        $this->addFlash('error', "La suppression de votre compte a échoué.");
        return $this->redirectToRoute('therapist_security');
    }

    /**
     * @Route(path="/activate/manager/{id}", name="therapist_activate_role_manager")
     * @ParamConverter(name="id", class="App\Entity\Therapist")
     */
    public function activateManagerRole(Therapist $therapist, EntityManagerInterface $manager)
    {
        if (!$therapist instanceof Therapist) {
            $this->addFlash('error', "Vous n'avez pas le bon role.");
            return $this->redirectToRoute('therapist_security');
        }
        $therapist->upgradeToManager();
        $manager->flush();
        $this->addFlash('success', "Vous avez maintenant le role manager.");
        return $this->redirectToRoute('therapist_security');
    }

    private function getCurrentTherapist(): Therapist
    {
        return $this->therapistRepository->findOneBy(['email' => $this->getUser()->getUsername()]);
    }
}