<?php
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteAllOfPlanificationEventCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:delete-planification-event')
            ->setDescription('Delete all events of planification')
            ->addOption('everything', InputArgument::OPTIONAL, 'Put YES');

        //Deleteplanification events
        //php bin/console app:delete-planification-event --everything=YES
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inputOption = $input->getOption('everything');
        $manager = $this->getContainer()->get('doctrine')->getManager();
        $countEventPlanification = 0;
        if ($inputOption && strcmp($inputOption, 'YES') == 0) {
            $allEventOfHours = $manager->getRepository("SageBundle:Employee\Planification\Hours")->findAll();
            $allEventOfHoursComment = $manager->getRepository("SageBundle:Employee\Planification\HoursComment")->findAll();
            $allEventOfClosure = $manager->getRepository("SageBundle:Employee\Planification\Closure")->findAll();
            $allEventOfClosureHistory = $manager->getRepository("SageBundle:Employee\Planification\History")->findAll();
            foreach ($allEventOfHours as $EventOfHours) {
                $manager->remove($EventOfHours);
            }
            foreach ($allEventOfHoursComment as $EventOfHoursComment) {
                $manager->remove($EventOfHoursComment);
            }
            foreach ($allEventOfClosure as $EventOfClosure) {
                $manager->remove($EventOfClosure);
            }
            foreach ($allEventOfClosureHistory as $EventOfClosureHistory) {
                $manager->remove($EventOfClosureHistory);
            }
            $manager->flush();
            $output->writeln('Delete All planification Event Count: ' . count($countEventPlanification));
            $output->writeln("All of planification event is deleted.");
        }
    }
}
