<?php
public function save(Event $event, $source = null)
    {
        $new = is_null($event->getId());

        //Only for the emailing
        $isApproved = false;

        //Validity Date if MAX(endDate, starDate of current closure)
        if (!$event->getValidityDate())
            $event->setValidityDate($this->getValidityDate($event->getEmployee()->getCompany(), $event->getEndDate()));
        $file = $event->getFile();
        if ($file) {
            if ($file->getFile()) {
                $file->setEmployee($event->getEmployee());
                $file->setType(FileManager::TYPE_EVENT_PROOF);
                $this->manager->persist($file);
                //$disEvent = new FileCreateEvent($employeeFile);
                //$this->dispatcher->dispatch(AppBundleEvents::NEW_FILE, $disEvent);
            } else {
                //TODO: Check update
                $event->setFile(null);
            }
        }
        //TRUE=Employee or Normal DRH <=> FALSE=White Brand DRH or ROLE_PAY_ADMIN
        $userType = $this->connectedUserType->getUserType();

        //If the user create the event from the Salary side the event must be PENNDING
        //Because sometimes if the salary use employee and manager the event is auto-validated
        if(strcmp($source,"FrontOffice")==0){
            $event->setStatus(self::PENDING);
        }else{
            //The Employee of the white brand can now use the front of the application like a normal employee
            //So we have to detect if the user are a employee to not auto approve the event
            //Else we must check if he's a DRH, White-Brand or ROLE_PAY_ADMIN
            if($this->connectedUserType->isRoleEmployee()){
                //Employee
                $event->setStatus(self::PENDING);
            }else{
                //The user is not a employee
                //TRUE=Employee or Normal DRH <=> FALSE=White Brand DRH or ROLE_PAY_ADMIN
                if(!$userType){
                    $event->setStatus(self::APPROVED);
                    $isApproved=true;
                }
            }
        }

        //Add source when the user create event from the BackOffice
        if($source){
            $event->setSource($source);
        }else{
            $event->setSource("BackOffice");
        }
        //Save Event
        $this->manager->persist($event);
        $this->manager->flush();

        //If the event was created by a DRH or WhiteBrand do not send a notification !
        // = $this->get('app.user_type_manager')->getUserType();
        //TRUE=Employee or Normal DRH <=> FALSE=White Brand DRH or ROLE_PAY_ADMIN
        if($userType){
            //Send event
            //Skip event related to hours events. 0300 Heures Travaills is not blocked
            if ($new && !in_array($event->getType()->getCode(), $this->ignoreDispatchForEventCodes)) {
                $disEvent = new EventUpdateEvent($event);
                $this->dispatcher->dispatch(AppBundleEvents::NEW_EVENT, $disEvent);
            }
        }else {
            if($isApproved){
                //Send e-mail only when the event is approved
                // check if event is one of the config_notif_payadmin_event_code
                if (in_array($event->getType()->getCode(), $this->codeEvent)) {


                    // Notification for Admin
                    $eventsConfigNotificationAdmin = $this->manager->getRepository("SageBundle:Config\Notification\Module\Payadmin")
                        ->findOneBy(array(
                            "title" => 'Evénements',
                            'enabled' => '1'
                        ));

                    if ($eventsConfigNotificationAdmin) {
                        // Get Admin @
                        $emails = $this->manager->getRepository('AppBundle:User')->getEmailsByRole('ROLE_PAY_ADMIN');

                        // Title construction
                        $title = $this->translator->trans('mail.event_title_employee', array(
                            '%company%' => $event->getEmployee()->getCompany()->getName(),
                            '%serial%' => $event->getEmployee()->getSerial(),
                            '%name%' => $event->getEmployee()->getPeople()->getFirstName() . ' ' . $event->getEmployee()->getPeople()->getLastName() . ' ' . $event->getType()->getTitle()
                        ));

                        $from = null;
                        $copyright = "Digi-Paye";

                        $urlParams = array(
                            'company' => $event->getEmployee()->getCompany()->getId(),
                            'id' => $event->getEmployee()->getId()
                        );

                        // Get mailer service and executing action
                        $mailer = $this->mailer;
                        $mailer->sendMail(
                            $emails,
                            $title,
                            'AppBundle:Mail:event_update.html.twig',
                            array(
                                'title' => $title,
                                'event' => $event,
                                'url_approve' => $this->router->generate('app_company_calendar_approve', $urlParams, UrlGeneratorInterface::ABSOLUTE_URL),
                                'url_reject' => $this->router->generate('app_company_calendar_reject', $urlParams, UrlGeneratorInterface::ABSOLUTE_URL),
                                'copyright' => $copyright
                            ),
                            $event->getFile(),
                            $from
                        );
                    }
                }
            }
        }
    }
