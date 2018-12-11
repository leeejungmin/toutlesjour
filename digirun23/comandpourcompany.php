<?php

namespace SageBundle\Command;

use SageBundle\Entity\Employee;
use SageBundle\Serializer\Normalizer\DigiPayNormalizer;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\CsvEncoder;

class DigiPayImportVariableCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('digipay:import:variable')
            ->setDescription('Import DigiPay variables with mapping file')
            ->addArgument('company_id', InputArgument::REQUIRED, 'Company Id')
            ->addArgument('variables', InputArgument::REQUIRED, 'Employees variables')
            ->addArgument('mapping', InputArgument::REQUIRED, 'Mapping')
            ->addOption('dry-run', 'w', InputOption::VALUE_NONE, 'Test run. Only output debug')
            ->addOption('min-date', 'm', InputOption::VALUE_REQUIRED, 'Minimal date to import variable. format: Ymd')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $company = $input->getArgument('company_id');
        $manager = $this->getContainer()->get('doctrine')->getManager();
        $company = $manager->getRepository('SageBundle:Company')->find($company);

        if (!$company) {
            $outpout->writeln('Company does not exist');

            return;
        }

        $minDate = false;
        if ($input->getOption('min-date')) {
            $minDate = \DateTime::createFromFormat('Ymd', $input->getOption('min-date'));
            if (!$minDate) {
                $output->writeln('Wrong format for minimal date.');

                return;
            }
        }

        $logger = new ConsoleLogger($output);
        $serializer = $this->getContainer()->get('sage.serializer.csv');

        $variables = $serializer->decode(file_get_contents($input->getArgument('variables')), 'csv');
        $mapping = $serializer->decode(file_get_contents($input->getArgument('mapping')), 'csv');
        $mapping = array_filter($mapping, function($a) { return !empty($a['digipay_section']); });
        $allVariables = array();

        if (!empty($mapping)) {
            $vManager = $this->getContainer()->get('app.manager.variable');
            $eRepo = $manager->getRepository('SageBundle:Employee');
            $vRepo = $manager->getRepository('SageBundle:Employee\Variable');
            $availableSections = $this->getContainer()->getParameter('sage_section_variable_list');

            foreach ($variables as $variable) {
                $e = $eRepo->findOneBy(array(
                    'company' => $company,
                    'serial' => trim($variable['serial'])
                ));
                $d = \DateTime::createFromFormat('d/m/Y', $variable['date_section']);
                if ($e && $d && (!$minDate || $minDate <= $d)) {
                    $periodVariables = array();
                    foreach ($mapping as $map) {
                        //ON coala must iterate over all mapping
                        //ON sage leave once 1 is done
                        $dpSection = false;
                        $v = null;
                        //Has Mapping
                        if (isset($variable[$map['original_section']])) {
                            $dpSection = $map['digipay_section'];
                            $v = $variable[$map['original_section']];
                        }
                        if (isset($variable['section_code']) && $variable['section_code'] == $map['original_section']) {

                            $dpSection = $map['digipay_section'];
                            //Get value depending on type of section
                            switch ($vManager->getTypeByCode($dpSection)) {
                                case 0:
                                    $v = $variable['nombre'];
                                    break;
                                case 1:
                                    $v = $variable['base'];
                                    break;
                                case 3:
                                    $v = $variable['montant_salarial'];
                                    break;
                                default:
                                    $v = null;
                            }
                            //$logger->info('['. $variable['serial'] .']' . $variable['section_code'] . ' -> ' . $dpSection . ':' . $vManager->getTypeByCode($dpSection) . ':' . $v);
                        }
                        //Check if value is int and different from 0.
                        //Remove space and convert comma
                        $v = abs(floatval(str_replace(',', '.', str_replace(' ', '', $v))));
                        if ($dpSection && in_array($dpSection, $availableSections) && $v) {
                            $hkey = $variable['serial'] . '_' . $variable['date_section'] . '_' . $dpSection;

                            //Check variables and cumul it over period
                            if (!isset($allVariables[$hkey])) {
                                $ev = $vManager->initByCode($dpSection);
                                $ev->setEmployee($e);
                                $ev->setValidFrom((clone $d)->modify('first day of this month 00:00:00'));
                                $ev->setValidUntil((clone $d)->modify('last day of this month 23:59:59'));
                                $ev->setValue(0);
                                //Check if Exists
                                $vv = $vRepo->findOneBy(array(
                                    'employee' => $ev->getEmployee(),
                                    'code' => $ev->getCode(),
                                    'validFrom' => $ev->getValidFrom(),
                                    'validUntil' => $ev->getValidUntil(),
                                ));
                                //Overwrite existing variable
                                if ($vv) {
                                    $ev = $vv;
                                    $ev->setValue(0);
                                }
                                $allVariables[$hkey] = $ev;
                            }
                            $allVariables[$hkey]->setValue($ev->getValue() + $v);
                        }
                    }
                }
            }
        }

        $dry = $input->getOption('dry-run');

        foreach ($allVariables as $variable) {
            $logger->info('[' . $variable->getEmployee()->getSerial() . ']Variable:' . $variable->getCode() . ':Period:' . $variable->getValidFrom()->format('Ymd') . ':value:' . $variable->getValue());
            if (!$dry) {
                $manager->persist($variable);
            }
        }
        if (!$dry) {
            $manager->flush();
        }

        $output->writeln('Done.');
    }

}
