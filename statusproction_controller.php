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
            $dateClosure = $start->format("Y-m-01 00:00:00");
            $dateEndCounters = clone $start;
            $dateEndCounters = $dateEndCounters->modify("last day of ".$dateEndCounters->format("F Y")." 23:59:59 ");
            $dateForInterval = $start->format("Y-m-01");
            $dateYearMonthForFolder= $start->format("Y/m");
            $dateYear= $start->format("Y");
        }else{
            $intervalStart = new DateTime();
            $intervalEnd = new DateTime();
            $interval['start'] = $intervalStart->modify("first day");
            $interval['end'] = $intervalEnd->modify("last day");
            $dateClosure = date("Y-m-01 00:00:00", strtotime("now"));
            $dateForInterval = date("Y-m-01", strtotime("now"));
            $dateYearMonthForFolder = date("Y/m", strtotime("now"));
            $dateEndCounters    = new \DateTime('last day of this month 23:59:59');
            $dateYear= date("Y", strtotime("now"));
        }
        $start = $interval['start'];
        $manager = $this->getDoctrine()->getManager();
        $closures =  $manager->getRepository('SageBundle:Company\Closure')
            ->getClosureByCompanyAndDates($dateClosure,$dateEndCounters);
        $dateformatforPreclosure = new DateTime($dateClosure);
        foreach ($closures as $closure){
            $localData = array();
            $localData["id"] = $closure['companyid'];
            $localData["name"] = $closure['companyname'];
            $localData["closureStatus"] = $closure['status'];
            $localData["closureValiddate"] = $closure['validatedAt'];
            //Check if the etat file are available
            /*$localData["etat"] = null;
            if ($paySlipManager->canAccessMount($stateFolder)) {
                $finder = new Finder();
                $files = iterator_to_array( $finder->files()->in($stateFolder)->name('/^(.*)_ERC|ETAT_DES_CHARGES_(.*)\.pdf$/i') );
                $forfile = array();
                foreach ($files as $file) {
                    array_push($forfile,$file);
                }
                if($files && $forfile[0]){
                    $localData["etat"] = strtotime(date("Y-m-d H:i:s", $forfile[0]->getMTime()));
                }
            }*/
            $datas []=$localData;
        }
        return $this->render('AppBundle:Backend:Stats/closure.html.twig', array(
            'companys' => $datas,
            'start' => $start
        ));
    }

    /**
     * @Route("/{company}/getClosures")
     * @Method("GET")
     * @Security("has_role('ROLE_PAY_ADMIN')")
     */
    public function companysearchAction(Request $request, Company $company)
    {
        $manager = $this->getDoctrine()->getManager();
        $closures = $manager->getRepository('SageBundle:Company\Closure')->findBy(
            array('company' => $company),
            array('periodStart' => 'DESC')
        );
        $output = array();
        $outputId = array();
        foreach ($closures as $closure){
            array_push($output,$closure->getPeriodStart()->format('M Y'));
            array_push($outputId,$closure->getId());
        }
        $results = $output;
        $response = new JsonResponse();
        $response->setData(array('data' => $results,
            'id' => $outputId));

        return $response;
    }
    /**
     * @Route("/{company}/status/{closure}")
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
