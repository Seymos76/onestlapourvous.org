<?php


namespace App\Form;


use App\Entity\Department;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TherapistSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'email',
                EmailType::class
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
                'displayName',
                TextType::class,
                [
                    'required' => false
                ]
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
                'phoneNumber',
                TelType::class
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}