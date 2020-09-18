<?php


namespace App\Controller;


use App\Entity\Appointment;
use App\Entity\Department;
use App\Entity\EmailReport;
use App\Entity\History;
use App\Entity\Patient;
use App\Repository\AppointmentRepository;
use App\Repository\DepartmentRepository;
use App\Repository\PatientRepository;
use App\Repository\TherapistRepository;
use App\Repository\TownRepository;
use App\Repository\UserRepository;
use App\Services\CustomSerializer;
use App\History\HistoryHelper;
use App\Services\MailerFactory;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Class ApiController
 * @package App\Controller
 * @Route(path="/api")
 */
class ApiController extends AbstractController
{
    /**
     * @Route(path="/appointments", name="api_appointments", methods={"GET"})
     * @return JsonResponse
     */
    public function getAvailableAppointments(
        AppointmentRepository $appointmentRepository,
        SerializerInterface $serializer
    ): JsonResponse
    {
        $appoints = $appointmentRepository->findAvailableAppointments();
        $data = $serializer->serialize($appoints, 'json', ['groups' => ['get_bookings']]);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @Route(path="/bookings-filtered", name="api_bookings_filtered", methods={"POST","GET"})
     * @return JsonResponse
     */
    public function bookingSearchByFilters(
        Request $request,
        SerializerInterface $serializer,
        AppointmentRepository $appointmentRepository
    )
    {
        $country = json_decode($request->getContent(), true);
        $appointmentsByCountry = $appointmentRepository->findAvailableBookingsByFilters($country);
        $data = $serializer->serialize($appointmentsByCountry, 'json', ['groups' => ['get_bookings']]);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @Route(path="/current/patient", name="api_current_patient", methods={"GET"})
     * @return JsonResponse
     */
    public function getCurrentUserInfo(
        PatientRepository $patientRepository,
        SerializerInterface $serializer,
        Request $request
    )
    {
        $id = $request->query->get('id');
        $patient = $patientRepository->find($id);
        $data = $serializer->serialize($patient, 'json', ['groups' => ['user_search']]);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @Route(path="/therapists-by-department", name="api_therapists_by_department", methods={"GET"})
     * @param Request $request
     * @param DepartmentRepository $departmentRepository
     */
    public function getTherapistsByDepartment(
        Request $request,
        DepartmentRepository $departmentRepository,
        TherapistRepository $therapistRepository,
        SerializerInterface $serializer
    )
    {
        $departmentId = $request->query->get('department');
        $department = $departmentRepository->find($departmentId);
        if ($department instanceof Department) {
            $therapists = $therapistRepository->findBy(['department' => $department]);
            $data = $serializer->serialize($therapists, 'json', ['groups' => ['patient_research']]);
            return new JsonResponse($data, Response::HTTP_OK, [], true);
        } else {
            return new JsonResponse(['message' => "Département non trouvé"], Response::HTTP_NOT_FOUND, [], true);
        }
    }

    /**
     * @Route(path="/create/booking", name="api_create_booking", methods={"GET"})
     * @return JsonResponse
     */
    public function createBooking(
        Request $request,
        AppointmentRepository $appointmentRepository,
        PatientRepository $patientRepository,
        EntityManagerInterface $entityManager,
        HistoryHelper $historyHelper,
        MailerFactory $mailer,
        SerializerInterface $serializer
    ): JsonResponse
    {
        $appointId = $request->query->get('appoint');
        $userId = $request->query->get('user');
        $patient = $patientRepository->find($userId);
        $appointment = $appointmentRepository->find($appointId);
        if (!$appointment instanceof Appointment || !$patient instanceof Patient) {
            return new JsonResponse(['message' => "Requête incorrecte"], Response::HTTP_NOT_FOUND, [], true);
        }
        $appointment->setBooked(true);
        $appointment->setStatus(Appointment::STATUS_BOOKED);
        $patient->addAppointment($appointment);
        // add booking history
        $historyHelper->addHistoryItem(History::ACTION_BOOKED, $appointment);
        $entityManager->flush();

        $mailer->createAndSend(
            "Confirmation de rendez-vous",
            $appointment->getPatient()->getEmail(),
            $this->renderView('email/appointment_booked_patient.html.twig', ['appointment' => $appointment]),
            null,
            EmailReport::TYPE_BOOKING_CONFIRMATION
        );

        $mailer->createAndSend(
            "Confirmation de rendez-vous",
            $appointment->getTherapist()->getEmail(),
            $this->renderView('email/appointment_booked_therapist.html.twig', ['appointment' => $appointment]),
            null,
            EmailReport::TYPE_BOOKING_CONFIRMATION
        );

        $data = $serializer->serialize(
            "Rendez-vous confirmé !",
            'json'
        );
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @Route(path="/confirm/booking/{id}", name="api_confirm_booking", methods={"GET"})
     * @ParamConverter(name="id", class="App\Entity\Appointment")
     * @return JsonResponse
     */
    public function confirmBooking(
        Appointment $appointment,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        MailerFactory $mailer,
        HistoryHelper $historyHelper
    ): JsonResponse
    {
        if (!$appointment || !$appointment instanceof Appointment) {
            return new JsonResponse("Pas de rendez-vous enregistré.", 500, [], true);
        }
        $appointment->setBooked(true);
        $appointment->setStatus(Appointment::STATUS_BOOKED);
        // add booking history
        $historyHelper->addHistoryItem(History::ACTION_BOOKED, $appointment);
        $entityManager->flush();

        $mailer->createAndSend(
            "Confirmation de rendez-vous",
            $appointment->getPatient()->getEmail(),
            $this->renderView('email/appointment_booked_patient.html.twig', ['appointment' => $appointment]),
            null,
            EmailReport::TYPE_BOOKING_CONFIRMATION
        );

        $mailer->createAndSend(
            "Confirmation de rendez-vous",
            $appointment->getTherapist()->getEmail(),
            $this->renderView('email/appointment_booked_therapist.html.twig', ['appointment' => $appointment]),
            null,
            EmailReport::TYPE_BOOKING_CONFIRMATION
        );

        $data = $serializer->serialize(
            "Rendez-vous confirmé !",
            'json'
        );
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @Route(path="/departments-by-country", name="api_get_departments_by_country", methods={"GET"})
     * @return JsonResponse
     */
    public function getDepartmentsByCountry(
        DepartmentRepository $departmentRepository,
        Request $request,
        CustomSerializer $serializer
    )
    {
        $departments = $departmentRepository->findBy(
            ['country' => $request->query->get('country')],
            ['code' => 'ASC']
        );

        $data = $serializer->serialize($departments, ['towns','users']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @Route(path="/towns-by-department", name="api_get_towns_by_department", methods={"GET"})
     * @return JsonResponse
     */
    public function getTownsByDepartments(
        TownRepository $townRepository,
        DepartmentRepository $departmentRepository,
        Request $request,
        CustomSerializer $serializer
    )
    {
        $department = $departmentRepository->find($request->query->get('department'));
        $towns = $townRepository->findBy(
            ['department' => $department],
            ['code' => 'ASC']
        );
        $data = $serializer->serialize($towns, ['users','department']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
}
