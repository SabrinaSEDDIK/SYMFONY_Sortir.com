<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class GestionProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('pseudo',TextType::class, [
                'label'=>'Pseudo',
                'attr'=>['placeholder'=>'Pseudo']
                ])
            ->add('nom',TextType::class, ['label'=>'Nom'])
            ->add('prenom',TextType::class, ['label'=>'Prenom'])
            ->add('telephone',TextType::class, ['label'=>'Téléphone'])
            ->add('email',TextType::class, ['label'=>'Email'])
            ->add('password',RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe entrés ne sont pas identiques.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => [
                    'label' => 'Nouveau mot de passe',
                    'mapped'=>'false'],
                'second_options' => [
                    'label' => 'Confirmation de mot de passe',
                    'mapped'=>'false'],

            ])
            ->add('campus', EntityType::class, [
                'label'=>'Campus : ',
                'class' => Campus::class,
                'choice_label'=> 'nom',
                'placeholder' => 'Sélectionner un campus'])
            // On ajoute le champ image dans le formulaire
            // Il n'est pas lié à la base de données (mapped à false)
            ->add('image', FileType::class, [
                'label'=> 'Ma photo : ',
                'label_attr' => ['class' => 'no-required'],
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'value' => 'Bonjour'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '2048k',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg',
                            'image/gif',
                            'image/webp'
                        ],
                        'maxSizeMessage' => 'Le fichier est trop lourd. La taille maximale autorisée est 2048 ko.',
                        'mimeTypesMessage' => 'Formats autorisés : png, jpg, jpeg, gif, webp',
                    ])
                ]
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
