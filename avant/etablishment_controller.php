<?
/**
     * @Route("s/synchronize")
     * @Security("is_granted('DIGIPAYE_COMPANY_VIEW', company)")
     */
    public function synchronizeAction(Company $company, Request $request)
    {
        //get token
        $InseeToken = new InseeToken();
        $apitoken = $this->get('app.insee_token')->getToken($InseeToken)[ "access_token" ];
        $apiurl= $this->container->getParameter('apiSirene')['urlsiret'];

        $establishments = $this->get('app.insee_token')->findEstablishmentsBySiren($company->getSiren(), $apiurl, $apitoken );
        dump($establishments);die;
        $establishmentsLastUpdate = $this->get('app.insee_token')
            ->getEstablishmentsLastUpdateBySirets(array_keys($establishments));
        $form = $this->createForm(EstablishmentSyncType::class, array(
            'establishments' => $establishments,
            'lastUpdates' => $establishmentsLastUpdate
        ));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $manager = $this->getDoctrine()->getManager();
            $data = $form->getData();
            $accessor = PropertyAccess::createPropertyAccessor();
            $validator = $this->get('validator');
            $successCount = 0;
            foreach ($data as $key => $value) {
                if (substr($key, 0, 5) != 'sync_' || !$value)
                    continue;
                $siret = substr($key, -14);
                $nic = substr($siret, -5, 4);
                $establishment = $manager->getRepository('SageBundle:Company\Establishment')
                    ->findOneBy(array(
                        'siret' => $siret,
                        'company' => $company
                    ));
                if (!$establishment) {
                    $establishment = new Establishment();
                    $establishment->setCompany($company);
                    //Use NIC as Sage Code since both are on 5 chars
                    $establishment->setCode(intval($nic));
                }
                foreach ($data['establishments'][$siret] as $property => $value) {
                    if ($accessor->isWritable($establishment, $property))
                        $accessor->setValue($establishment, $property, $value);
                }
                $errors = $validator->validate($establishment);
                if (count($errors) == 0) {
                    $manager->persist($establishment);
                    $successCount++;
                } else {
                    foreach ($errors as $error) {
                        $this->addFlash('danger',
                            'SIRET=' . $siret . ' :' . $error->getMessage() . ' : ' . $error->getPropertyPath()
                        );
                    }
                }
            }
            $manager->flush();
            if ($successCount)
                $this->addFlash('success', 'flash.company.establishment.sync.success');

            return $this->redirectToRoute('app_company_establishment_synchronize', array(
                'company' => $company->getId()
            ));
        }

        return $this->render('AppBundle:Company/Establishment:sync.html.twig', array(
            'company' => $company,
            'establishments' => $establishments,
            'form' => $form->createView()
        ));
    }
