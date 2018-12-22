<?php

namespace SageBundle\Form\Employee;

use AppBundle\Form\Type\DatePickerType;
use SageBundle\Form\SageReferenceEntityType;
use SageBundle\Form\Employee\FileType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

class ContractType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $company = $options['company'];
        if ($options['start']) {
            $builder
                ->add('establishment', EntityType::class, array(
                    'class' => 'SageBundle:Company\Establishment',
                    'query_builder' => function (EntityRepository $er) use ($company) {
                        return $er->createQueryBuilder('e')
                            ->where('e.company = :company')
                            ->setParameter('company', $company)
                            ->orderBy('e.name');
                    },
                    'choice_label' => function ($val) {
                        return substr($val->getSiret(), -5)
                            . ' | ' . $val->getName()
                            . ', ' . $val->getAddressLine1() . ' ' . $val->getAddressLine2()
                            . ', ' . $val->getAddressZipcode() . ' - ' . $val->getAddressCity();
                    }
                ))
                ->add('reasonForEntry', SageReferenceEntityType::class, array(
                    'reference_type' => 'entry_type',
                    'preferred_choices' => function ($val, $key) {
                        return $val->getCode() == "EM";
                    }
                ))
                ->add('type', EntityType::class, array(
                    'placeholder'=>'',
                    'label'=>'Type de contrat',
                    'class' => 'SageBundle:Reference\ContractType',
                    'choice_label' => function ($val) {
                        return $val->getCode() . ' | ' . $val->getTitle();
                    }
                ))
                ->add('typeReason', SageReferenceEntityType::class, array(
                    'reference_type' => 'contract_type_reason',
                    'required' => false
                ))
                ->add('codeInsee', EntityType::class, array(
                    'placeholder'=>'',
                    'label' => 'Catégorie Sociopro',
                    'class' => 'SageBundle:Reference\JobType',
                    'choice_label' => function ($val) {
                        return $val->getInsee() . ' | ' . $val->getTitle();
                    }
                ))
                ->add('seniorityDate', DatePickerType::class, array(
                    'required' => false
                ))
                ->add('startDate', DatePickerType::class, array(
                    'required' => true
                ))
                ->add('trialPeriodEndDate', DatePickerType::class, array(
                    'required' => false
                ))
                ->add('endDate', DatePickerType::class, array(
                    'required' => $options['end'],
                    'label'=>'Fin de contrat'
                ))
                ->add('file', FileType::class, array(
                    'label' => 'Document',
                    'required' => false,
                ))
                ;

        }
        if ($options['end']) {
            $builder
                ->add('trialPeriodEndDate', DatePickerType::class, array(
                    'required' => false
                ))
                ->add('endDate', DatePickerType::class)
                ->add('reasonForRelease', SageReferenceEntityType::class, array(
                    'reference_type' => 'leaving_reason',
                ))
                ->add('lastWorkingPaidDate', DatePickerType::class)
                ->add('fireNotificationDate', DatePickerType::class, array(
                    'required' => false
                ))
                ->add('fireSignatureDate', DatePickerType::class, array(
                    'required' => false
                ))
                ->add('fireProcedureDate', DatePickerType::class, array(
                    'required' => false
                ))
                ->add('settlementOngoing', CheckboxType::class, array(
                    'value' => false,
                    'required' => false
                ))
                ->add('noticeType', ChoiceType::class, array(
                    'choices' => array(
                        'form.label.notice_type.vide' => 0,
                        'form.label.notice_type.0' => 0,
                        'form.label.notice_type.1' => 1,
                        'form.label.notice_type.2' => 2,
                        'form.label.notice_type.10' => 10,
                        'form.label.notice_type.50' => 50,
                        'form.label.notice_type.60' => 60,
                    )
                ))
                ->add('noticeStartDate', DatePickerType::class, array(
                    'required' => false //TODO: switch required if faute grave
                ))
                ->add('noticeEndDate', DatePickerType::class, array(
                    'required' => false //TODO: switch required if faute grave
                ))
                ->add('file', FileType::class, array(
                    'label' => 'Document de sortie',
                    'required' => false,
                ))
                ;
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SageBundle\Entity\Employee\Contract',
            'start' => true,
            'end' => true,
            'company' => null,
            'validation_groups' => function (FormInterface $form) {
                $options = $form->getConfig()->getOptions();
                if ($options['end']) {
                    return array('Default', 'EndContract');
                }

                return array('Default');
            }
        ));
    }
}
