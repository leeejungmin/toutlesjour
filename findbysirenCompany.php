public function findCompanyBySiren($siren)
    {
        //http://www.societe.com/societe/carrefour-hypermarches-451321335.html
        $client = $this->getClient();
        $url = sprintf($this->companyurl, 'mycompany', $siren);
        $crawler = $client->request('GET', $url);
        if ($client->getResponse()->getStatus() != 200)
            return false;
        $company = array();
        $crawler->filter('#rensjur > tr')->each(function(Crawler $node, $i) use (&$company) {
            $attribute = $node->filter('td')->first()->text();
            if ($attribute == "SIREN") {
                $company['siren'] = str_replace(' ', '' ,$node->filter('td')->last()->text());
            } else if ($attribute == "Dénomination") {
                $company['name'] = $node->filter('td')->last()->text();
            } else if ($attribute == "SIRET (siege)") {
                $company['siret'] = $node->filter('td')->last()->text();
            }
        });
        $crawler->filter('#rensjurcomplete > tr')->each(function(Crawler $node, $i) use (&$company) {
            $attribute = $node->filter('td')->first()->text();
            if ($attribute == "Code APE (NAF) de l'entreprise") {
                $company['ape'] = $node->filter('td')->last()->text();
            }
        });
        return $company;
    }
