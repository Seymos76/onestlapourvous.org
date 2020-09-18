<?php

namespace App\Form;

use App\Entity\Appointment;
use App\Form\DataTransformer\AppointmentDateTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppointmentType extends AbstractType
{
    private $dateTransformer;

    public function __construct(AppointmentDateTransformer $dateTransformer)
    {
        $this->dateTransformer = $dateTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'bookingDate',
                TextType::class,
                [
                    'label' => "Sélectionnez une date",
                ]
            )
            ->add(
                'bookingStart',
                TimeType::class,
                [
                    'input'  => 'datetime',
                    'widget' => 'choice',
                    'label' => "Heure de début"
                ]
            )
            ->add(
                'bookingEnd',
                TimeType::class,
                [
                    'input'  => 'datetime',
                    'widget' => 'choice',
                    'label' => "Heure de fin"
                ]
            )
        ;
        $builder->get('bookingDate')
            ->addModelTransformer($this->dateTransformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Appointment::class,
        ]);
    }
}
