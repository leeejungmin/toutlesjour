<?php

namespace AppBundle\Controller\Backend;

use Symfony\Component\Process\Process;
use SageBundle\Entity\Reference\EventType;
use SageBundle\Form\References\EventType as EventTypeForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;
use SageBundle\Entity\Company;
use SageBundle\Entity\Company\Closure;

/**
 * @Route("/advanced")
 * @Security("has_role('ROLE_PAY_ADMIN')")
 */
class AdvanceVariableController extends Controller
{
    /**
     * @Route("/index")
     */
    public function indexAction(Request $request)
    {
        //AppBundle:Backend:Advanced/index.html.twig
        return $this->render('AppBundle:Backend:Advanced/index.html.twig', array(

        ));
    }
    /**
     * @Route("/{company}/status/{closure}")
     */
    public function statusAction(Request $request, Company $company, Closure $closure )
    {
        $manager = $this->getDoctrine()->getManager();
        $closures = $manager->getRepository('SageBundle:Company\Closure')->find($closure);
        dump($request);

            $closures->setStatus($status);
            $manager->persist($closure);
            $manager->flush();

            return $this->redirectToRoute('app_backend_advanced_index_index', array(
                'company' => $company->getId(),
            ));

        //$closure->setStatus($status);
        return $this->render('AppBundle:Backend:Advanced/status.html.twig', array(

        ));
    }

    /**
     * @Route("/closure")
     */
    public function closureAction( Request $request)
    {
        $manager = $this->getDoctrine()->getManager();
        $companys = $manager->getRepository('SageBundle:Company')->findAll();
        //AppBundle:Backend:Advanced/closure.html.twig
        return $this->render('AppBundle:Backend:Advanced/closure.html.twig', array(
            'companys' => $companys
        ));
    }

    /**
     * @Route("/{company}/getClosures")
     * @Method("GET")
     * @Security("has_role('ROLE_PAY_ADMIN')")
     */
    public function companysearchAction(Request $request, Company $company)
    {
//dump($company);die;
        $manager = $this->getDoctrine()->getManager();
        $closures = $manager->getRepository('SageBundle:Company\Closure')->findBy(
            array('company' => $company)
        );
        //$closures = $manager->getRepository('SageBundle:Company\Closure')->getCurrentByCompany($company);
        $output = array();
        $outputId = array();
        //$output = '';


        foreach ($closures as $closure){
            //$output .= $closure->getPeriodStart()->format('m-y');
            //$output .= '';
            //$outputclosure = $closure->getPeriodStart()->format('m-y');
           array_push($output,$closure->getPeriodStart()->format('M Y'));
           array_push($outputId,$closure->getId());
        }
        //dump($output);
        //dump($closures);

        //dump('goodjungmin');die;


        $results = $output;
        $response = new JsonResponse();
        $response->setData(array('data' => $results,
            'id' => $outputId));

        return $response;
    }
}
