<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cpt\EventBundle\Interfaces\Entity;

use CalendR\Event\EventInterface as CalendREventInterface;
use Cpt\PublicationBundle\Interfaces\Entity\PublicationInterface as PublicationInterface;

use Cpt\EventBundle\Interfaces\Entity\LocationInterface as LocationInterface;

/**
 *
 * @author cyril
 */
interface EventInterface extends CalendREventInterface, PublicationInterface, LocationInterface{
    
     function addRegistration(\Cpt\EventBundle\Entity\Registration $registrations);
     function removeRegistration(\Cpt\EventBundle\Entity\Registration $registrations);
     function getRegistrations();
     function setRegistrations($registrations);
     function setMaxnumattendees($maxnumattendees);
     function getMaxnumattendees();
     function setCountQueuedAttendees($countQueuedAttendees);
     function getCountQueuedAttendees();
     function setCountTotalAttendees($countTotalAttendees);
     function getCountTotalAttendees();
     
     function setRestricted($restricted);
     function getRestricted();
     function setApproved($approved);
     function getApproved();
 
     function setCptEvent($cptEvent);
     function getCptEvent();

     function setQueue($queue);
     function getQueue();
     function setRegistrationAllowed($registrationAllowed);
     function getRegistrationAllowed();
}

?>
