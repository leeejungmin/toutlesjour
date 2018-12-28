<?php
/**
     * @Route("/{company}/getClosures")
     * @Method("POST")
     * @Security("has_role('ROLE_PAY_ADMIN')")
     */
    public function companysearchAction(Request $request, Company $company)
    {
        $manager = $this->getDoctrine()->getManager();
       // $q = $request->query->get('q', "");
        //dump($manager->getRepository('SageBundle:Company'));die;
        //$repocompany = $manager->getRepository('SageBundle:Company')->find($request->request->get('company'));
        $closures = $manager->getRepository('SageBundle:Company/Closures')->findBy(
            array('company' => $company)
        );
        $output = "";
        foreach ($closures as $closure){
            $output .= $closure->getPeriodStart()->modify("M Y");
        }

        dump($closures);
        dump($output);
        die;
        //$repocompany = $manager->getRepository('SageBundle:Company')->find(2);
        //$closure = $manager->getRepository('SageBundle:Company\Closure')->getCurrentByCompany($repocompany);

       // $dateStart->modify($dateStart->format('m-Y'));
        //$dateEnd->format('mm-yyyy');

       /* $repoclosure = $manager->getRepository('SageBundle:Company\Closure')->findOneBy(array(
                //'company' => $request->request->get('company'),
                'company' => 50,
            ));;*/
       // $results = $repoclosure->searchByQuery($q);
        $results = $closure;
        $response = new JsonResponse();
        $response->setData(array('data' => $results));

        return $response;
    }
}
