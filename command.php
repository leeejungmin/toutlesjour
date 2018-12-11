<?php
protected function execute(InputInterface $input, OutputInterface $output)
   {
       $employeeParameter = $input->getArgument('check');
       $companyId = $input->getOption('company');
       $everythingParameter = $input->getOption('everything');
       $employeeId = $input->getOption('employee');
       $manager = $this->getContainer()->get('doctrine')->getManager();



       //Message starting operation

       if($everythingParameter && strcmp($everythingParameter,'YES')==0){
           //Delete all the employees events of all the companys
            $companyRepo = $manager->getRepository("SageBundle:Company\Event");
            $company = $companyRepo->find($companyId);
            if($company){
                foreach ($company as $companyone) {
                    $manager->remove($companyone);
                    }
                    $manager->flush();
                    $output->writeln(' Company Count: '.count($company));
                    $output->writeln("One Company of Employee event is deleted.");
                //Delete events
                //Get count and show everything
            }

            if($employeeId){
              $employeesRepo = $manager->getRepository("SageBundle:Employee\Event");
              $employee = $employeesRepo->find($employeeId);
              foreach ($employee as $employeeone) {
                  $manager->remove($employeeone);
                  }
                  $manager->flush();
                  $output->writeln(' Company Count: '.count($employee));
                  $output->writeln("One Employee by event is deleted.");
            }
          }
         if($everythingParameter){
           $allemployees = $manager->getRepository("SageBundle:Employee\Event")->findAll();
           foreach ($allemployees as $employeeone) {
               $manager->remove($employeeone);
               }
               $manager->flush();
               $output->writeln(' Company Count: '.count($allemployees));
               $output->writeln("All of Employee event is deleted.");
         }

       }


       //Show message end operation OK
