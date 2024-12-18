<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pseudo', SearchType::class, [
                'label' => 'Rechercher un utilisateur',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Chercher un utilisateur'
                ],
            ])
            ->add('movie', SearchType::class, [
                'label' => 'Rechercher un film',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Chercher un Film'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'method' => 'GET',
        ]);
    }
}