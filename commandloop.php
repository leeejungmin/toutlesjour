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
