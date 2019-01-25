<?php

namespace SageBundle\Form\Workflow;

use SageBundle\Form\CompanyCreateManuallyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AddManuallyCompanyWorkflowType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /*$builder
            ->add('siren', TextType::class);*/
        $company = $options['data']['company'];
        $builder
            ->add('company', CompanyCreateManuallyType::class, array(
                'data' => $company,
                'required' => $company->getSiren() != null,
                'preferred_idccs' => $options['preferred_idccs'],
                'required' => true
            ));

        //Month Range
        $formatter = \IntlDateFormatter::create(
            null,
            null,
            null,
            null,
            null,
            'y MMMM'
        );
        $month = new \DateTime("first day of this month");
        $choices = array();
        for ($i = 0; $i < 12; $i++)
        {
            $choices[$formatter->format($month)] = $month->format('Y-m');
            $month->modify('- 1 month');
        }
        $builder
            ->add('closureStartMonth', ChoiceType::class, array(
                'choices' => $choices,
                'required' => $company->getSiren() != null,
            ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'preferred_idccs' => array('idccs' => array(), 'percents' => array())
        ));
    }
}
