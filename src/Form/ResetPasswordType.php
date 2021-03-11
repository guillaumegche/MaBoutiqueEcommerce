<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('new_password', RepeatedType::class, [
            'type' => PasswordType::class,
            'invalid_message' => 'Le mot de passe et la confirmation doivent être identique',
            'label' => 'Mon nouveau mot de passe',
            'constraints' => new Length([
                'min' => 8,
                'max' => 30
            ]),                
            'required' => true,
            'first_options' => [
                'label' => 'Mon nouveau mot de passe',
                'attr' => [
                    'placeholder' => 'Tapez votre mot de passe'
                ]
            ],
            'second_options' => [
                'label' => 'Confirmation mon nouveau mot de passe',
                'attr' => [
                    'placeholder' => 'Confirmez votre mot de passe'
                ]
            ],
        ])
        ->add('submit', SubmitType::class, [
            'label' => 'Mettre à jour mon mot de passe',
            'attr' => [
                'class' => 'btn btn-block btn-info'
            ]
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
