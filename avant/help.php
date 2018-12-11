$dataOutput = array();

$entityManager = $this->getDoctrine()->getManager();
$repo = $entityManager->getRepository(Collaborator::class);

 $firstname = $_POSt['firstname'];
 $lastname = $_POSt['lastname'];
 $email= $_POSt['email']; 
 $position = $_POSt['position'];



 $dataOutput["firstname"] = $firstname;
 $dataOutput["lastname"] = $lastname;
 $dataOutput["email"] = $email;
 $dataOutput["position"] = $position;

 $repo->setFirstname($firstname)
         ->setLastname($lastname)
         ->setEmail($email);
         ->setPosition($position);

$entityManager->persist($repo);
$entityManager->flush();
 return $response;
