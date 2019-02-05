<?
public function findEstablishmentsBySiren($siren)
    {
        //http://www.societe.com/etablissements/carrefour-hypermarches-451321335.html
        $client = $this->getClient();
        $url = sprintf($this->establishmentsurl, 'mycompany', $siren);
        $crawler = $client->request('GET', $url);
        if ($client->getResponse()->getStatus() != 200)
            return false;
        $establishments = array();
        $crawler->filterXPath("//*[starts-with(@id, 'etab')]")->each(function(Crawler $enode, $i) use (&$establishments) {
            if (preg_match('/etab\d+complete/', $enode->attr('id')))
            {
                $establishment = array();
                $enode->filter('tr')->each(function(Crawler $node, $j) use (&$establishment) {
                    $attribute = $node->filter('td')->first()->text();
                    $value = $node->filter('td')->last();
                    if ($attribute == "Statut") {
                        $establishment['status'] = $value->text();
                    } else if ($attribute == "Dernière date maj") {
                        $establishment['lastUpdate'] = \DateTime::createFromFormat('d-m-Y', $value->text());
                    } else if ($attribute == "Nom") {
                        $establishment['name'] = $value->text();
                    } else if ($attribute == "Complément de nom") {
                        $establishment['altName'] = $value->text();
                    } else if ($attribute == "N° de SIRET") {
                        $establishment['siret'] = $value->filter('a')->text();
                    } else if ($attribute == "Adresse") {
                        $establishment['addressLine1'] = $value->text();
                    } else if ($attribute == "Complément d'adresse") {
                        $establishment['addressLine2'] = $value->text();
                    } else if ($attribute == "Code postal") {
                        $establishment['addressZipcode'] = $value->text();
                    } else if ($attribute == "Ville") {
                        $establishment['addressCity'] = $value->text();
                    } else if ($attribute == "Département de l'unité urbaine") {
                        $establishment['addressPostOffice'] = $value->text();
                    } else if ($attribute == "Code ape (NAF)") {
                        $establishment['codeApe'] = $value->text();
                    } else if ($attribute == "Nature de l'activité") {
                        $establishment['activity'] = $value->text();
                    }
                });
                $establishments[$establishment['siret']] = $establishment;
            }
        });
        return $establishments;
    }
