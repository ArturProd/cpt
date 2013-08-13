<?php

namespace Cpt\EventBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;



class Location
{
    protected $id;
    
    protected $country_code;
    
    protected $city_name;
    
    protected $city_postal_code;
    
    protected $street;
    
    protected $street_number;
    
    protected $additional_address;
    
    protected $corporate_name;
            
    protected $google_map_url;
    
    public function __construct()
    {
        $this->country_code = "FR";
        $this->city_name = "LYON";
        $this->city_postal_code = "69000";
        $this->street = "";
        $this->street_number = "";
        $this->additional_address = "";
        $this->corporate_name = "";
        $this->google_map_url = "";
    }
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getCountryCode() {
        return $this->country_code;
    }

    public function setCountryCode($country_code) {
        $this->country_code = $country_code;
    }

    public function getCityName() {
        return $this->city_name;
    }

    public function setCityName($city_name) {
        $this->city_name = $city_name;
    }

    public function getCityPostalCode() {
        return $this->city_postal_code;
    }

    public function setCityPostalCode($city_postal_code) {
        $this->city_postal_code = $city_postal_code;
    }

    public function getStreet() {
        return $this->street;
    }

    public function setStreet($street) {
        $this->street = $street;
    }

    public function getStreetNumber() {
        return $this->street_number;
    }

    public function setStreetNumber($street_number) {
        $this->street_number = $street_number;
    }

    public function getAdditionalAddress() {
        return $this->additional_address;
    }

    public function setAdditionalAddress($additional_address) {
        $this->additional_address = $additional_address;
    }

    public function getCorporate_name() {
        return $this->corporate_name;
    }

    public function setCorporateName($corporate_name) {
        $this->corporate_name = $corporate_name;
    }

    public function getGoogleMapUrl() {
        return $this->google_map_url;
    }

    public function setGoogleMapUrl($google_map_url) {
        $this->google_map_url = $google_map_url;
    }


}

?>
