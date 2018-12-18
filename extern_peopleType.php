<?php

namespace SageBundle\Form\Workflow;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormInterface;

class AddPeopleForExternType extends AbstractType
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
            ->add('email', EmailType::class, array(
                'label' => 'form.label.civilstatus.email',
            ))
             */
            ->add('lastName', null, array(
                'label' => 'form.label.civilstatus.last_name',
            ))
            ->add('firstName', null, array(
                'label' => 'form.label.civilstatus.first_name',
            ))
            ->add('socialSecurity', null, array(
                'label' => 'form.label.registration.social_security',
                'required' => false,
                'attr'=> array(
                    'data-rule-secu' => true
                )))
            ->add('bankIban', null, array(
                'label' => 'form.label.bank.iban',
                //'required' => $options['bank_is_required'],
                'required' => false,
                'attr'=> array(
                    'data-rule-iban' => true
                )))
            ->add('birthdate', BirthdayType::class, array(
                'label' => 'form.label.registration.birthdate',
                'attr' => array('class'=>'select2-nosearch form-inline-birthdate'),
                'years'=> range(date('Y') - 100, date('Y'))
            ))
            ->add('birthCity', TextType::class, array(
                'label' => 'form.label.registration.birth_city',
            ))
            ->add('addressLine1', null, array(
                'label' => 'form.label.address.line1',
                'required' => true,
                'attr' => array(
                    'placeholder' => "",
                )
            ))
            ->add('addressLine2', null, array(
                'label' => 'form.label.address.line2'
            ))
            ->add('addressCity', null, array(
                'label' => 'form.label.address.city',
                'required' => true
            ))
            ->add('addressZipcode', null, array(
                'label' => 'form.label.address.zipcode',
                'required' => true
            ))
            ->add('addressCountry', EntityType::class, array(
                'label' => 'form.label.address.country',
                'class' => 'SageBundle:Reference\Country',
                'choice_label' => 'title',
                'preferred_choices' => function($val, $key) {
                    //TODO: Country of Enterprise ? of Linked Establishment ?
                    return $val->getCode() == "FRA";
                }
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SageBundle\Entity\People',
            'bank_is_required' => true,
            'validation_groups' => function (FormInterface $form) {
                $groups = array('Default');
                if ($form->getConfig()->getOption('bank_is_required', true))
                    $groups[] = 'BankRequired';

                return $groups;
            }
        ));
    }
}
