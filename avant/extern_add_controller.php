<?php
$form = $this->get('app.manager.form')->createWorkflowAddExternForm($company);
        $form->handleRequest($request);

        /* if (null !== $request->request->get('add_extern_workflow')) {
             $data = $form->getData();
             dump($form->isSubmitted());
             dump($form->isValid());
             dump(($form->isSubmitted() && $form->isValid()));
             dump($request->request->get('add_extern_workflow'));
             dump($request->request);
             dump($form->getErrors());
             die;

         }*/
        dump("this is befor isValid");
        if ($form->isSubmitted() && $form->isValid()) {
            //dump('goodJungmin');die;
            $data = $form->getData();
            //dump($data);die;
            $contract = $data['contract'];
            $chekckExternType = $request->request->get('add_extern_workflow')['choiceoption'];
            //this is for setting fakeinformation by id.
            $manager = $this->getDoctrine()->getManager();
            $contractType = $manager->getRepository('SageBundle:Reference\ContractType');
            $collectiveAgreement = $manager->getRepository('SageBundle:Reference\CollectiveAgreement');
            $jobType = $manager->getRepository('SageBundle:Reference\JobType');
            $establishment = $manager->getRepository('SageBundle:Reference\Establishment');

            $jobType = $jobType->findOneByCode('388A');
            $establishment = $establishment->find(1)->getReference();
            $collectiveAgreement = $collectiveAgreement->find(1)->getCollectiveAgreement();

            $contract->setCodeInsee($jobType);
            $contract->setEstablishment($establishment);
            $job->setCollectiveAgreement($collectiveAgreement);

            if($chekckExternType=='interim'){
                // $contractForSetType->find(25)->getType();
                // $contract->setType($contractForSetType);
            }else if($chekckExternType=='stage'){
                $contractType->find(20)->getType();
                //$contractForSetType = $data['']->find(20)->getType();
                $contract->setType($contractType);
                $payslip  = $data['payslip'];
            }
            $employee = $data['employee'];
            $people = $data['people'];
            $contract = $data['contract'];
            //$job      = $data['job'];
            //Set Relations
            $employee->setCompany($company);
            $employee->setPeople($people);
            $contract->setEmployee($employee);
            // $job->setEmployee($employee);
            //Persist
            $manager = $this->getDoctrine()->getManager();
            if($chekckExternType=='stage'){
                $payslip->setEmployee($employee);
                $manager->persist($payslip);
            }
            $manager->persist($people);
            $manager->persist($employee);
            $manager->persist($contract);
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
                    //$copyright = "Digi-Paye";
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

        //$setpaymentMode = $request->request->get('add_extern_workflow')['job']['paymentMode'];
        // $setReasonForEntry = $request->request->get('add_extern_workflow')['contract']['reasonForEntry'];
        $setTypeReason = $request->request->get('add_extern_workflow')['contract']['typeReason'];
        //Get Employee  for Dulplicate Function
        $getEmployeeMatricule = $this->getDoctrine()->getRepository('SageBundle:Employee')->findBy(array('company'=>$company));

        return $this->render('AppBundle:Company/Workflow:add_extern.html.twig', array(
            'company' => $company,
            'form' => $form->createView(),
            'getEmployeeMatricule' => $getEmployeeMatricule,
            //'setReasonForEntry' => $setReasonForEntry,
            'setTypeReason' => $setTypeReason,
            //  'setpaymentMode' => $setpaymentMode,
        ));
    }
