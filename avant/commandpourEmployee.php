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
            ->addArgument('check', InputArgument::REQUIRED, 'y/n')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
              $employee = $input->getArgument('check');
              $manager = $this->getContainer()->get('doctrine')->getManager();
              $employees = $manager->getRepository("SageBundle:Employee\Event");

              if (!$employees) {
                  $output->writeln('Employee Event does not exist');
                  return;
              }
              if (!$employee == 'n') {
                  $output->writeln('Go back');
                  return;
              }

              if($employee == 'y'){
                 foreach ($employees as $employ) {

                      $employees->remove($employ);
                      //$manager->remove($employees);
                  }
                  $manager->flush();
                  $msj="All of Employee event is deleted.";
              }
              //Send all the invite once access have been created
              $output->writeln($msj);
        $output->writeln('DONE');
    }

}
