<?php

namespace App\Form;

use App\Entity\File;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\File as ConstraintsFile;
use Symfony\Component\Validator\Constraints\NotBlank;

class FileUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('path',FileType::class, [
                'label' => false,
                'mapped' => false,
                // unmapped fields can't define their validation using annotations
                'constraints' => [
                    new NotBlank([
                        "message"=>"Merci de saisir un fichier à charger"
                    ]),
                    new ConstraintsFile([
                        'maxSize' => '3000k',
                        'mimeTypes' => [
                            'text/plain',
                            'application/pdf',
                        ],
                        'mimeTypesMessage' => 'Merci de charger un fichier de type txt ou pdf',
                    ])
                ],
            ])
            ->add('alias', null, [
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
