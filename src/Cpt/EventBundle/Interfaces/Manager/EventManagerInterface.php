<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Cpt\EventBundle\Interfaces\Manager;

use CalendR\Event\Provider\ProviderInterface as CalendRProviderInterface;
use Cpt\EventBundle\Interfaces\Entity\EventInterface as EventInterface;
use FOS\UserBundle\Model\UserInterface as UserInterface;
use Cpt\EventBundle\Interfaces\Entity\RegistrationInterface as RegistrationInterface;

/**
 *
 * @author cyril
 */
interface EventManagerInterface extends CalendRProviderInterface{
     function createEvent(UserInterface $author, $enabled=true, $approved=true, $restricted=false);

     function SaveEvent(EventInterface $event);
    
     function CopyEvent(EventInterface $event);

     function getEventById($id);
     
}
