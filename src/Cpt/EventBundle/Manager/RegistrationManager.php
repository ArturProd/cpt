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
    
    public function AddRegistration(EventInterface $event, RegistrationInterface $registration)
    {
        $event->addRegistration($registration);
        $this->FillQueue($event, $registration->getNumparticipant(),$registration->getUser()->getId());
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
        
        $this->AddRegistration($event,$registration);

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
