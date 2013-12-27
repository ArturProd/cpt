<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Cpt\EventBundle\Interfaces\Entity;

/**
 *
 * @author cyril
 */
interface LocationInterface {
     function setCountryCode($countryCode);
     function getCountryCode();
     function setCityName($cityName);
     function getCityName();
     function setCityPostalCode($cityPostalCode);
     function getCityPostalCode();
     function setStreet($street);
     function getStreet();
     function setStreetNumber($streetNumber);
     function getStreetNumber();
     function setAdditionalAddress($additionalAddress);
     function getAdditionalAddress();
     function setCorporateName($corporateName);
     function getCorporateName();
     function setLocationShowMap($locationShowMap);
     function getLocationShowMap();
     function setLocationLong($locationLong);
     function getLocationLong();
     function setLocationLat($locationLat);
     function getLocationLat();
}

?>
