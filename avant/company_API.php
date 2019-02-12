/**
    * @Route("/new_company")
    */
   public function newCompanyAction(Request $request)
   {
       $company = new Company();
       $companyData = array();
       $form = $this->createForm(AddCompanyWorkflowType::class, array(
           'siren' => null,
           'company' => $company
       ));
       $form->handleRequest($request);
       if ($form->get('sync_api')->isClicked())
       {
           $data = $form->getData();
           $siren = $data['siren'];
           $companyData = $this->get('app.siren_api')->findCompanyBySiren($siren);
           dump($companyData);die;
           if ($companyData) {
               //TODO: fill Data from API
               $company->setSiren($siren);
               $company->setName($companyData['name']);
               $company->setSiret($companyData['siret']);
               $company->setApe($companyData['ape']);
               $idccs = $this->get('app.siren_api')->getIdccByApe($companyData['ape']);
               //dump($idccs);
               $form = $this->createForm(AddCompanyWorkflowType::class, array(
                   'siren' => $siren,
                   'company' => $company
               ), array(
                   'preferred_idccs' => $idccs
               ));
           } else {
               $form = $this->createForm(AddCompanyWorkflowType::class, array(
                   'siren' => $siren,
                   'company' => $company
               ));
               $form->handleRequest($request);
               $form->get('siren')->addError(new FormError('SIREN Unknown'));
           }
       } else if ($form->isSubmitted() && $form->isValid()) {
           $manager = $this->getDoctrine()->getManager();
           $manager->persist($company);
           $manager->flush();
           //TODO: Translation
           //Add closure period
           $data = $form->getData();
           $closureStartMonth = \DateTime::createFromFormat('Y-m', $data['closureStartMonth']);
           if ($closureStartMonth) {
               $closure = $this->get('app.manager.closure')
                   ->initClosure($company, $closureStartMonth->format('F Y'));
               $manager->persist($closure);
               $manager->flush();
           }

           $this->addFlash(
               'success',
               'Company Created'
           );
           return $this->redirectToRoute('app_company_default_index', array('company' => $company->getId()));
       }

       return $this->render('AppBundle:Backend:Workflow/new_company.html.twig', array(
           'form' => $form->createView(),
           'companyData' => $companyData,
       ));
   }
