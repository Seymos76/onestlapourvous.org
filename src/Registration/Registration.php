<?php


namespace App\Registration;


use App\Entity\Department;
use App\Entity\EmailReport;
use App\Entity\Patient;
use App\Entity\User;
use App\Repository\DepartmentRepository;
use App\Services\MailerFactory;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class Registration extends AbstractController
{
    private $departmentRepository;
    private $encoder;
    private $mailerFactory;
    private $entityManager;

    public function __construct(
        DepartmentRepository $departmentRepository,
        UserPasswordEncoderInterface $encoder,
        MailerFactory $mailerFactory,
        EntityManagerInterface $entityManager
    )
    {
        $this->departmentRepository = $departmentRepository;
        $this->encoder = $encoder;
        $this->mailerFactory = $mailerFactory;
        $this->entityManager = $entityManager;
    }

    public function getDepartment(Request $request): array
    {
        $selectedCountry = $request->request->get('country');
        $selectedDepartment = $request->request->get('department');

        $slugger = new Slugify();

        $localisation['departSlug'] = $slugger->slugify($selectedDepartment);
        $localisation['department'] = $selectedCountry === 'fr' ?
            $this->entityManager->getRepository(Department::class)->findOneBy(['country' => $selectedCountry, 'code' => $selectedDepartment]) :
            $this->entityManager->getRepository(Department::class)->findOneBy(['country' => $selectedCountry, 'slug' => $localisation['departSlug']])
        ;
        $localisation['selectedCountry'] = $selectedCountry;
        return $localisation;
    }

    public function registerUser(UserInterface $user, array $data): UserInterface
    {
        /** @var Patient $user */
        $user->setCountry($data['selectedCountry'] ? $data['selectedCountry'] : 'fr');
        if (!$data['department'] instanceof Department) {
            $user->setScalarDepartment($data['departSlug']);
        }

        $user->setDepartment($data['department'])
            ->setUniqueEmailToken()
            ->setPassword(
                $this->encoder->encodePassword($user, $user->getPassword())
            );
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $user;
    }

    public static function getToken(RequestContext $requestContext)
    {
        return substr($requestContext->getPathInfo(), 20, strlen($requestContext->getPathInfo()));
    }

    public function activateUserOrNot(string $emailToken): array
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['emailToken' => $emailToken]);
        $arrResponse = [
            'message' => [
                'type' => '',
                'message' => ''
            ],
            'route'
        ];
        if (!$user instanceof UserInterface) {
            throw new \Exception("Pas d'utilisateur pour ce token");
        }
        if (true === $user->isActive()) {
            $arrResponse['message']['type'] = 'info';
            $arrResponse['message']['message'] = "Votre adresse email est déjà confirmée.";
            $arrResponse['route'] = 'app_login';
        }
        if (false === $user->isActive()) {
            $user->setEmailToken('')->activate();
            $this->entityManager->flush();
            $arrResponse['message']['type'] = 'success';
            $arrResponse['message']['message'] = "Votre adresse email vient d'être confirmée.";
            $arrResponse['route'] = 'app_login';
        }
        return $arrResponse;
    }

    public function sendRegistrationEmail(UserInterface $user, string $userClass)
    {
        return $this->mailerFactory->createAndSend([
            'subject' => "Validation de votre inscription",
            'to' => $user->getEmail(),
            'from' => null,
            'body' => $this->renderView(
                'email/patient_registration.html.twig',
                ['email_token' => $user->getEmailToken(), 'project_url' => $_ENV['PROJECT_URL']]
            )
        ], EmailReport::TYPE_REGISTRATION);
    }
}