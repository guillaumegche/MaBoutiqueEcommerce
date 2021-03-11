<?php

namespace App\Form;

use App\Classe\Search;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        /* une entrée pour la recherche textuelle */
            ->add('string', TextType::class, [ 
                'label' => 'Rechercher',
                'required' => false, /* on autorise la validation même si le champ est vide */
                'attr' => [
                    'placeholder' => 'Votre recherche ...',
                    'class' => 'form-control-sm'
                ]
            ])
            /* une entrée pour la recherche via les checkbox */
            ->add('categories', EntityType::class, [ /* EntityType qui va lier notre entrée à une Entity*/
                'label' => false,
                'required' => false, 
                'class' => Category::class, /* on relie le filtre, l'entrée à notre entité Category */
                'multiple' => true, /* choix multiples autorisés */
                'expanded' => true /* checkbox */
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Filtrer',
                'attr' => [
                    'class' => 'btn btn-block btn-info'
                ]
            ]);
    }

    /* Fonction qui va lier notre formulaire à notre objet Search */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Search::class, /* on lie notre objet Search */
            'method' => 'GET', /* méthode GET pour avoir une belle URL */
            'crsf_protection' => false /* on désactive la sécurité crsf car pas important ici */
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}