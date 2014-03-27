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
    
    public function RegisterUserForEvent(EventInterface $event, UserInterface $user, $numparticipants = 1, $organizer = false)
    {
      
        $registration = $this->CreateRegistration($event, $user, $numparticipants, $organizer);
        $this->AddRegistrationAndUpdateQueue($event, $registration);
        
        $this->getEventManager()->SaveEvent($event);
        
        return true;
    }
    
    protected function AddRegistrationAndUpdateQueue(EventInterface $event, RegistrationInterface $p_registration)
    {
        $found = false;
        $old_num_participant = 0;
        $user_id = $p_registration->getUserId();
        
        // Searching for an existing registration for this user to this event
        foreach($event->getRegistrations() as $registration)
        {
            if ($registration->getUserId() == $user_id)
            {
                $old_num_participant = $registration->getNumParticipant();
                $registration->setNumParticipant($p_registration->getNumparticipant());
                $registration->setOrganizer($p_registration->getOrganizer());
                $found = true;
                break;
            }
        }
        
        // If not found, we add the registration and fill the queue
        if (!$found){
            $event->addRegistration($p_registration);
            $this->FillQueue($event, $p_registration->getNumparticipant(),$user_id);
        } else if ($p_registration->getNumparticipant() > $old_num_participant) // There are more participants => we can just fill the rest of the queue
        {
            $this->FillQueue($event, $registration->getNumparticipant() - $old_num_participant ,$registration->getUser()->getId());
        }
        else { // we must shring the queue
            $queue = $event->getQueue();
            $user_id_index = -1;
            
            for($i=0; $i<count($queue);++$i)
            {
                if ($queue[$i] == $user_id)
                {
                    $user_id_index = $i;
                    break;
                }
            }
            
            if ($user_id_index == -1){ // Would be very bad => means that the queue is broken                
                $this->get('logger')->error('RegistrationManager: Could not find user id ' + $user_id + ' in the queue of event ' + $event->getId() + ' but registration exists for this user and this event.');
                throw new Exception("Internal Error");
            }
            
            $shrinked_user_queue = array_fill ( 0 , $p_registration->getNumparticipant() , $user_id );
            array_splice ( $queue , $user_id_index , $old_num_participant , $shrinked_user_queue );
            $event->setQueue($queue);
        }
        
        $event->UpdateCounters();
        
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
    
    public function RemoveRegistration(EventInterface $event, $user)
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

    public function CreateRegistration(EventInterface $event, $user, $numparticipants, $organizer)
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
        
        $this->AddRegistrationAndUpdateQueue($event,$registration);

    }
    // <editor-fold defaultstate="collapsed" desc="Protected">


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
 
    
    protected function FillQueue(EventInterface $event, $num_item, $item_value)
    {
        $old_queue = $event->getQueue();
        $added_queue = array_fill ( 0 , $num_item , $item_value );
        $new_queue = array_merge($old_queue, $added_queue);
        $event->setQueue($new_queue);
    }
 
    
}
