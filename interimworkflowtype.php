<?php

namespace SageBundle\Form\Workflow;

use AppBundle\Form\EmployeeAccountType;
use SageBundle\Form\People\CivilStatusType;
use SageBundle\Form\Employee\ContractType;
use SageBundle\Form\Employee\EstablishmentType;
use SageBundle\Form\Employee\JobType;
use SageBundle\Form\Employee\PaySlipType;
use SageBundle\Form\Workflow\AddPeopleForInterimType;
use SageBundle\Form\Workflow\AddContractForInterimType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class AddInterimWorkflowType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('people', AddPeopleForInterimType::class, array(
                'label' => 'Civil Status',
                'constraints' => new \Symfony\Component\Validator\Constraints\Valid(),
            ))
            ->add('contract', AddContractForInterimType::class, array(
                'label' => 'Contract',
                'end' => false,
                'company' => $options['company'],
                'constraints' => new \Symfony\Component\Validator\Constraints\Valid(),
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            //        'data_class' => 'SageBundle\Entity\Employee'
            'company' => null,
            'validation_groups' => array('NewEmployee')
        ));
    }
}
