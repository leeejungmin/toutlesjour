<?php

namespace AppBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Louis21Bundle\Entity\Debug;

class DebugType extends AbstractType
{
    protected $codes;

    public function __construct($codes = array())
    {
        $this->codes = $codes;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$codes = $options['codes'];
        $builder
          ->add('headerCode', IntegerType::class)
          ->add('module', TextType::class)
          ->add('step', TextType::class)
          ->add('uri', TextType::class)
          ->add('sent', TextType::class)
          ->add('returnServer', TextType::class)
          ->add('returnCurl', TextType::class)
          ->add('comment', TextType::class)
          ->add('DateTime', DateTimeType::class)
            )
        ;

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Louis21Bundle\Entity\Debug',
        ));
    }
}

/**
    * @Route("/debug/treat/{id}" )
    */
   public function redoAction($id, Debug $debug,Company $company)
   {
       $name = "houssem1234";

       if($debug->getModule()=='tenant'){
           if($debug->getStep()=='tenant_creation'){

              $this->get('louis21.essential.token')->tenantCreation($name, $company);

           }else{
               dump("hell jungmin");
               $this->get('louis21.essential.token')->editTenant($name, $company);

           }
       }elseif($debug->getModule()==='user'){
           if($debug->getStep()==='user-creation'){
              //avec houssem
           }
       }
       $debugs= $this->getDoctrine()->getManager()->getRepository('Louis21Bundle:Debug')->findAll();

       return $this->render('AppBundle:Backend/Louis21Debug:debug.html.twig', array(
           'debugs' => $debugs,

       ));
   }




   ->add('headerCode', IntegerType::class, array(
                'disabled' => 'true',
            ))
            ->add('module', TextType::class, array(
                'disabled' => 'true',
            ))
            ->add('step', TextType::class, array(
                'disabled' => 'true',
            ))
            ->add('uri', TextType::class, array(
                'disabled' => 'true',
            ))
            ->add('sent', TextType::class)
            ->add('returnServer', TextType::class, array(
                'disabled' => 'true',
            ))
            ->add('returnCurl', TextType::class, array(
                'disabled' => 'true',
            ))
            ->add('comment', TextType::class, array(
                'disabled' => 'true',
            ))
            ->add('DateTime', DateTimeType::class, array(
                'disabled' => 'true',
            ))
