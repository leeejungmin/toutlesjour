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

class AddContractForStagiereType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $company = $options['company'];
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
            ->add('type', EntityType::class, array(
                'class' => 'SageBundle:Reference\ContractType',
                'choice_label' => function ($val) {
                    return $val->getCode() . ' | ' . $val->getTitle();
                }
            ))
            ->add('startDate', DatePickerType::class, array(
                'required' => true
            ))
            ->add('endDate', DatePickerType::class, array(
                'required' => false
            ))

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
            'validation_groups' => array('Default', 'FixContract')
        ));
    }
}
