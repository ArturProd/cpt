<?php

namespace Cpt\EventBundle\Entity;

class Country
{
    protected $country_code;
    protected $country_name;
    
    public function getCountryCode()
    {
        return $this->country_code;
    }
    
    public function setCountryCode($value){
        $this->country_code = $value;
    }
    
    public function getCountryName()
    {
        return $this->country_name;
    }
    
    public function setCountryName($value){
        $this->country_name = $value;
    }
}