<?php

namespace AppBundle\Command;

use SageBundle\Entity\Employee;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use SageBundle\Serializer\Normalizer\EmployeeNormalizer;
use SageBundle\Serializer\Encoder\SageEncoder;

class DeleteAllOfEmployeeEventCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:delete-employee-event')
            ->setDescription('Delete all events of employee')
            ->addArgument('check', InputArgument::OPTIONAL, 'y/n')
            ->addOption('company_id',null, InputOption::VALUE_OPTIONAL, 'Put the id of the Company',false)
            ->addOption('employee_id',null, InputOption::VALUE_OPTIONAL, 'Put the id of the Employee',false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $employee = $input->getArgument('check');
        $companyid = $input->getOption('company_id');
        $employeeid = $input->getOption('employee_id');
        $manager = $this->getContainer()->get('doctrine')->getManager();
        $employees = $manager->getRepository("SageBundle:Employee\Event");
        $allemployees = $employees->findAll();

        $companyid = $employees->findByCompanyId($companyid);
        $employeeid = $employees->findByEmployeeId($employeeid);


        if (!$employees) {
            $output->writeln('Employee Event does not exist');
            if (!$employeeid) {
                $output->writeln('Employee id does not exist');

            }
            if (!$companyid) {
                $output->writeln('Company id does not exist');

            }
            return;
        }
        //dump($companyid);die;

        if ($employee == 'n') {
            $output->writeln('Go back');
            return;
        }

        if ($companyid || $employeeid){
            if(!$companyid){
                $employees =$employeeid;
                $output->writeln("One Employee by event is deleted.");
            }else{
                $employees =$companyid;
                $output->writeln("One Company of Employee event is deleted.");
            }
        }

        if($employee == 'y'){
                $employees = $allemployees;
                $output->writeln("All of Employee event is deleted.");
        }

        foreach ($employees as $employ) {

            $manager->remove($employ);

        }
        $manager->flush();
        $output->writeln('DONE');
    }

}


public function findByEmployeeId($employee)
   {
       return $this->createQueryBuilder('e')
           ->where('e.employee = :employee')
           ->setParameter('employee', $employee)
           ->getQuery()
           ->getResult();
   }
