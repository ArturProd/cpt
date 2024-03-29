<?php

namespace Cpt\EventBundle\Interfaces\Manager;

use Cpt\EventBundle\Interfaces\Entity\RegistrationInterface as RegistrationInterface;
use Cpt\EventBundle\Interfaces\Entity\EventInterface as EventInterface;
use FOS\UserBundle\Model\UserInterface as UserInterface;

/**
 *
 * @author cyril
 */
interface RegistrationManagerInterface {
    
     function RegisterUserForEvent(EventInterface $event, UserInterface $user, $numparticipants = 1);

     function isCreatorAlsoAnimator(EventInterface $event);
    
     //function AddRegistrationAndUpdateQueue(EventInterface $event, RegistrationInterface $registration);

     function getRegistration(EventInterface $event, UserInterface $user);
    
     function CancelRegistration(EventInterface $event, UserInterface $user);    
    
     function getAttendees(EventInterface $event, $organizers_only = false);
    
     function setOrganizers(EventInterface $event, $user_array);
     
     function AddDefaultRegistration(EventInterface $event, $registred_user = null);

     function DeleteAllRegistrations(EventInterface $event);
}
