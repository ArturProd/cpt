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
interface RegistrationInterface {
     function __construct($user = null, $event = null, $numparticipant = 1, $organizer = false);

     function getUserId();
     function setEvent(EventInterface $event);
     function getEvent();
     function setUser(\Application\Sonata\UserBundle\Entity\User $user = null);
     function getUser();
     function getId();
     function setCreatedAt($createdAt);
     function getCreatedAt();
     function setNumparticipant($numparticipant);
     function getNumparticipant();
     function setNumqueuedparticipant($numqueuedparticipant);
     function getNumqueuedparticipant();
     function setUpdatedAt($updatedAt);
     function getUpdatedAt();
     function setOrganizer($organizer);
     function getOrganizer();
}

?>
