<?php


namespace App\Form;


use App\Entity\Appointment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppointmentSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'start',
                DateType::class,
                [
                    'widget' => 'single_text',
                    'label' => "Date du"
                ]
            )
            ->add(
                'end',
                DateType::class,
                [
                    'widget' => 'single_text',
                    'label' => "Date au",
                    'required' => false
                ]
            )
            ->add(
                'zipCode',
                TextType::class,
                [
                    'label' => "CP / Commune",
                    'required' => false
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null
        ]);
    }
}