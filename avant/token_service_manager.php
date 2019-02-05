<?php


namespace AppBundle\Services;

use SageBundle\Entity\InseeToken;
use \Doctrine\ORM\EntityManager;

class InseeTokenManager
{
    private $manager;
    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
    }

    public function generateToken() {
        $curlgenerate = curl_init();

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
            echo 'Error:' . curl_error($curlgenerate);
        }
        $httpCode = curl_getinfo($curlgenerate, CURLINFO_HTTP_CODE);
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
        if(date_format($inseerespo->getExpiration(),"Y-m-d H:i:s") < $currentdate || $inseerespo->getExpiration() == null ){
            //call function for getting token
            $gettokenjson = $this->generateToken();
            $token = $gettokenjson['access_token'];
            $expirationin = $gettokenjson['expires_in'];
            $generationdate = new \DateTime();
            $modifiedtime = '+'.strval($expirationin).' second';
            $generationdate->modify($modifiedtime);
            $generationdate->modify('-5 minute');
            $inseeToken->setToken($token);
            $inseeToken->setExpiration(date_format($generationdate,"Y-m-d H:i:s"));
            $inseeToken->setGeneratedAt($generationdate);
            return $gettokenjson;
      }else{
            //if it is ok, get token from database
            $fortokenarray = array('access_token' => $inseerespo->getToken() );
            return $fortokenarray;
        }
    }
}
