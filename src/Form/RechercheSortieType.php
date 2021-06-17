<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\RechercheSortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RechercheSortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
                'label' => 'Campus : ',
                'label_attr' => ['class' => 'no-required']
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie : ',
                'label_attr' => ['class' => 'no-required'],
                'required'   => false
            ])
            ->add('dateDebutRecherche', DateType::class, [
                'label' => 'Entre',
                'label_attr' => ['class' => 'no-required'],
                'html5' => true,
                'widget' => 'single_text',
                'input_format' => 'Y-m-d H:i',
                'required'   => false
            ])
            ->add('dateFinRecherche', DateType::class, [
                'label' => 'Et ',
                'label_attr' => ['class' => 'no-required'],
                'html5' => true,
                'widget' => 'single_text',
                'required'   => false
            ])
            ->add('isOrganisateur', CheckboxType::class, [
                'label' => 'Sorties dont je suis l\'organisateur/trice' ,
                'label_attr' => ['class' => 'no-required'],
                'required'   => false
            ])
            ->add('isRegistered', CheckboxType::class, [
                'label' => 'Sorties auxquelles je suis inscrit/e' ,
                'label_attr' => ['class' => 'no-required'],
                'required'   => false
            ])
            ->add('isNotRegistered', CheckboxType::class, [
                'label' => 'Sorties auxquelles je ne suis pas inscrit/e' ,
                'label_attr' => ['class' => 'no-required'],
                'required'   => false
            ])
            ->add('isFinished', CheckboxType::class, [
                'label' => 'Sorties passées',
                'label_attr' => ['class' => 'no-required'],
                'required'   => false
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RechercheSortie::class,
        ]);
    }
}
