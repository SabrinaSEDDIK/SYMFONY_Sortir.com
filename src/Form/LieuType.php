<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;

class LieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'label'=>'Lieu : ',
                'attr' => array(
                    'id'=>'modal-lieu'
                )
            ])

            ->add("codePostal", TextType::class, [
                'mapped'=>false,
                'label'=>'Code Postal : ',
                'attr' => array(
                    'id'=>'modal-cp'
                ),
                'constraints' => [
                    new NotBlank(['message' => 'Ce champ est requis']),
                    new Regex(['pattern' => '/\d{2}[ ]?\d{3}/', 'message'=>'Format incorrect'])]
            ])

            ->add('ville', TextType::class, [
                'label'=>'Ville : ',
                'mapped'=>false,
                'attr' => array(
                    'id'=>'modal-ville'
                ),
                'constraints' => [
                    new NotBlank(['message' => 'Ce champ est requis'])]
            ])

            ->add('latitude', NumberType::class,  [
                'label'=>'Latitude : ',
                'attr' => array(
                    'id'=>'modal-latitude'
                )
            ])

            ->add('longitude', NumberType::class, [
                'label'=>'Longitude : ',
                'attr' => array(
                    'id'=>'modal-longitude'
                )
            ])
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
        ]);
    }
}
