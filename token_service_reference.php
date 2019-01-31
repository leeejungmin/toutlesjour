<?php

namespace AppBundle\Services;

use SageBundle\Entity\Employee;

class Holidays
{
    private $holidays;
    private $holidaysZoning;

    public function __construct($holidays, $holidaysZoning)
    {
        $this->holidays = $holidays;
        $this->holidaysZoning = $holidaysZoning;
    }

    protected function getZoneByZip($zip) {
        $departement = mb_substr($zip,0,2);

        foreach($this->holidaysZoning as $zone => $depList) {
            if(in_array($departement, $depList)) {
                return $zone;
            }
        }
    }

    public function getEmployeeHolidays(Employee $employee) {
        $zip = $employee->getPeople()->getAddressZipcode();
        if ($zip &&  $zone = $this->getZoneByZip($zip)) {
            return isset($this->holidays[$zone]) ? $this->holidays[$zone] : array();
        } else {
            return array();
        }
    }
}
