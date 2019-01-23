/**
     * @Route("/{id}/files")
     * @Security("is_granted('DIGIPAYE_EMPLOYEE_EDIT', employee)")
     */
    public function filesAction(Company $company, Employee $employee, Request $request)
    {
        $manager = $this->getDoctrine()->getManager();

        //Create Form
        $employeeFile = new File();
        $form = $this->createForm(TypableFileType::Class, $employeeFile);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $employeeFile->setEmployee($employee);
            $manager->persist($employeeFile);
            $manager->flush();

            $this->addFlash('success', 'flash.employee_file.upload.success');

            //Get the Actual User Type (Connected)
            $userType = $this->get('app.user_type_manager')->getUserType();
            //TRUE=Employee or Normal DRH <=> FALSE=White Brand DRH or ROLE_PAY_ADMIN
            if($userType) {
                $disEvent = new FileCreateEvent($employeeFile);
                $this->get('event_dispatcher')->dispatch(AppBundleEvents::NEW_FILE, $disEvent);
            }

            return $this->redirectToRoute('app_company_employee_files', array(
                'company' => $company->getId(),
                'id' => $employee->getId()
            ));
        }

        $fileRequest = new FileRequest();
        $fileRequest->setEmployee($employee);
        $formFileRequest = $this->createForm(FileRequestGlobalType::class, $fileRequest);
        $formFileRequest->handleRequest($request);
        if ($formFileRequest->isSubmitted() && $formFileRequest->isValid()) {
            $fileRequest->setStatus(FileRequestManager::STATUS_PENDING);
            $manager->persist($fileRequest);
            $manager->flush();

            $this->addFlash('success', 'flash.employee_file.upload_request.success');

            //Get the Actual User Type (Connected)
            $userType = $this->get('app.user_type_manager')->getUserType();
            //TRUE=Employee <=> FALSE=White Brand DRH or ROLE_PAY_ADMIN
            if($userType) {
                $disEvent = new FileRequestCreateEvent($fileRequest);
                $this->get('event_dispatcher')->dispatch(AppBundleEvents::NEW_FILE_REQUEST, $disEvent);
            }

            return $this->redirectToRoute('app_company_employee_files', array(
                'company' => $company->getId(),
                'id' => $employee->getId()
            ));
        }

        $fileRequests = null;
        $fileRequestForms = null;
        //No need to compute if no user to receive requests
        if ($employee->getUserAccess()) {

            //Request Files
            $fileRequests = $manager->getRepository('SageBundle:Employee\FileRequest')
                ->getByEmployee($employee);

            $fileRequestForms = array();
            foreach ($fileRequests as $fileRequest) {
                //$fileRequestForm = $this->createForm(FileRequestUploadType::class, $fileRequest);
                //Use Named builder to build a unique form per file request
                $fileRequestForm = $this->get('form.factory')
                    ->createNamedBuilder('file_request_action_' . $fileRequest->getType() . '_' . $fileRequest->getId(),
                        FileRequestActionChoiceType::class, $fileRequest)
                    ->getForm();
                //Handle Request
                $fileRequestForm->handleRequest($request);
                if ($fileRequestForm->isSubmitted() && $fileRequestForm->isValid()) {
                    switch ($fileRequestForm->get('fileAction')->getData()) {
                        case FileRequestManager::ACTION_APPROVE:
                            $this->get('app.manager.file_request')->approve($fileRequest);

                            $this->addFlash('success', 'flash.employee_file.upload_request_approve.success');
                            break;
                        case FileRequestManager::ACTION_REJECT:
                            $this->get('app.manager.file_request')->reject($fileRequest);

                            $this->addFlash('success', 'flash.employee_file.upload_request_reject.success');
                            break;
                        case FileRequestManager::ACTION_CANCEL:
                            $this->get('app.manager.file_request')->cancel($fileRequest);

                            $this->addFlash('success', 'flash.employee_file.upload_request_cancel.success');
                            break;
                        default:
                            break;
                    }

                    return $this->redirectToRoute('app_company_employee_files', array(
                        'company' => $company->getId(),
                        'id' => $employee->getId()
                    ));
                }

                //Create View
                $fileRequestForms[$fileRequest->getId()] = $fileRequestForm->createView();
            }

        }

        //Forms to update validity date
        $files = $manager->getRepository('SageBundle:Employee\File')
            ->getFilesByEmployee($employee);
        $fileForms = array();
        foreach ($files as $file) {
            $fileForm = $this->get('form.factory')
                ->createNamedBuilder('file_' . $file->getId(),
                    FileUpdateValidityType::class, $file)
                ->getForm();
            //Handle Request
            $fileForm->handleRequest($request);
            if ($fileForm->isSubmitted() && $fileForm->isValid()) {
                $manager->persist($file);
                $manager->flush($file);

                $this->addFlash('success', 'flash.employee_file.validity_update.success');

                return $this->redirectToRoute('app_company_employee_files', array(
                    'company' => $company->getId(),
                    'id' => $employee->getId()
                ));
            }

            $fileForms[$file->getId()] = $fileForm->createView();
        }

        $variableTitles = $this->get('app.manager.variable')->getTitlesByCompany($company);

        return $this->render('AppBundle:Company/Employee:files.html.twig', array(
            'company' => $company,
            'employee' => $employee,
            'form' => $form->createView(),
            'formFileRequest' => $formFileRequest->createView(),
            'files' => $files,
            'fileForms' => $fileForms,
            'fileRequests' => $fileRequests,
            'fileRequestActionForms' => $fileRequestForms,
            'variableTitles' => $variableTitles
        ));
    }
