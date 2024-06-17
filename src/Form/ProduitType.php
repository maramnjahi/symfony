<?php

namespace App\Form;

use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\File as FileConstraint;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\HttpFoundation\File\File;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomP', null, [
                'label' => 'Product Name',
            ])
            ->add('descriptionP', null, [
                'label' => 'Product Description',
            ])
            ->add('prixP', null, [
                'label' => 'Product Price',
            ])
            ->add('imageP', FileType::class, [
                'label' => 'Picture',
                'required' => true, 
                'constraints' => [
                    new NotBlank(['message' => 'Please upload a picture']),
                    new FileConstraint([
                        'maxSize' => '1024k', // Limit the file size if needed
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image file (JPEG or PNG).',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}