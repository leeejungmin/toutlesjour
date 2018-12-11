<?php
     * @Route("s/new")
     * @Security("is_granted('ACCESS_MANAGER', company)")
     */
    public function newAction(Company $company,Request $request)
    {
        dump('jungmin');
        //die;
        $userAccessManager = $this->get('app.manager.user_access');
        $userAccess = $userAccessManager->createAccess($this->getUser());
        $userAccess->setCompany($company);
        //setAccessType pas sur
        $userAccess->setAccessType(UserAccessVoter::ACCESS_MANAGER);

        $form = $this->createForm(CompanyUserAccessThreeType::class, $userAccess, array(
            'validation_groups' => array('NewAccess', 'CompanyAccess')
        ));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();

            $email = $form->get('email')->getData();
            //Get User or Create it and send invite
            $userManager = $this->get('app.manager.user');
            $user = $userManager->getUserByEmail($email, true,
                $form->get('lastName')->getData(),
                $form->get('firstName')->getData()
            );

            //Detect if the user are Whitebrand
            $userWhiteBrand = $this->get('app.user_type_manager')->getUserWhiteBrandID();
            //TRUE=DRH WhiteBrand
            if($userWhiteBrand) {
                $whiteBrand = $this->getDoctrine()->getRepository('AppBundle:WhiteLabel')->find($userWhiteBrand);
                $userAccess->setWhiteLabel($whiteBrand);
                $userAccess->setWhiteBrandLevel("CLIENT MARQUE BLANCHE");
            }

            //Check Valid
            $userAccess->setUser($user);
            $userAccess->setCompany($company);
            $validator = $this->get('validator');
            $errors = $validator->validate($userAccess, null, array('NewAccess', 'CompanyAccess'));

            if (count($errors)) {
                foreach ($errors as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            } else {
                $manager->persist($userAccess);
                $manager->flush();

                $userManager->sendInvitation($user, $userAccess);

                $this->addFlash('success', 'flash.user_access.created.success');

                return $this->redirectToRoute('app_company_employee_team_permission', array(
                   'company' => $company->getId()
                ));
            }
        }

        return $this->render('AppBundle:Company/Employee/Team:make_manager.html.twig', array(
            'form' => $form->createView(),
            'company' => $company
        ));
    }
}
