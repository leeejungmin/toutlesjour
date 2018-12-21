<?php
$form = $this->get('app.manager.form')->createWorkflowAddExternForm($company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $contract = $data['contract'];
            $chekckExternType = $request->request->get('add_extern_workflow')['choiceoption'];
            //this is for setting fakeinformation by id.
            $manager = $this->getDoctrine()->getManager();
            $contractType = $manager->getRepository('SageBundle:Reference\ContractType');
            $collectiveAgreement = $manager->getRepository('SageBundle:Reference\CollectiveAgreement');
            $jobType = $manager->getRepository('SageBundle:Reference\JobType');
            $jobType = $jobType->findOneBy(array(
                'code' => 'CSU',
            ));
            $collectiveAgreement = $collectiveAgreement->find(1019);
            $contract->setCodeInsee($jobType);
            $company->setCollectiveAgreement($collectiveAgreement);
            if($chekckExternType=='interim'){
                $contractType = $contractType->findOneByCode('INT');
            }else if($chekckExternType=='stage'){
                //Contract
                $contractType = $contractType->findOneByCode('CSU');
                $contract->setType($contractType);
                $payslip  = $data['payslip'];
            }
            $contract->setReasonForEntry('EX');//External
            $contract->setType($contractType);
            $employee = $data['employee'];
            $people = $data['people'];
            $job      = $data['job'];

            //Set Relations
            $employee->setCompany($company);
            $employee->setPeople($people);
            $contract->setEmployee($employee);
            $job->setEmployee($employee);
            //Persist
            $manager = $this->getDoctrine()->getManager();
            if($chekckExternType=='stage'){
                $payslip->setEmployee($employee);
                $manager->persist($payslip);
            }
            $manager->persist($people);
            $manager->persist($employee);
            $manager->persist($contract);
            $manager->persist($job);
            $manager->flush();
            $userType = $this->get('app.user_type_manager')->getUserType();
            //TRUE=Employee <=> FALSE=White Brand DRH or ROLE_PAY_ADMIN
            if ($userType) {
                $disEvent = new EmployeeCreateEvent($employee, $contract);
                $this->get('event_dispatcher')->dispatch(AppBundleEvents::NEW_EMPLOYEE, $disEvent);
            } else {// FALSE= {only for ROLE_PAY_ADMIN}
                // Notification for PayAdmin
                // Boarding_Notification
                $boardingConfigNotificationAdmin = $manager->getRepository("SageBundle:Config\Notification\Module\Payadmin")
                    ->findOneBy(array(
                        'enabled' => '1',
                        'id' => '2'//Entrees
                    ));

                if ($boardingConfigNotificationAdmin) {
                    // Get Admin @
                    $emails = $manager->getRepository('AppBundle:User')->getEmailsByRole('ROLE_PAY_ADMIN');

                    // Title construction
                    $title = $this->get('translator')->trans('mail.boarding_title_employee', array(
                        '%company%' => $employee->getCompany()->getName(),
                        '%serial%' => $employee->getSerial(),
                        '%name%' => $employee->getPeople()->getFirstName() . ' ' . $employee->getPeople()->getLastName()
                    ));

                    $from = null;
                    $copyright = "";
                    $urlParams = array(
                        'company' => $employee->getCompany()->getId(),
                        'id' => $employee->getId()
                    );

                    // Get mailer service and executing action
                    $mailer = $this->get('app.mail_sender');
                    $mailer->sendMail(
                        $emails,
                        $title,
                        'AppBundle:Mail:employee_create_dpae.html.twig',
                        array(
                            'title' => $title,
                            'employee' => $employee,
                            'contract' => $contract,
                            'url_token' => $this->generateUrl('app_company_employee_show', $urlParams, UrlGeneratorInterface::ABSOLUTE_URL),
                            'copyright' => $copyright
                        ),
                        null,
                        $from
                    );
                }
            }

            return $this->redirectToRoute('app_company_employee_show', array(
                'company' => $company->getId(),
                'id' => $employee->getId()
            ));
        }

        $setTypeReason = $request->request->get('add_extern_workflow')['contract']['typeReason'];
        $setbankIban = '';
        $setpayslip  = '';
        if(isset($request->request->get('add_extern_workflow')['people']['bankIban']) || isset($request->request->get('add_extern_workflow')['people']['payslip'])){
            $setbankIban = $request->request->get('add_extern_workflow')['people']['bankIban'];
            $setpayslip = $request->request->get('add_extern_workflow')['payslip'];
        }
        //Get Employee  for Dulplicate Function
        $getEmployeeMatricule = $this->getDoctrine()->getRepository('SageBundle:Employee')->findBy(array('company'=>$company));

        return $this->render('AppBundle:Company/Workflow:add_extern.html.twig', array(
            'company' => $company,
            'form' => $form->createView(),
            'getEmployeeMatricule' => $getEmployeeMatricule,
            'setTypeReason' => $setTypeReason,
            'setbankIban' => $setbankIban,
            'setpayslip' => $setpayslip,
        ));
    }
