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
use SageBundle\Form\AdvancedClosureType;
use Symfony\Component\Finder\Finder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use DateTime;


/**
 * @Route("/Stats-production")
 * @Security("has_role('ROLE_PAY_ADMIN')")
 */
class StatsProductionController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        return $this->render('AppBundle:Backend:Stats/index.html.twig');
    }

    /**
     * @Route("/closures/{start}", defaults={"start"=null})
     * @ParamConverter("start", options={"format": "Y-m-d"})
     */
    public function closureAction(\DateTime $start = null)
    {

        if($start){
            //User choosed a specific month
            $interval = $this->get('app.helper')->getDateInterval($start, 'month');
        }else{
            $intervalStart = new DateTime();
            $intervalEnd = new DateTime();
            $interval['start'] = $intervalStart->modify("first day");
            $interval['end'] = $intervalEnd->modify("last day");
        }
        $datas = null;
        $start = $interval['start'];
        $end = $interval['end'];

        $manager = $this->getDoctrine()->getManager();

        $closures =  $manager->getRepository('SageBundle:Company\Closure')
            ->getClosureByCompanyAndDates($start, $end);

        foreach ($closures as $closure){
            $localData = array();
            $localData["etatcondition"] = null;
            $localData["id"] = $closure['companyid'];
            $localData["name"] = $closure['companyname'];
            $localData["closureStatus"] = $closure['status'];
            $localData["closureExisintFile"] = $closure['existingFile'];
            $localData["closureValidate"] = $closure['validatedAt'];
            //make condition for date different color
            if($localData["closureExisintFile"]){
              $nowTime = new DateTime();
              $diff=date_diff($nowTime,$localData["closureExisintFile"]);
              if(intval($diff->format('%d')) < 1){
                  $localData["etatcondition"] = 'lessthan1day';
              }elseif(intval($diff->format('%d')) == 1 || intval($diff->format('%d')) < 2){
                  $localData["etatcondition"] = 'lessthan2day';
              }elseif(intval($diff->format('%d')) > 2 ||intval($diff->format('%d')) == 2 ){
                  $localData["etatcondition"] = 'morethan2day';
              }
            }

            $datas[]=$localData;
        }

        return $this->render('AppBundle:Backend:Stats/closure.html.twig', array(
            'companys' => $datas,
            'start' => $start
        ));
    }


    /**
     * @Route("/status")
     */
    public function statusAction(Request $request, Company $company, Closure $closure )
    {

        $manager = $this->getDoctrine()->getManager();
        $closures = $manager->getRepository('SageBundle:Company\Closure')->find($closure);
        $form = $this->createForm(AdvancedClosureType::class, $closures, array(
        ));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $status = $form->get('status')->getData();
            $closures->setStatus($status);
            $manager->persist($closure);
            $manager->flush();
            $this->addFlash('success', 'flash.closure.change.success.success');
            return $this->redirectToRoute('app_backend_advanceclosure_index');
        }
        return $this->render('AppBundle:Backend:Advanced/status.html.twig', array(
            'form' => $form->createView(),
            'company' => $company
        ));
    }
}
