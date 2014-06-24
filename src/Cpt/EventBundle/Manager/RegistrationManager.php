<?php

namespace Cpt\EventBundle\Manager;

use Cpt\EventBundle\Manager\BaseManager as BaseManager;
use Cpt\EventBundle\Interfaces\Manager\RegistrationManagerInterface as RegistrationManagerInterface;

use Cpt\EventBundle\Interfaces\Entity\RegistrationInterface as RegistrationInterface;
use Cpt\EventBundle\Interfaces\Entity\EventInterface as EventInterface;
use FOS\UserBundle\Model\UserInterface as UserInterface;

use Cpt\EventBundle\Entity\Registration as Registration;


/**
 * Description of RegistrationManager
 *
 * @author cyril
 */
class RegistrationManager extends BaseManager implements RegistrationManagerInterface {
    
    
    // <editor-fold defaultstate="collapsed" desc="Public">

    /**
     * 
     * Register a user for an event, or update the registration
     * For new registrations, organizer is set to false
     * For updated registrations, organizer is not changed
     * 
     * @param \Cpt\EventBundle\Interfaces\Entity\EventInterface $event
     * @param \FOS\UserBundle\Model\UserInterface $user
     * @param type $numparticipants
     * @return boolean
     */
    public function RegisterUserForEvent(EventInterface $event, UserInterface $user, $numparticipants = 1, $animator = false, $and_save_event = true)
    {
      
        $dummy_registration = $this->CreateRegistration($event, $user, $numparticipants, $animator);
        $registration = $this->UpdateRegistration($event, $dummy_registration);
        
        if ($and_save_event){
            $this->getEventManager()->SaveEvent($event);
        }
        
        return $registration;
    }
    
    public function CancelRegistration(EventInterface $event, UserInterface $user){
        // The author cannot cancel his registration
        if ($event->getAuthor()->getId() == $user->getId()){
            throw new \Exception("The author of the event cannot cancel his registration.");
        }
        
        // Check that there will still be an animator         
        $found_other_animator = false;
        foreach($event->getRegistrations() as $registration)
        {
            // There is at least one registration for another user which is set an organizer
            if (($registration->getUser()->getId() != $user->getId()) && $registration->getOrganizer()){
                $found_other_animator = true;
                break;
            }
        }
        
        if (!$found_other_animator){
            throw new \Exception("Cannot cancel this registration: there must be at least one organizer for the event");
        }
        
        // Find the registration
        $registration = $this->getRegistration($event, $user);
        
        if (!$registration){
            throw new \Exception("Cannot cancel this registration: registration not found");
        }
        
        // Remove the registration from the event
        $event->removeRegistration($registration);
                
        
        // Update event queue
        $queue = $event->getQueue();
        $new_queue = array_diff($queue, [$user->getId()]);
        $event->setQueue($new_queue);
        
        // Save event
        $this->getEventManager()->SaveEvent($event);

    }
     
