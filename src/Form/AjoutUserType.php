<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AjoutUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'label'=>'Nom : '
            ])
            ->add('prenom',TextType::class, [
                'label'=>'Prénom : '
            ])
            ->add('pseudo',TextType::class, [
                'label'=>'Pseudo : ',
            ])
            ->add('email', EmailType::class, [
                'label'=>'Email : '
            ])
            ->add('telephone', TextType::class, [
                'label'=>'Téléphone : '
            ])
            ->add('campus', EntityType::class, [
                'label'=>'Campus : ',
                'class' => Campus::class,
                'choice_label'=> 'nom',
                'placeholder' => 'Sélectionner un campus'
            ])
            ->add('administrateur', CheckboxType::class, [
                'label'=>'L\'utilisateur est administrateur ',
                'required'=>false,
                'label_attr'=>array('class'=> 'no-required')
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
