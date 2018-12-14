<?php
if($employee->getId()==6646) {


            if ($companyId) {
            $employees = $manager->getRepository("SageBundle:Employee")->findBy(
                array('company' => $companyId)
            );
            //dump($employee);die;
            //Get the events of the specific employee
                  dump($employee->getId());
                  dump(count($employee->getEvents()));
                  //dump($companyEvents);die;
                  dump("-------");
                  for ($i = 1; $i <= count($employee->getEvents()); $i++) {
                      $employ = $employee->getEvents()->get($i);
                      $manager->remove($employ);
                      dump($i);
                      $manager->flush();
                  }

                  while (($employee->getEvents()->get($countOneEventCompany))!=null) {

                       $employ = $employee->getEvents()->get($countOneEventCompany);
                       $manager->remove($employ);
                       $output->writeln("deleting is processing now .");
                       dump($countOneEventCompany);
                       $manager->flush();
                       $countOneEventCompany++;
                   }
                  // $employ = $employee->getEvents()->getMapping();
                  //$employ = $employee->getEvents()->getValues( )
                  //$manager->flush();
                  $output->writeln("One Company of Employee event is deleted.");
                  }
            foreach ($employees as $employee) {
                $companyEvents = $employee->getEvents();
                dump($employee->getId());
                //$companyEvents->removeEvents($companyEvents);
                //$manager->remove($companyEvents);
               // dump($oneemployee); die;
               // array_push($array, $companyEvents);
                //$countOneEventCompany++;
            }
            //$output->writeln('c\'est just array: ' . count($array));
           // $manager->flush();
           // $output->writeln('Delete Employees Event Count: '.$countOneEventCompany);
           // $output->writeln("One Company of Employee event is deleted.");

        }





        <?php

namespace AppBundle\Command;

use SageBundle\Entity\Employee;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use SageBundle\Serializer\Normalizer\EmployeeNormalizer;
use SageBundle\Serializer\Encoder\SageEncoder;
use SageBundle\Entity\Employee\Event;
use Doctrine\ORM\PersistentCollection;

class DeleteAllOfEmployeeEventCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:delete-employee-event')
            ->setDescription('Delete all events of employee')
            ->addOption('company', null, InputOption::VALUE_OPTIONAL, 'Put the id of the Company', false)
            ->addOption('employee', null, InputOption::VALUE_OPTIONAL, 'Put the id of the Employee', false)
            ->addOption('everything', null, InputOption::VALUE_OPTIONAL, 'Delete everything', false);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $companyId = $input->getOption('company');
        $everythingParameter = $input->getOption('everything');
        $employeeId = $input->getOption('employee');
        $manager = $this->getContainer()->get('doctrine')->getManager();
        $countOneEventEmployee = 0;
        $countOneEventCompany = 0;
        $countEveryEventEmployee = 0;
        if ($companyId) {
            $employees = $manager->getRepository("SageBundle:Employee")->findBy(
                array('company' => $companyId)
            );
            foreach ($employees as $employee) {
                $companyEvents = $employee->getEvents();
                    //Get the events of the specific employee
                    dump($employee->getId());
                    dump(count($employee->getEvents()));
                    dump("-------");
                $eachEventCount = 0;
                while (($employee->getEvents()->get($eachEventCount))!=null) {

                    $companyEvents = $employee->getEvents()->get($eachEventCount);
                    $manager->remove($companyEvents);
                    $output->writeln("deleting is processing now .");
                    $eachEventCount++;
                }
                    $output->writeln("One Company of Employee event is deleted.");
                    $countOneEventCompany++;
            }
            $manager->flush();
            $output->writeln('Delete Employees Event Count: '.$countOneEventCompany);
            $output->writeln("One Company of Employee event is deleted.");
        }
        if ($employeeId) {
            $employeesEvents = $manager->getRepository("SageBundle:Employee")->find($employeeId)->getEvents();

            foreach ($employeesEvents as $employeeEvent) {
                $manager->remove($employeeEvent);
                $countOneEventEmployee++;
            }
            $manager->flush();
            $output->writeln(' Delete One Employee Event Count: ' . $countOneEventEmployee);
            $output->writeln("One Employee by event is deleted.");
        }
        if ($everythingParameter && strcmp($everythingParameter, 'YES') == 0) {
            $allEventOfEmployee = $manager->getRepository("SageBundle:Employee\Event")->findAll();
            foreach ($allEventOfEmployee as $EventOfEmployee) {
                $manager->remove($EventOfEmployee);
                $countEveryEventEmployee++;
            }
            $manager->flush();
            $output->writeln('Delete All Employee Event Count: ' . count($allEventOfEmployee));
            $output->writeln($countEveryEventEmployee);
            $output->writeln("All of Employee event is deleted.");

        }
    }
}
