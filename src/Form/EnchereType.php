<?php

namespace App\Form;

use App\Entity\Enchere;

use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class EnchereType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateDebut', TextType::class, [
                'label' => 'Date de début',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir une date de début.']),
                    new Callback([$this, 'validateDateDebut']),
                ],
                'attr' => [
                    'type' => 'date',
                ],
            ])
            ->add('heured', TextType::class, [
                'label' => 'Heure de début',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir une Heure de début.']),
                    new Callback([$this, 'validateTime']),
                ],
            ])
            ->add('dateFin', TextType::class, [
                'label' => 'Date de fin',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir une date de fin.']),
                    new Callback([$this, 'validateDateFin']),
                ],
            ])
            ->add('heuref', TextType::class, [
                'label' => 'Heure de fin',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir une Heure de fin.']),
                    new Callback([$this, 'validateTime']),
                ],
            ]) 
            ->add('montantInitial', null, [
                'label' => 'Montant initial',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir un Montant initial.']),
                    new Assert\GreaterThan(['value' => 0, 'message' => 'Le montant initial doit être supérieur à zéro.']),
                ],
            ])
            ->add('nomEnchere', null, [
                'label' => 'Nom de l\'enchère',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir le nom de l\'enchère.']),
                ],
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image',
                'required' => false,
                'mapped' => false, // Tell Symfony not to bind this to the entity
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => ['image/*'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (png, jpeg, jpg).',
                    ]),
                ],
            ]);
    }

    public function validateDateDebut($value, ExecutionContextInterface $context)
    {
        if ($value !== null) {
            $dateFormat = 'Y-m-d';
            $date = \DateTime::createFromFormat($dateFormat, $value);

            if (!$date || $date->format($dateFormat) !== $value) {
                $context->buildViolation('La date doit être au format "Y-m-d".')
                    ->addViolation();
            }
        }
    }

    public function validateTime($value, ExecutionContextInterface $context)
    {
        if ($value !== null) {
            $timeFormat = 'H:i';
            $time = \DateTime::createFromFormat($timeFormat, $value);

            if (!$time || $time->format($timeFormat) !== $value) {
                $context->buildViolation('L\'heure doit être au format "H:i".')
                    ->addViolation();
            }
        }
    }

    public function validateDateFin($value, ExecutionContextInterface $context)
    {
        $form = $context->getRoot();
        $dateDebut = $form['dateDebut']->getData();

        if ($dateDebut && $value) {
            $dateDebutDateTime = \DateTime::createFromFormat('Y-m-d', $dateDebut);
            $dateFinDateTime = \DateTime::createFromFormat('Y-m-d', $value);

            if ($dateDebutDateTime > $dateFinDateTime) {
                $context->buildViolation('La date de fin doit être postérieure à la date de début.')
                    ->addViolation();
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Enchere::class,
        ]);
    }
}
