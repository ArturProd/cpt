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
    
    const PARTICIPATIONLEVEL_UNKNOWN = -1;
    const PARTICIPATIONLEVEL_NOPARTICIPATION = 0;
    const PARTICIPATIONLEVEL_AUTHOR = 2;
    const PARTICIPATIONLEVEL_ORGANIZER  = 4;
    const PARTICIPATIONLEVEL_ATTENDEE  = 8;

     function UpdateCounters();


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
