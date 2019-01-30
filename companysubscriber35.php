<?php
namespace AppBundle\EventListener;

use AppBundle\AppBundleEvents;
use AppBundle\Event\EmployeeUpdatePaySlipEvent;
use SageBundle\Entity\Employee\PaySlip;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EmployeePaySlipSubscriber implements EventSubscriberInterface
{

    public function __construct($scheduleRepo)
    {
        $this->scheduleRepo = $scheduleRepo;
    }

    public static function getSubscribedEvents()
    {
        return array(
            AppBundleEvents::UPDATE_PAYSLIP => 'onUpdatePaySlip'
        );
    }

    public function onUpdatePaySlip(EmployeeUpdatePaySlipEvent $event)
    {
        //dump($event);die;
        $entity = $event->getPaySlip();
        //Set yearlyBaseWage
        $yearlyBaseWage = $entity->getBaseWage() * $entity->getMonthCount();
        $entity->setYearlyBaseWage($yearlyBaseWage);
        //Set schedule
        //$weeklySchedule = 0;
       /* if(empty($entity->getSchedule())||empty($entity->getweeklySchedule())){
            $weeklySchedule = 35;
        }else{
            $schedule = $entity->getSchedule();
            $weeklySchedule = $entity->getweeklySchedule();
        }*/
        if ($entity->getEmployee()->getScheduleModel()) {
            //$weeklySchedule = 35;
            $title = $entity->getEmployee()->getScheduleModel()->getTitle();
            $matches = array();
            //Calc total hours only if schedule is less than 35H for Hourly schedule.
            if (preg_match('/(?P<type>\w)_(?P<value>\d+(\.\d+)?)-.*/', $title, $matches) === 1) {
                if ($matches['type'] == "H" && floatval($matches['value']) < 35.0)
                    $weeklySchedule = $this->scheduleRepo->getTotalHours(
                        $entity->getEmployee()->getScheduleModel());
            }
        }
        $schedule = $weeklySchedule * 52 / 12;
        $entity->setWeeklySchedule($weeklySchedule);
        $entity->setSchedule($schedule);
    }
}
