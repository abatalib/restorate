<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Restaurant;
use App\Repository\CityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RestaurantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class,[
                'label'=>'Nom'
            ])
            ->add('city', EntityType::class, [
                'class'=>City::class,
                'query_builder' => function (CityRepository $c) {
                    return $c->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                },
                'choice_label'=>'name',
                'placeholder' => '',
                'label'=>"Ville"
            ])
            ->add('images', FileType::class, [
                'label'=>false,
                'multiple'=>true,
                'mapped'=>false,
                'required'=>false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Restaurant::class,
        ]);
    }
}
