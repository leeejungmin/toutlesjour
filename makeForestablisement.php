<?php

namespace SageBundle\Form\Workflow;

use AppBundle\Form\Type\DatePickerType;
use SageBundle\Form\SageReferenceEntityType;
use SageBundle\Form\Employee\FileType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormInterface;

class AddContractForExternType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $company = $options['company'];
        $builder
            /*->add('type', EntityType::class, array(
                'class' => 'SageBundle:Reference\ContractType',
                'choice_label' => function ($val) {
                    return $val->getCode() . ' | ' . $val->getTitle();
                }
            ))*/
            ->add('typeReason', SageReferenceEntityType::class, array(
                'reference_type' => 'contract_type_reason',
                'required' => false
            ))
            ->add('startDate', DatePickerType::class, array(
                'required' => true
            ))
            ->add('endDate', DatePickerType::class, array(
                'label' => 'Fini le',
            ))
            /*->add('universityName', TextType::class, array(
                'label' => 'Nom de l\'école',
                'required' => false,
            ))
            ->add('tutorName', TextType::class, array(
                'label' => 'Nom du tuteur',
                'required' => false,
            ))*/
            ->add('interimAgency', TextType::class, array(
                'label' => 'Nom de l’agence d’intérim de rattachement',
                'required' => false,
            ))
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
            ))/*
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
            ->add('codeInsee', EntityType::class, array(
                'placeholder'=>'',
                'label' => 'Catégorie Sociopro',
                'class' => 'SageBundle:Reference\JobType',
                'choice_label' => function ($val) {
                    return $val->getInsee() . ' | ' . $val->getTitle();
                }
            ))*/
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'company' => null,
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
