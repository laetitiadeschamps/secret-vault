<?php

namespace App\Form;

use App\Entity\File;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class FileUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file',FileType::class, [
                'label' => false,
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                'required' => false,
                // unmapped fields can't define their validation using annotations
                'constraints' => [
                    new File([
                        'maxSize' => '3000k',
                        'mimeTypes' => [
                            'text/plain',
                            'application/pdf',
                        ],
                        'mimeTypesMessage' => 'Merci de charger un fichier de type txt ou pdf',
                    ])
                ],
            ])
            ->add('name', null, [
                'label'=>false,
                'attr'=> [
                    'placeholder'=>'Donnez un nom à votre fichier'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label'=>'Lancer le chargement',
                'attr'=> [
                    'class'=>'btn-dark'
                ]
                
            ])
           
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => File::class,
        ]);
    }
}
