<?php
class DashboardController extends Controller
{
    /**
     * @Route("/{whitelabel}/whitebrand/{start}", defaults={"start"=null})
     * @ParamConverter("start", options={"format": "Y-m-d"})
     */
    public function indexAction(WhiteLabel $whitelabel, \DateTime $start = null)
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
            //When we do not have the date
            //$interval = $this->get('app.helper')->getActualClosureInterval($whitelabel);
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
        $companys =  $manager->getRepository('SageBundle:Company')->getComapniesByWhiteBrand($whitelabel);
        $datas = array();
        $companyPreclosureHistory = $manager->getRepository("SageBundle:Company\PreClosureHistory");

        $employeeRepo = $this->getDoctrine()->getRepository('SageBundle:Employee');
        $eventRepo = $this->getDoctrine()->getRepository('SageBundle:Employee\Event');
        $paySlipManager = $this->get('app.manager.payslip');

        $interval = $this->get('app.helper')->getDateInterval($dateForInterval, 'month');
        $intervalYear = $this->get('app.helper')->getDateInterval($dateYear, 'year');

        foreach ($companys as $company){
            $localData = array();
            $localData["id"] = $company->getId();
            $localData["name"] = $company->getName();
            $localData["siret"] = $company->getSiret();
            $localData["PreclosureHistory"] = $companyPreclosureHistory->findBy(array(
            'company' => $company
            ), array(
                'creatAt' => 'DESC'
            ));
            //$localData["siren"] = $company->getName();

            $employeesCountYear =   $manager->getRepository('SageBundle:Employee')->countActiveByCompany($company, $intervalYear["start"], $intervalYear["end"]);
            $localData["number_salarys"] = $employeesCountYear;
            $closure =  $manager->getRepository('SageBundle:Company\Closure')
                ->getOneByCompanyAndPeriodStart($company,$dateClosure);

            $employees = $this->get('app.manager.user')->getManagedEmployeeByCompany($company);
            /*$pengingEvents = $eventRepo->findPendingEventByCompanyByCodesByEmployees($company,
                array_merge($this->getParameter('sage_event_type_leaves'),
                    $this->getParameter('sage_event_type_absences')),
                $employees
            );
            $pengingEvents = $eventRepo->findPendingEventByCompanyByCodesByEmployees($company,
                    $this->getParameter('sage_whitebrand_board_event'),
                    $employees
            );
            $localData["pendingEvents"] = count($pengingEvents);
            */

            /*$allEvents = $eventRepo->findAllEventByCompanyByCodesByEmployeesByDates($company,
                array_merge($this->getParameter('sage_event_type_leaves'),
                    $this->getParameter('sage_event_type_absences')),
                $employees,
                $interval["start"]->format("Y-m-d"),
                $interval["end"]->format("Y-m-d")
            );*/
            $allEvents = $eventRepo->findAllEventByCompanyByCodesByEmployeesByDates($company,
                    $this->getParameter('sage_whitebrand_board_event'),
                $employees,
                $interval["start"]->format("Y-m-d"),
                $interval["end"]->format("Y-m-d")
            );

            $localData["allEvents"] = count($allEvents);

            //Get the closure status
            $localData["closure"] = $closure;

            $localData["counters"] = $this->get('app.counter')->countGlobalsByCompanyByPeriod($company,
                $dateClosure,
                $dateEndCounters);

            //Get counter for the WhiteBrand Dashboard
            $localData["counters"]["employees"]["newcomerByPeriod"] = $employeeRepo->countNewcomerByCompanyByInterval($company, $dateClosure, $dateEndCounters);

            //Check if the bulletin file are available
            $localData["bulletin"] = null;
            $stateFolder = $paySlipManager->getPaySlipStatesFolderWhiteBrandDashboard($company, $dateYearMonthForFolder);
            if ($paySlipManager->canAccessMount($stateFolder)) {
                $finder = new Finder();
                $files = iterator_to_array( $finder->files()->in($stateFolder)->name('/^(.*)_BUL|BULLETINS_(.*)\.pdf$/i') );
                if($files){
                    $localData["bulletin"] = true;
                }
            }

            //Check if the etat file are available
            $localData["etat"] = null;
            if ($paySlipManager->canAccessMount($stateFolder)) {
                $finder = new Finder();
                $files = iterator_to_array( $finder->files()->in($stateFolder)->name('/^(.*)_ERC|ETAT_DES_CHARGES_(.*)\.pdf$/i') );
                if($files){
                    $localData["etat"] = true;
                }
            }
            $datas []=$localData;
        }

        return $this->render('AppBundle:Company/Dashboard:whitebrand.html.twig', array(
            'companys' => $datas,
            'whitelabel' => $whitelabel,
            'start' => $start
        ));
    }
