<?php
namespace AppBundle\Command;
use SageBundle\Entity\Company\Closure;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use AppBundle\Services\PaySlipManager;
use DateTime;

class CheckExistingFileInClosureCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('app:check-existing-file-in-closure')
            ->setDescription('Check Existing File In Closure Command');

        //Check Existing File In Closure with month and year
        //php bin/console app:check-existing-file-in-closure --env=demo
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = $this->getContainer()->get('doctrine')->getManager();
        $countExistingFile = 0;

        $dateStartstring  = strtotime((new DateTime())->modify("first day of")->format('Y-m-d 00:00:00'));
        $dateStart  = date('Y-m-d H:i:s',$dateStartstring);
        $dateEndstring = strtotime((new DateTime())->modify("last day of")->format('Y-m-d 23:59:59'));
        $dateEnd = date("Y-m-d H:i:s", $dateEndstring);
        $respoClosure = $manager->getRepository('SageBundle:Company\Closure');
        $existFiles = $manager->getRepository('SageBundle:Company\Closure')
            ->getClosureByCompanyAndDates($dateStart, $dateEnd);

        $dateYearMonthForFolder= (new DateTime())->format("Y/m");
        foreach ($existFiles as $existFile) {
            if($existFile["validatedAt"]!=null && $existFile["existingFile"]==null ){
                $stateFolder = $this->getContainer()->get('app.manager.payslip')->getPaySlipStatesFolderforCommand($existFile['sagedbname'], $dateYearMonthForFolder);
                $findByIdClosure = $respoClosure ->findBy(
                    array('id' => $existFile['id'])
                );

                if ($paySlipManager = $this->getContainer()->get('app.manager.payslip')->canAccessMount($stateFolder)) {
                    $finder = new Finder();
                    $files = iterator_to_array( $finder->files()->in($stateFolder)->name('/^(.*)_BUL|BULLETINS_(.*)\.pdf$/i') );
                    $forfile = array();
                    foreach ($files as $file) {
                        array_push($forfile,$file);
                    }
                    if($files && $forfile[0]){
                        $companyfiledate = strtotime(date("Y-m-d ", $forfile[0]->getMTime()));
                        $date = new DateTime();
                        $date->setTimestamp($companyfiledate)->format("Y-m-d H:i:s");
                        $findByIdClosure[0]->setExistingFile($date);
                        $manager->persist($findByIdClosure[0]);
                    }
                }
                $countExistingFile++;
            }
        }
        $manager->flush();
        $output->writeln("Number Of Existing File In Closure: " . $countExistingFile);
        $output->writeln("Check Existing File In Closure is finished.");
    }
}
