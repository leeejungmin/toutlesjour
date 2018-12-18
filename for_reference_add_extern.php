<?php
$form = $this->get('app.manager.form')->createWorkflowAddEmployeeForm($company);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $employee = $data['employee'];
            $people = $data['people'];
            $contract = $data['contract'];
            $job      = $data['job'];
            $payslip  = $data['payslip'];
            //Set Relations
            $employee->setCompany($company);
            $employee->setPeople($people);
            $contract->setEmployee($employee);
            $job->setEmployee($employee);
            $payslip->setEmployee($employee);

            //Persist
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($people);
            $manager->persist($employee);
            //Set File
            $file = $this->get('app.manager.file')->addContractToEmployee($employee, $contract->getFile());
            if ($file) {
                $manager->persist($file);
            }
            $manager->persist($contract);
            $manager->persist($job);
            $manager->persist($payslip);
            $manager->flush();

            $this->addFlash('success', 'flash.workflow.new_employee.success');

            //Create Account
            $account = $data['account'];
            if ($account->getEnableOnboarding()) {
                //Block the creation if the address is no-reply digi-paye or mon-cabinet
                if(strcmp(strtolower($account->getEmail()),"no-reply@digi-paye.com")!=0 && strcmp(strtolower($account->getEmail()),"noreply@digi-paye.com")!=0 && strcmp(strtolower($account->getEmail()),"no-reply@mon-cabinet.com")!=0 && strcmp(strtolower($account->getEmail()),"noreply@mon-cabinet.com")!=0 ){
                    //Automatically create and send invitation if not exists
                    $access = $this->get('app.manager.employee')
                        ->addEmployeeAccess($this->getUser(), $employee, $account->getEmail());
                    if (!$access) {
                        $this->addFlash('danger', 'flash.employee.account.account_created.error');
                    } else {
                        $manager->persist($access);
                        $manager->flush();

                        $this->get('app.manager.user')->sendInvitation($access->getUser(), $access);

                        $this->addFlash('success', 'flash.employee.account.account_created.success');
                    }
                }else{
                    $this->addFlash('danger', 'flash.employee.account.email.noreply');
                }

            }

            $userType = $this->get('app.user_type_manager')->getUserType();
            //TRUE=Employee <=> FALSE=White Brand DRH or ROLE_PAY_ADMIN
            if($userType) {
                $disEvent = new EmployeeCreateEvent($employee, $contract);
                $this->get('event_dispatcher')->dispatch(AppBundleEvents::NEW_EMPLOYEE, $disEvent);
            }else{// FALSE= {only for ROLE_PAY_ADMIN}
                // Notification for PayAdmin
                // Boarding_Notification
                $boardingConfigNotificationAdmin = $manager->getRepository("SageBundle:Config\Notification\Module\Payadmin")
                    ->findOneBy(array(
                        'enabled'=> '1',
                        'id'=>'2'//Entrees
                    ));

                if($boardingConfigNotificationAdmin){
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

            return $this->redirectToRoute('app_company_employee_edit', array(
                'company' => $company->getId(),
                'id' => $employee->getId()
            ));
        }

        $DuplicateEmployee = $this->createForm(DuplicateEmployee::class, null, array());
        $DuplicateEmployee->handleRequest($request);

        $setpaymentMode = $request->request->get('add_employee_workflow')['job']['paymentMode'];
        $setReasonForEntry = $request->request->get('add_employee_workflow')['contract']['reasonForEntry'];
        $setTypeReason = $request->request->get('add_employee_workflow')['contract']['typeReason'];
