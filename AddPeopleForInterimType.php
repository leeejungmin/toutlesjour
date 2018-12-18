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
            ->add('universityName', TextType::class, array(
                'label' => 'Nom de l\'école',
                'required' => false,
            ))
            ->add('tutorName', TextType::class, array(
                'label' => 'Nom du tuteur',
                'required' => false,
            ))
            ->add('interimAgency', TextType::class, array(
                'label' => 'Nom de l’agence d’intérim de rattachement',
                'required' => false,
            ))


        ;
    }
    ->add('universityName', EntityType::class, array(
                'class' => 'SageBundle:Employee\Contract',
                'label' => 'Nom de l\'école',
                'required' => false,
            ))
            ->add('tutorName', EntityType::class, array(
                'class' => 'SageBundle:Employee\Contract',
                'label' => 'Nom du tuteur',
                'required' => false,
            ))
            ->add('interimAgency', EntityType::class, array(
                'class' => 'SageBundle:Employee\Contract',
                'label' => 'Nom de l’agence d’intérim de rattachement',
                'required' => false,
            ))

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'company' => null,
            'data_class' => 'SageBundle\Entity\Employee\Contract',
            'validation_groups' => array('Default', 'FixContract')
        ));
    }
}
