<?php

namespace SageBundle\Form\People;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;

class AddPeopleForInterimType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          /*  ->add('civility', ChoiceType::class, array(
                'label' => 'form.label.civilstatus.civility',
                'choices' => array(
                    'M' => 0,
                    'Mme' => 1,
                    'Mlle' => 2
                ),
                'property_path' => 'civility'
            ))
            ->add('personalPhone', null, array(
                'label' => 'form.label.civilstatus.personal_phone',
                'attr'=> array(
                    'data-rule-digits' => true
                )
            ))
           */
            ->add('lastName', null, array(
                'label' => 'form.label.civilstatus.last_name',
            ))
            ->add('firstName', null, array(
                'label' => 'form.label.civilstatus.first_name',
            ))
            ->add('email', EmailType::class, array(
                'label' => 'form.label.civilstatus.email',
            ))

            ->add('birthdate', BirthdayType::class, array(
                'label' => 'form.label.registration.birthdate',
                'attr' => array('class'=>'select2-nosearch form-inline-birthdate'),
                'years'=> range(date('Y') - 100, date('Y'))
            ))
            ->add('birthCity', TextType::class, array(
                'label' => 'form.label.registration.birth_city',
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SageBundle\Entity\People'
        ));
    }
}
