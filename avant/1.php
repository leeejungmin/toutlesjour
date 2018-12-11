<?php
public function indexAction(Company $company, Request $request)
   {
       //make managerform
       $formanger = $this->managerForm($company);

       $team = new Team();
       $team->setCompany($company);

       $form = $this->createForm(TeamType::class, $team, array(
           'company' => $company
       ));
       $formanger->handleRequest($request);
       $form->handleRequest($request);

       if (($form->isSubmitted() && $form->isValid()) {
         // Handle $form2
         $team->setCompany($company);
         $manager = $this->getDoctrine()->getManager();
         $manager->persist($team);
         $manager->flush();
         $this->addFlash('success', 'flash.employee.team.created.success');

         return $this->redirectToRoute('app_company_employee_team_index', array(
             'company' => $company->getId()
         ));
    } else if ($formanger->isSubmitted() && $formanger->isValid()) {
        // Handle $form2
        $manager = $this->getDoctrine()->getManager();

        $email = $formanger->get('email')->getData();
        //Get User or Create it and send invite
        $userManager = $this->get('app.manager.user');
        $user = $userManager->getUserByEmail($email, true,
            $formanger->get('lastName')->getData(),
            $formanger->get('firstName')->getData()
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
                $formanger->addError(new FormError($error->getMessage()));
            }
        } else {
            $manager->persist($userAccess);
            $manager->flush();

            $userManager->sendInvitation($user, $userAccess);

            $this->addFlash('success', 'flash.user_access.created.success');
// return first page with what variable???
            return $this->redirectToRoute('AppBundle:Company/Employee/Team:index.html.twig', array(
              'company' => $company,
              'teams' => $teams,
              'teamStatuses' => $teamStatuses,
              'form' => $form->createView(),
              'formanager' => $formanger->createView(),
              'deleteForms' => $deleteForms
            ));
        }
    }

       $teams = $this->getDoctrine()->getManager()->getRepository('SageBundle:Employee\Team')
           ->getByCompany($company);

       $teamStatuses = array();
       $deleteForms = array();
       $teamManager = $this->get('app.manager.team');
       foreach ($teams as $entity) {
           $t = $teamManager->getTeammateWithStatus($entity);
           $teamStatuses[$entity->getId()] = $t;
           //return;
           $deleteForms[$entity->getId()] = $this->createDeleteForm(
               $company->getId(),
               $entity->getId())
               ->createView();
       }
       //Get External Managers Status
       //Get Manager Account and Access and Contract
       //Get Employee Account, Access and Contract

       return $this->render('AppBundle:Company/Employee/Team:index.html.twig', array(
           'company' => $company,
           'teams' => $teams,
           'teamStatuses' => $teamStatuses,
           'form' => $form->createView(),
           'formanager' => $formanger->createView(),
           'deleteForms' => $deleteForms
       ));
   }
