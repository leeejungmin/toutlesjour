<?php


namespace AppBundle\Services;

use SageBundle\Entity\InseeToken;
use \Doctrine\ORM\EntityManager;
use SageBundle\Repository\Company\EstablishmentRepository;

class InseeTokenManager
{
    private $manager;
    private $establishmentRepo;

    public function __construct(EntityManager $manager,EstablishmentRepository $establishmentRepo)
    {
        $this->manager = $manager;
        $this->establishmentRepo = $establishmentRepo;
    }
    protected function getCurl()
    {
        return curl_init();
    }

    public function generateToken() {
        $curlgenerate = $this->getCurl();

        curl_setopt($curlgenerate, CURLOPT_URL, 'https://api.insee.fr/token');
        curl_setopt($curlgenerate, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlgenerate, CURLOPT_POSTFIELDS, "grant_type=client_credentials&validity_period=604800");
        curl_setopt($curlgenerate, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = 'Authorization: Basic R0ljc0FxRENvU1hTczk5Y0d0ZGwyOVR3ajBNYTpSZjBrUEFTaHFnbXNGR0gyVUtnTHp4Q3A4RWdh';
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($curlgenerate, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($curlgenerate);
        if (curl_errno($curlgenerate)) {
            $result['error'] = 'Error:' . curl_error($curlgenerate);
        }
        $jsonResult = json_decode($result, true);
        return $jsonResult;
    }


    public function getToken(InseeToken $inseeToken){
        $currentdate = (new \DateTime())->format('Y-m-d H:i:s');
        $inseerespo= $this->manager->getRepository('SageBundle:InseeToken')->findOneBy(
            array(), array('id' => 'DESC')
        );
        if (!$inseerespo) {
            $inseerespo = new InseeToken();
            $inseerespo->setExpiration(null);
        }
        //create token
        if($inseerespo->getExpiration() == null || date_format($inseerespo->getExpiration(),"Y-m-d H:i:s") < $currentdate){
            //call function for getting token
            $gettokenjson = $this->generateToken();
            $token = $gettokenjson['access_token'];
            $expirationin = $gettokenjson['expires_in'];
            $generationdate = new \DateTime();
            $currentdate = new \DateTime();
            $modifiedtime = '+'.strval($expirationin).' second';
            $generationdate->modify($modifiedtime);
            $generationdate->modify('-5 minute');
            $inseeToken->setToken($token);
            $inseeToken->setExpiration($generationdate);
            $inseeToken->setGeneratedAt($currentdate);
            $entityManager = $this->manager;
            $entityManager->persist($inseeToken);
            $entityManager->flush();
            return $gettokenjson;
        }else{
            //if it is ok, get token from database
            $fortokenarray = array('access_token' => $inseerespo->getToken() );
            return $fortokenarray;
        }
    }
    public function findEstablishmentsBySiren($companySiren, $apiurl, $apitoken){
        $curlgenerate = $this->getCurl();

        curl_setopt($curlgenerate, CURLOPT_URL,$apiurl.'?q=siren:'.$companySiren);
        curl_setopt($curlgenerate, CURLOPT_RETURNTRANSFER, 1);

        $headers = array();
        $headers[] = 'Authorization: Bearer '.$apitoken;
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($curlgenerate, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($curlgenerate);
        if (curl_errno($curlgenerate)) {
            echo 'Error:' . curl_error($curlgenerate);
        }
        $httpCode = curl_getinfo($curlgenerate, CURLINFO_HTTP_CODE);
        $jsonResult = json_decode($result, true);
        //for making array
        $jsonestablishments = $jsonResult['etablissements'];
        $establishments = array();
        foreach ($jsonestablishments as &$establishment) {
            //71.12B -> 7112B
            $find = array('.');
            $replace = array("");
            $arr = array($establishment['uniteLegale']['activitePrincipaleUniteLegale']);
            $activity = str_replace($find,$replace,$arr);
            $activity = implode("",$activity);
            $address1 = $establishment['adresseEtablissement']['numeroVoieEtablissement'].' '.$establishment['adresseEtablissement']['typeVoieEtablissement'].' '.$establishment['adresseEtablissement']['libelleVoieEtablissement'];
            $establishment['name'] = $establishment['uniteLegale']['denominationUniteLegale'];
            //$establishment['altName'] = $establishment['']
            $establishment['siret'] = $establishment['siret'];
            $establishment['addressLine1'] = $address1;
            //$establishment['addressLine2'] = null;
            $establishment['addressZipcode'] = $establishment['adresseEtablissement']['codePostalEtablissement'];
            $establishment['addressCity'] = '';
            $establishment['addressPostOffice'] = $establishment['adresseEtablissement']['codeCommuneEtablissement'];
            //$establishment['activity'] = null;
            $establishment['codeApe'] = $activity;
            $establishments[$establishment['siret']] = $establishment;
        }
        return $establishments;

    }

    public function getEstablishmentsLastUpdateBySirets($sirets)
    {
        $updates = $this->establishmentRepo->getLastUpdateBySiret($sirets);
        $results = array();
        foreach ($updates as $update) {
            $results[$update['siret']] = $update['updatedAt'];
        }
        return $results;
    }
}
