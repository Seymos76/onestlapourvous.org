<?php

namespace App\Form;

use App\Entity\Department;
use App\Entity\Therapist;
use App\Repository\DepartmentRepository;
use App\Repository\TownRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TherapistRegisterType extends AbstractType
{
    protected $departmentRepository;
    protected $townRepository;

    public function __construct(DepartmentRepository $departmentRepository, TownRepository $townRepository)
    {
        $this->departmentRepository = $departmentRepository;
        $this->townRepository = $townRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'email',
                EmailType::class
            )
            ->add(
                'password',
                RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'invalid_message' => 'Les mots de passe ne correspondent pas.',
                    'options' => ['attr' => ['class' => 'password-field']],
                    'required' => true,
                    'first_options'  => ['label' => 'Mot de passe'],
                    'second_options' => ['label' => 'Confirmez votre mot de passe'],
                ]
            )
            ->add(
                'firstName',
                TextType::class
            )
            ->add(
                'lastName',
                TextType::class
            )
            ->add(
                'phoneNumber',
                TelType::class
            )
            ->add(
                'ethicEntityCodeLabel',
                TextType::class
            )
            ->add(
                'schoolEntityLabel',
                TextType::class
            )
            ->add(
                'hasAcceptedTermsAndPolicies',
                CheckboxType::class
            )
            ->add(
                'hasCertification',
                CheckboxType::class
            )
            ->add(
                'isSupervised',
                CheckboxType::class
            )
            ->add(
                'isRespectingEthicalFrameWork',
                CheckboxType::class
            )
        ;
    }

    protected function getDepartmentByCountry(?string $country = null): array
    {
        return $this->departmentRepository->findBy(
            ['country' => $country ?? 'fr'],
            ['code' => 'ASC']
        );
    }

    protected function getTownsByDepartment(?Department $department = null): array
    {
        if ($department) {
            return $this->townRepository->findBy(['department' => $department]);
        } else {
            $department = $this->getDepartmentByCountry("fr");
            return $this->townRepository->findBy(['department' => $department[0]]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Therapist::class,
        ]);
    }
}
