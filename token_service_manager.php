<?php


namespace AppBundle\Services;

use SageBundle\Entity\InseeToken;

class InseeTokenManager
{
    private $token;
    private $expiration;
    private $consumerKey    = 'GIcsAqDCoSXSs99cGtdl29Twj0Ma';
    private $consumerSecret	= 'Rf0kPAShqgmsFGH2UKgLzxCp8Ega';
    private $tokenUrl	    = 'https://api.insee.fr/token';
    private $establishUrl   = 'https://api.insee.fr/entreprises/sirene/V3/siret?q=siren:632013843 ';
    private $sirenhUrl	    = 'https://api.insee.fr/entreprises/sirene/V3/siren/632013843 ';
    private $urlAuthenticate = 'Basic R0ljc0FxRENvU1hTczk5Y0d0ZGwyOVR3ajBNYTpSZjBrUEFTaHFnbXNGR0gyVUtnTHp4Q3A4RWdh';
    private $grant_type = 'client_credentials';
    private $validity_period = '604800';

    public function __construct($token, $expiration)
    {
        $this->token = $token;
        $this->expiration = $expiration;
        $this->$consumerKey = $consumerKey;
        $this->$consumerSecret = $$consumerSecret;
        $this->$tokenUrl = $$tokenUrl;
        $this->$establishUrl = $$establishUrl;
        $this->$sirenhUrl = $$sirenhUrl;
        $this->$urlAuthenticate = $$urlAuthenticate;
        $this->$grant_type = $$grant_type;
        $this->$validity_period = $$validity_period;
    }
    protected function getToken() {

    }
    protected function generateToken(){

        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_POST, 0);
        curl_setopt($curlHandle, CURLOPT_FAILONERROR, true);
        curl_setopt($curlHandle, CURLOPT_URL, $tokenUrl);
        curl_setopt($curlHandle, urlAuthenticate, $urlAuthenticate);
        curl_setopt($curlHandle, grant_typed, $grant_type);
        curl_setopt($curlHandle, validity_period, $userAgent);
        $time_start= microtime(true);
        $response = curl_exec($curlHandle);
        $time_end = microtime(true);
        $execution_time = round(($time_end - $time_start)*1000,0);
    }
}
