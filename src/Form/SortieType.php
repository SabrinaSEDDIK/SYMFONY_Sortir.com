<?php

namespace App\Form;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Ville;
use App\Entity\Campus;
use App\Entity\Sortie;
use App\Form\LieuType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'label'=>'Nom de la sortie : '
            ])

            ->add('dateHeureDebut', DateTimeType::class, [
                'label'=>'Date et heure de la sortie : ',
                'html5' => true,
                'widget' => 'single_text'
            ])

            ->add('dateLimiteInscription', DateType::class, [
                'label'=>'Date limite d\'inscription : ',
                'html5' => true,
                'widget' => 'single_text'
            ])

            ->add('nbInscriptionsMax', IntegerType::class, [
                'label'=>'Nombre de places : '
            ])

            ->add('duree', IntegerType::class, [
                'label'=>'Durée (minutes) : '
            ])

            ->add('infosSortie', TextareaType::class, [
                'label'=>'Description et infos : ',
                'attr' => array(
                    'style'=>'height: 180px',
                    'placeholder' => 'ex : À l\'occasion du printemps, ...')
            ])

            ->add('siteOrganisateur', EntityType::class, [
                'label'=>'Campus : ',
                'class' => Campus::class,
                'choice_label'=> 'nom',
                'placeholder' => 'Sélectionner un campus'
            ])

            ->add('ville', EntityType::class, [
                'class' => Ville::class,
                'choice_label'=> 'nom',
                'mapped'=>false,
                'label'=>'Ville de la sortie : ',
                'placeholder' => 'Sélectionner une ville'
            ])

            ->add("lieu", EntityType::class, [
                "class"=> Lieu::class,
                'label'=>'Lieu : ',
                'attr' => array(
                    'required'=>'required'
                )
            ])

            ->add("rue", TextType::class, [
                'mapped'=>false,
                'label'=>'Rue : ',
                'label_attr'=>['class'=> 'no-required'],
                'disabled'=> 'disabled'
            ])

            ->add("codePostal", TextType::class, [
                'mapped'=>false,
                'label'=>'Code Postal : ',
                'label_attr'=>['class'=> 'no-required'],
                'disabled'=> 'disabled',
                'constraints' => [
                    new Regex(['pattern' => '/\d{2}[ ]?\d{3}/', 'message'=>'Format incorrect'])]
            ])

            ->add('latitude', NumberType::class,  [
                'label'=>'Latitude : ',
                'label_attr'=>['class'=> 'no-required'],
                'mapped'=>false,
                'disabled'=> 'disabled',
                'constraints' => [
                    new Range(['min' => -90, 'max'=>90, 'notInRangeMessage'=>'Valeur comprise entre -90 et 90 '])]
            ])

            ->add('longitude', NumberType::class, [
                'label'=>'Longitude : ',
                'label_attr'=>['class'=> 'no-required'],
                'mapped'=>false,
                'disabled'=> 'disabled',
                'constraints' => [
                    new Range(['min' => -180, 'max'=>180, 'notInRangeMessage'=>'Valeur comprise entre -180 et 180'])]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
