<?php

namespace AppBundle\Form;

use AppBundle\Security\UserAccessVoter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class CompanyUserAccessType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
     Nom, Prénom, Type de contrat, Date de début, Date de fin, Planning,
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, array(
                'required' => true,
                'mapped' => false,
                'label' => 'change.email'
            ))
            ->add('lastName', TextType::class, array(
                'required' => true,
                'mapped' => false,
                'label' => 'change.lastName'
            ))
            ->add('firstName', TextType::class, array(
                'required' => true,
                'mapped' => false,
                'label' => 'change.firstName'
            ))
            ->add('lastdate', DateTimeType::class, array(
                'required' => true,
                'mapped' => false,
                'label' => 'change.firstName'
            ))
            ->add('firstdate', DateTimeType::class, array(
                'required' => true,
                'mapped' => false,
                'label' => 'change.firstName'
            ))
            ->add('accessType', ChoiceType::class, array(
                'choices' => array(
                    'DRH' => UserAccessVoter::ACCESS_DRH,
                    'MANAGER' => UserAccessVoter::ACCESS_MANAGER
                ),
                'label' => 'access.type'
            ))
            ->add('enabled')
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\UserAccess',
        ));
    }
}
