<?php
/**
    * @Route("/Access")
    * @Method({"POST"})
    */
   public function accessAction(Request $request)
   {
       //$request->query->get('xx') => For GET
       //$request->request->get('xx') => For POST
//var_dump($request->request->get('email'));
//var_dump($request->request->get('token'));

       $output = array("email" => "", "token" => "");

       //Get the User ID By Email
       $user = $this->getDoctrine()->getRepository('AppBundle:User')
           ->findBy(array(
               'email' => $request->request->get('email')
           ));

       if($user){
           //dump($user);
           //dump($user[0]);

           $access = $this->getDoctrine()->getRepository('Louis21Bundle:IdentifyServerToken')
               ->findBy(array(
                   'user' => $user[0],
                   'token' => $request->request->get('token'),
                   'enabled' => true
               ));
           if($access){
               $output["email"] = $user[0]->getUserName();
               $output["token"] = $access[0]->getToken();
               //$output["createdAt"] = $access[0]->getCreatedAt();
           }
       }

       $response = new Response();
       $response->setContent(json_encode($output));
       $response->headers->set('Content-Type', 'application/json');
       return $response;
