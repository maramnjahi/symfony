<?php

namespace App\Form;

use App\Entity\Commande;
use App\Entity\Detailscommande;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DetailscommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numArticle')
            ->add('nomArticle')
            ->add('quantite')
            ->add('prixUnitaire')
            ->add('sousTotal')
            ->add('commande', EntityType::class,[
                'class'=>Commande::class,
                'choice_label'=>'id',
                'multiple'=>false,
                'expanded'=>false,
                'placeholder'=>'choisir un commande'
            ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Detailscommande::class,
        ]);
    }
}
