<?php


namespace AppBundle\Services;

use SageBundle\Entity\InseeToken;

class InseeTokenManager
{
    private $consumerKey    = 'GIcsAqDCoSXSs99cGtdl29Twj0Ma';
    private $consumerSecret	= 'Rf0kPAShqgmsFGH2UKgLzxCp8Ega';
    private $tokenUrl	    = 'https://api.insee.fr/token';
    private $establishUrl   = 'https://api.insee.fr/entreprises/sirene/V3/siret?q=siren:632013843 ';
    private $sirenhUrl	    = 'https://api.insee.fr/entreprises/sirene/V3/siren/632013843 ';
    private $urlAuthenticate = array('Authorization: Basic R0ljc0FxRENvU1hTczk5Y0d0ZGwyOVR3ajBNYTpSZjBrUEFTaHFnbXNGR0gyVUtnTHp4Q3A4RWdh');
    private $grant_type = 'client_credentials';
    private $validity_period = '604800';
    private $post_data = array('grant_type=client_credentials&validity_period=604800');
    private $manager;

    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
    }
    public function getTokenResult() {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api.insee.fr/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials&validity_period=604800");
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = 'Authorization: Basic R0ljc0FxRENvU1hTczk5Y0d0ZGwyOVR3ajBNYTpSZjBrUEFTaHFnbXNGR0gyVUtnTHp4Q3A4RWdh';
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $jsonResult = json_decode($result, true);
        return $jsonResult;
    }
    public function treatdatabase(InseeToken $inseeToken){

       $token = $this->getToken()['access_token'];
       $expiration = $this->getToken()['expires_in'];
    }
}
