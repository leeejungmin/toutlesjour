<?php

namespace AppBundle\Controller\Company;

use SageBundle\Entity\Company;
use SageBundle\Security\CompanyVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/dashboard")
 */
class DashboardController extends Controller
{
    /**
     * @Route("/")
     * Secured by SecurityListener
     * @Security("is_granted('DIGIPAYE_COMPANY_VIEW', company)")
     */
    public function indexAction(Company $company, \DateTime $start = null)
    {
        //Authorize for specific user
        $returntyperoute = $this->get('app.helper')->digipayeAccessValidation($company->getId(), $this->getUser()->getId());
        if(!$returntyperoute){
            return $this->redirectToRoute('app_access_index');
        }
        //Show the actual Closure on the Company Dashboard
        $interval = $this->get('app.helper')->getActualClosureInterval($company);
        $start = $interval['start'];
        $startDuplicateQueryStart = clone $interval['start'];
        $startDuplicateQueryStart = $startDuplicateQueryStart->modify("first day of ".$startDuplicateQueryStart->format("F Y")." 00:00:00 ");

        $startDuplicateQueryEnd = clone $interval['start'];
        $startDuplicateQueryEnd = $startDuplicateQueryEnd->modify("last day of ".$startDuplicateQueryEnd->format("F Y")." 23:59:59 ");

        $startDuplicateQueryEndPlusFifteenDays = clone $interval['start'];
        $startDuplicateQueryEndPlusFifteenDays = $startDuplicateQueryEndPlusFifteenDays->modify("last day of ".$startDuplicateQueryEndPlusFifteenDays->format("F Y")." 23:59:59 ");
        $startDuplicateQueryEndPlusFifteenDays = $startDuplicateQueryEndPlusFifteenDays->modify("+15 days");

        $startDuplicateQueryEndPlusOneMonth = clone $interval['start'];
        $startDuplicateQueryEndPlusOneMonth = $startDuplicateQueryEndPlusOneMonth->modify("last day of ".$startDuplicateQueryEndPlusOneMonth->format("F Y")." 23:59:59 ");
        $startDuplicateQueryEndPlusOneMonth = $startDuplicateQueryEndPlusOneMonth->modify("+1 month");

        //TODO: ListNotification
        $employeeRepo = $this->getDoctrine()->getRepository('SageBundle:Employee');
        $eventRepo = $this->getDoctrine()->getRepository('SageBundle:Employee\Event');
        $variableRepo = $this->getDoctrine()->getRepository('SageBundle:Employee\Variable');
        $fileRequestRepo = $this->getDoctrine()->getRepository('SageBundle:Employee\FileRequest');
        $fileRepo = $this->getDoctrine()->getRepository('SageBundle:Employee\File');
        $peopleRepo = $this->getDoctrine()->getRepository('SageBundle:People');
        $contractRepo = $this->getDoctrine()->getRepository('SageBundle:Employee\Contract');

        $employees = false;
        //Managers
        if (!$this->isGranted(CompanyVoter::COMPANY_EDIT, $company)) {
            //Empty to avoid request
            $pendingLog = array();
            $completedLog = array();
            $pendingBoarding = array();
            $counters = array();
            $pendingHours = array();
            $newcomers = array();
            $leavers = array();
            $uploadedFileRequests = array();
            $expiringFiles = array();
            $employeeAlerts = array();

            $employees = $this->get('app.manager.user')->getManagedEmployeeByCompany($company);
        } else {

            $changeLogRepo = $this->getDoctrine()->getRepository('SageBundle:Employee\ChangeLog');
            $pendingLog = $changeLogRepo->findPendingByCompanyIdSkipBoardingEmployee($company);
            $completedLog = $changeLogRepo->findCompletedByCompanyId($company);

            $pendingBoarding = $employeeRepo->getPendingBoardings($company,
                $startDuplicateQueryStart,
                $startDuplicateQueryEnd);

            $counters = $this->get('app.counter')->countGlobalsByCompanyByPeriod($company,
                $startDuplicateQueryStart,
                $startDuplicateQueryEnd);

            $pendingHours = $eventRepo->findPendingHoursByCompanyIdByEmployees($company, $employees);

            $newcomers = $employeeRepo->getNewcomerByCompany($company, $startDuplicateQueryStart);
            $leavers = $employeeRepo->getLeaverByCompany($company,
                $startDuplicateQueryStart,
                $startDuplicateQueryEnd);

            $uploadedFileRequests = $fileRequestRepo->getUploadedByCompany($company);

            $expiringFiles = $fileRepo->getFilesExpiringByCompanyByInterval($company,
                $startDuplicateQueryStart,
                $startDuplicateQueryEndPlusOneMonth);

            $employeeAlerts = $this->get('app.manager.alert')->getAlertsByCompanyByInterval($company,
                $startDuplicateQueryStart,
                $startDuplicateQueryEndPlusFifteenDays);

            /*
            $changeLogRepo = $this->getDoctrine()->getRepository('SageBundle:Employee\ChangeLog');
            $pendingLog = $changeLogRepo->findPendingByCompanyIdSkipBoardingEmployee($company);
            $completedLog = $changeLogRepo->findCompletedByCompanyId($company);

            $pendingBoarding = $employeeRepo->getPendingBoardings($company,
                new \DateTime('first day of this month 00:00'),
                new \DateTime('last day of this month 23:59'));

            $counters = $this->get('app.counter')->countGlobalsByCompanyByPeriod($company,
                new \DateTime('first day of this month 00:00'),
                new \DateTime('last day of this month 23:59'));

            $pendingHours = $eventRepo->findPendingHoursByCompanyIdByEmployees($company, $employees);

            $newcomers = $employeeRepo->getNewcomerByCompany($company, new \DateTime('first day of this month 00:00'));
            $leavers = $employeeRepo->getLeaverByCompany($company,
                new \DateTime('first day of this month 00:00'),
                new \DateTime('last day of this month 23:59'));

            $uploadedFileRequests = $fileRequestRepo->getUploadedByCompany($company);

            $expiringFiles = $fileRepo->getFilesExpiringByCompanyByInterval($company,
                new \DateTime('first day of this month 00:00'),
                new \DateTime('last day of this month 23:59 + 1 month')
            );

            $employeeAlerts = $this->get('app.manager.alert')->getAlertsByCompanyByInterval($company,
                new \DateTime('first day of this month 00:00'),
                new \DateTime('last day of this month 23:59 + 15 days'));
            */
        }

        $pendingEvents = $eventRepo->findPendingEventByCompanyByCodesByEmployees($company,
            array_merge($this->getParameter('sage_event_type_leaves'),
                $this->getParameter('sage_event_type_absences')),
            $employees
        );
        $filesCode = $this->get('app.manager.variable')->getVariableCodesByFiles($expiringFiles);
        $variableTitles = $this->get('app.manager.variable')->getTitlesByCompany($company);

        return $this->render('AppBundle:Company/Dashboard:index.html.twig', array(
            'company' => $company,
            'pending_boarding' => $pendingBoarding,
            'pending_log' => $pendingLog,
            'pending_events' => $pendingEvents,
            'pending_hours' => $pendingHours,
            'newcomers' => $newcomers,
            'leavers' => $leavers,
            'counters' => $counters,
            'uploadedFileRequests' => $uploadedFileRequests,
            'expiringFiles' => $expiringFiles,
            'filesCode' => $filesCode,
            'variableTitles' => $variableTitles,
            'employeeAlerts' => $employeeAlerts,
            'start' => $start
        ));
    }
}