    public function getRegistration(EventInterface $event, UserInterface $user)
    {
        return $this->getRegistrationRepository()
            ->createQueryBuilder('u')
                ->Where('u.user = :user_id AND u.event = :event_id')
                ->setParameter('user_id', $user->getId())
                ->setParameter('event_id', $event->getId())
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    public function getAttendees(EventInterface $event, $organizers_only = false)
    {
        /*$organizers = Array();
        foreach ($event->getRegistrations() as $registration)
            if ($registration->getOrganizer())    
                $organizers[] = $registration->getUser();
       
        return $organizers;*/
        
        return $this->getUserRepository()
            ->createQueryBuilder('u')
                ->leftJoin("u.registrations", "r")
                ->Where('r.event = :event_id AND r.organizer=:organizers_only')
                ->setParameter('organizers_only', $organizers_only)
                ->setParameter('event_id', $event->getId())
            ->getQuery()
            ->getResult();
    }
    
    public function UpdateParticipationLevel(EventInterface $event, UserInterface $user)
    {
        if ((!$event) || (!$user)){
            return;
        } 
    
        $userregistration = $this->getRegistrationRepository()
            ->createQueryBuilder('r')
                ->select('r')
                ->Where('u.user = :user_id AND u.event = :event_id')
                ->setParameter('user_id', $user->getId())
                ->setParameter('event_id', $event->getId())
            ->getQuery()
            ->getArrayResult();
        
        
        $is_author = ($event->getAuthor()->getId() == $user->getId());
        $is_attendee = !empty($userregistration);
        $is_organizer = false;
 
        $event->computeParticipationLevel($is_author, $is_attendee, $is_organizer);
        
    }

    public function setOrganizers(EventInterface $event, $user_array)
    {
       foreach ($user_array as $user)
       {
           $registration = $this->getRegistration($event, $user);

           if (!$registration) {
               $registration = new Registration($user, $event, true);
           }
           else {
                $registration->getOrganizer(true);
           }
           
           $this->em->persist($registration);

           $this->em->flush();
       }

    }    
        
    
    /**
     * Indicates if the creator of the envent is also an organizer
     * @param \Cpt\EventBundle\Entity\Event $event
     */
    public function isCreatorAlsoAnimator(EventInterface $event)
    {
        $organizers = $this->getAttendees($event, true);
        
        foreach ($organizers as $user) {
            if ($user->getId() == $event->getAuthorId()) {
                return true;
            }
        }
            
        return false;
    }
    
    public function isAuthor(EventInterface $event, UserInterface $user)
    {
        return ($event->getAuthor()->getId() == $user->getId());
    }
    
    public function isAuthorSingleOrganizer(EventInterface $event)
    {
        $organizers = $this->getRegistrationManager()->getAttendees($event, true);

        if ((count($organizers) == 1) && ($organizers[0]->getId() == $event->getAuthor()->getId())) {
            return true;
        }
        
        return false;
    }
    
    public function AddDefaultRegistration(EventInterface $event, $registred_user = null)
    {
        if (!$registred_user){
            $registred_user = $event->getAuthor ();
        }
        
        // By default, creator is also an organizer
        $registration = new Registration($registred_user, $event, 1, true);
        
        $this->UpdateRegistration($event,$registration);

    }

    public function DeleteAllRegistrations(EventInterface $event)
    {
            return $this->getRegistrationRepository()
            ->createQueryBuilder('u')
                ->delete()
                ->Where('u.event = :event_id')
                ->setParameter('event_id', $event->getId())
            ->getQuery()
            ->execute();    
    }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Protected">
    protected function CreateRegistration(EventInterface $event, $user, $numparticipants, $organizer)
    {
        if (!is_integer($numparticipants)) {
            throw new \Symfony\Component\Security\Core\Exception\InvalidArgumentException("num participants must be an integer");
        }
        
        if (!is_bool($organizer)) {
            throw new \Symfony\Component\Security\Core\Exception\InvalidArgumentException("organizer must be boolean");
        }
        
        $registration = new Registration($user, $event, $numparticipants, $organizer);
  
        return $registration; 
    }
    
    protected function FillQueue(EventInterface $event, $num_item, $item_value)
    {
        $old_queue = $event->getQueue();
        $added_queue = array_fill ( 0 , $num_item , $item_value );
        $new_queue = array_merge($old_queue, $added_queue);
        $event->setQueue($new_queue);
    }
      
    protected function UpdateRegistration(EventInterface $event, RegistrationInterface $p_registration)
    {
        $old_num_participant = 0;
        $user_id = $p_registration->getUserId();
        $found_registration = null;
        
        // Searching for an existing registration for this user to this event
        foreach($event->getRegistrations() as $registration)
        {
            if ($registration->getUserId() == $user_id)
            {   // If the registration exists, organizer is not modified
                $old_num_participant = $registration->getNumParticipant();
                $found_registration = $registration;                
                break;
            }
        }
        
        // If not found, we add the registration as provided and fill the queue (new registrations are necessarily at the end)
        if (!$found_registration){
            $event->addRegistration($p_registration);
            $this->FillQueue($event, $p_registration->getNumparticipant(),$user_id);
            
            //$event->UpdateCounters();
            
            return $p_registration;
        } 
        //else if ($p_registration->getNumparticipant() == $old_num_participant){
            // Same num of participant, nothing to do
        //    return $found_registration;
        else {

            $found_registration->setNumparticipant($p_registration->getNumparticipant());
            $found_registration->setOrganizer($p_registration->getOrganizer());
            // Update the event queue
            $this->ModifyNumberOfAttendeesForUser($event, $user_id, $p_registration->getNumparticipant(), $old_num_participant );
            
            //$event->UpdateCounters();
            
            return $found_registration;
        }
    }
        
    /**
     * 
     * Updates the event.queue for a given event and a user_id, changing the number of attendees
     * 
     * @param type $event The event having the queue to be updated
     * @param type $user_id The user id to consider
     * @param type $new_num_attendees The new number of attendees
     * @param type $old_num_attendees The old number of attendees
     */
    protected function ModifyNumberOfAttendeesForUser(EventInterface $event, $user_id, $new_num_attendees, $old_num_attendees)
    {
            $old_queue = $event->getQueue();
            $new_queue = Array();
            $count_user_id = 0;
            
            // Shrink the queue
            if ($new_num_attendees < $old_num_attendees)
            {
                // Going through the old queue
                for ($i = 0; $i<count($old_queue);++$i)
                {
                    // If it is an attendee from a different user, push it to new queue
                    if ($old_queue[$i] != $user_id)
                    {
                        array_push($new_queue,$old_queue[$i]);
                    // Else, push it to new queue only to the point tota number does not exceed the new count
                    } else {
                        $count_user_id++;

                        if ($count_user_id <= $new_num_attendees)
                        {
                            array_push($new_queue,$old_queue[$i]);
                        } 
                    }
                }
                
                // Assign the new queue
                $event->setQueue($new_queue);
   
            } else if ($new_num_attendees > $old_num_attendees) {
                $count_to_be_added = $new_num_attendees - $old_num_attendees;
                $this->FillQueue($event, $count_to_be_added ,$user_id);
            }
    }
    
    protected function RemoveRegistration(EventInterface $event, $user)
    {
        return $this->getRegistrationRepository()
            ->createQueryBuilder('u')
                ->delete()
                ->Where('u.user = :user_id AND u.event = :event_id')
                ->setParameter('user_id', $user->getId())
                ->setParameter('event_id', $event->getId())
            ->getQuery()
            ->execute();
    }
    // </editor-fold>
}
