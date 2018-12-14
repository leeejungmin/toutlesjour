<?php
    /**
     * @Route("/add_extern")
     * Secured by SecurityListener
     * @Security("is_granted('DIGIPAYE_COMPANY_EDIT', company)")
     */
    public function addExternAction(Company $company, Request $request)
    {
        //dump('jungmingood');die;
        //dump('jungmingood');die;
          $formInterim = $this->get('app.manager.form')->createWorkflowAddInterimForm($company);
          $formInterim->handleRequest($request);
          $formstagiere = $this->get('app.manager.form')->createWorkflowAddStagiereForm($company);
          $formstagiere->handleRequest($request);
          dump($formInterim);
          dump($formstagiere);
          dump($request->request->get('add_interim_workflow'));
          dump($request->request);
          if (null !== $request->request->get('add_interim_workflow')) {
            $data = $formInterim->getData();
              dump($formInterim);
              dump('this is interim');die;
          } elseif (null !== $request->request->get('add_stagiere_workflow')) {

            $data = $formstagiere->getData();
            $payslip  = $data['payslip'];
              dump($formstagiere);
              dump('this is stage');die;
          }
          if (($formInterim->isSubmitted() && $formInterim->isValid())  ||  ($formstagiere->isSubmitted() && $formstagiere->isValid()) ){
              dump('goodJungmin');die;
              if(isset($formInterim)){
                  $data = $formInterim->getData();
              }
              if(isset($formstagiere)){
                  $data = $formstagiere->getData();
                  $payslip  = $data['payslip'];
              }
              $employee = $data['employee'];
              $people = $data['people'];
              $contract = $data['contract'];
              // $job      = $data['job'];
              //Set Relations
              $employee->setCompany($company);
              $employee->setPeople($people);
              $contract->setEmployee($employee);
              //  $job->setEmployee($employee);
              //Persist
              $manager = $this->getDoctrine()->getManager();
              if(isset($formstagiere)){
                  $payslip->setEmployee($employee);
                  $manager->persist($payslip);
              }
              $manager->persist($people);
              $manager->persist($employee);
              //Set File
              // $file = $this->get('app.manager.file')->addContractToEmployee($employee, $contract->getFile());
              //  if ($file) {
              //      $manager->persist($file);
              //   }
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

          //$setpaymentMode = $request->request->get('add_employee_workflow')['job']['paymentMode'];
          $setReasonForEntry = $request->request->get('add_employee_workflow')['contract']['reasonForEntry'];
          $setTypeReason = $request->request->get('add_employee_workflow')['contract']['typeReason'];
          //Get Employee  for Dulplicate Function
          $getEmployeeMatricule = $this->getDoctrine()->getRepository('SageBundle:Employee')->findBy(array('company'=>$company));

          return $this->render('AppBundle:Company/Workflow:add_extern.html.twig', array(
              'company' => $company,
              'formstagiere' => $formstagiere->createView(),
              'formInterim' => $formInterim->createView(),
              'getEmployeeMatricule' => $getEmployeeMatricule,
              'setReasonForEntry' => $setReasonForEntry,
              'setTypeReason' => $setTypeReason,
              //  'setpaymentMode' => $setpaymentMode,
          ));
      }
