<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use VictorPrdh\RecaptchaBundle\Form\ReCaptchaType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('email', null, [
            'constraints' => [
                new NotBlank([
                    'message' => 'Please enter an email',
                ]),
                new Assert\Email(),
            ],
        ])
        ->add('plainPassword', PasswordType::class, [
            // instead of being set onto the object directly,
            // this is read and encoded in the controller
            'mapped' => false,
            'attr' => ['autocomplete' => 'new-password'],
            'constraints' => [
                new NotBlank([
                    'message' => 'Please enter a password',
                ]),
                new Length([
                    'min' => 6,
                    'minMessage' => 'Your password should be at least {{ limit }} characters',
                    // max length allowed by Symfony for security reasons
                    'max' => 4096,
                ]),
            ],
        ])
        ->add('nom' , null, [
            'constraints' => [
                new NotBlank([
                    'message' => 'Please enter last name',
                ]),
            ],
        ])
        ->add('prenom' , null, [
            'constraints' => [
                new NotBlank([
                    'message' => 'Please enter first name',
                ]),
            ],
        ])
        ->add('age' , null, [
            'constraints' => [
                new NotBlank([
                    'message' => 'Please enter your age',
                ]),
            ],
        ])
        ->add('sexe', ChoiceType::class, [
            'choices' => [
                'Homme' => 'Homme',
                'Femme' => 'Femme',
            ],
            'placeholder' => 'Select Type', // Optional placeholder text
            'attr' => [
                'class' => 'form-control' , // Add custom CSS class
            ],
            'constraints' => [
                new NotBlank([
                    'message' => 'Please select a Type',
                ]),
            ],
        ])
        ->add('phone' , null, [
            'constraints' => [
                new NotBlank([
                    'message' => 'Please enter your phone number',
                ]),
            ],
        ])
        ->add("recaptcha", ReCaptchaType::class);

    ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => [
                'novalidate' => 'novalidate', // comment me to reactivate the html5 validation!  🚥
            ],
            'data_class' => User::class,
            'constraints' => [
                new UniqueEntity([
                    'fields' => 'email',
                    'message' => 'This email is already registered.',
                ]),
            ],

        ]);
    }
}
