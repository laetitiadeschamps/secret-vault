<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
           
            ->add('email', null, [
                'label'=>false,
                'attr'=> [
                    'placeholder'=>"Entrez votre mail"
                ]
            ])
            ->add('username', TextType::class, [
                'label'=>false,
                'attr'=> [
                    'placeholder'=>"Entrez votre nom d'utilisateur"
                ],
                'empty_data' => ''
            ])
            ->add('plainPassword', RepeatedType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent être identiques',
                'required' => true,
                'first_options'  => ['label' => false, 'attr'=> ['placeholder'=>'Mot de passe']],
                'second_options' => ['label' => false, 'attr'=>['placeholder'=>'Répétez votre mot de passe']],
                'attr' => ['autocomplete' => 'new-password'], 
                'constraints'=> [
                   new Length(['min'=>6, 'max'=>50, 'minMessage'=>"Mot de passe trop court, minimum {{ limit }} caractères" ]),
                   new NotBlank(['message'=>"Le mot de passe doit être renseigné"]),
                   new Regex(['value'=>'/[a-z]+/','message'=>  'Le mot de passe doit contenir une lettre minuscule']),
                   new Regex('/[A-Z]+/',  'Le mot de passe doit contenir une lettre majuscule'),
                   new Regex('/\d+/',  'Le mot de passe doit contenir un chiffre'),
                   new Regex('/[@$%_*|=-]+/',  'Le mot de passe doit contenir un caractère spécial parmi @$%_*|=-'),
                ]
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                "label"=>"Accepter les conditions d'utilisation",
                'constraints' => [
                    new IsTrue([
                        'message' => "Vous devez accepter les conditions d'utilisation.",
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label'=>'Valider',
                'attr'=> [
                    'class'=>'btn-success w-50 mx-auto'
                ]
            ])
           
           
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
