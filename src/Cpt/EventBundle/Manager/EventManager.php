<?php

namespace Cpt\EventBundle\Manager;

use Doctrine\ORM\EntityManager;
use Cpt\EventBundle\Manager\BaseManager as BaseManager;
use Cpt\EventBundle\Interfaces\Manager\EventManagerInterface as EventManagerInterface;

use Cpt\EventBundle\Entity\Event as Event;
use Cpt\EventBundle\Entity\Registration as Registration;
use Cpt\EventBundle\Interfaces\Entity\EventInterface as EventInterface;
use Cpt\EventBundle\Interfaces\Entity\RegistrationInterface as RegistrationInterface;
use FOS\UserBundle\Model\UserInterface as UserInterface;

use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;

class EventManager extends BaseManager implements EventManagerInterface
{

    // <editor-fold defaultstate="collapsed" desc="Event ProviderInterface">
    public function getEvents( \DateTime $begin, \DateTime $end, array $options = array())
    {
         $events = $this->getEventRepository()
            ->createQueryBuilder('e')
                ->Where('e.begin >= :from')
                 ->AndWhere('e.begin < :to') // We don't care about event end date: if begining is in a period, then the event is returned
     //           ->AndWhere('e.end < :to') // To should be exclude according to CalendR specifications
                ->setParameter('from', $begin)
                ->setParameter('to', $end)
            ->getQuery()
            ->getResult();
         
         return $events;
    }
    // </editor-fold>

    
    // <editor-fold defaultstate="collapsed" desc="Public: Event related">

    public function createEvent(UserInterface $author, $enabled=true, $approved=true, $restricted=false)
    {
        $event = new Event();
        
         if (!$author){
             throw new \InvalidArgumentException("Cannot create an event without creator (null)");
         }
         
        $this->AddDefaultRegistration($event, $author);
         
        $event->setAuthor($author);
        $event->setEnabled($enabled);
        $event->setRestricted($restricted);
        $event->setApproved($approved);
        
         return $event;
    }
    
    public function SaveEvent(EventInterface $event)
    {
         $this->ValidateBusinessRules($event);
                
        // Deleting all existing registrations, as the new collection will be peristed
         $this->DeleteAllRegistrations($event);

         $this->em->persist($event);

         $this->em->flush();
        try {
         //  

        } catch (\Exception $e) {
            $this->em->getConnection()->rollback();
            $this->em->close();
            throw $e;
        }        
    }
    
    public function CopyEvent(EventInterface $event)
    {
        if (($event) && ($event->getId() != -1))
        {
            $event = clone $event;
            $event->setId(-1);
            $event->setRegistrations(new ArrayCollection());
            $event->setComments(new ArrayCollection());
            $event->setQueue(Array());
            $this->AddDefaultRegistration($event);
        }
   
        return $event;
    }
    
    public function getEventById($id)
    {
        $event = $this->getEventRepository()->find($id);
        
        if (!$event) {
            throw new SymfonyException\NotFoundHttpException("Resource not found.");
        }
        
        return $event;
    }
    
  
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Public: Registration related">
    
    public function isMyEvent(EventInterface $event)
    {
        $this->getPermissionManager()->RestrictAccessToLoggedIn();
        $user = $this->getUser();
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
        $organizers = $this->getAttendees($event, true);

        if ((count($organizers) == 1) && ($organizers[0]->getId() == $event->getAuthor()->getId())) {
            return true;
        }
        
        return false;
    }
     
    public function isAuthor(EventInterface $event, UserInterface $user)
    {
        return ($event->getAuthor()->getId() == $user->getId());
    }
    
    /*public function getRegistration(EventInterface $event, UserInterface $user)
    {
        $registrations = $event->getRegistrations();
        foreach ($registrations as $registration)
            if ($registration->getUser()->getId() == $user->getId())
                return $registration;
            
        return null;
    }*/
    
    /*
    public function isAttendee(EventInterface $event, UserInterface $user)
    {
        $registrations = $event->getRegistrations();
        foreach ($registrations as $registration)
            if ($registration->getUser()->getId() == $user->getId())
                return true;
        
        return false;
    }
    
    public function isOrganizer(EventInterface $event, UserInterface $user)
    {
        $registrations = $event->getRegistrations();
        foreach ($registrations as $registration)
            if (($registration->getOrganizer()) && ($registration->getUser()->getId() == $user->getId()))
                return true;
        
        return false;
    }*/
    
    public function AddRegistration(EventInterface $event, RegistrationInterface $registration)
    {
        $event->addRegistration($registration);
        $this->FillQueue($event, $registration->getNumparticipant(),$registration->getUser()->getId());
        $this->ValidateBusinessRules($event);
        $this->UpdateCounters($event);
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
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Public: User related">
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
    // </editor-fold>    
        
    // <editor-fold defaultstate="collapsed" desc="Protected">

    protected function AddDefaultRegistration(EventInterface $event, $registred_user = null)
    {
        if (!$registred_user){
            $registred_user = $event->getAuthor ();
        }
        
        // By default, creator is also an organizer
        $registration = new Registration($registred_user, $event, 1, true);
        
        $this->AddRegistration($event,$registration);

    }
    
    protected function ValidateBusinessRules(EventInterface $event)
    {
        // Checking parameters
        if (($event === null)){
            throw new \InvalidArgumentException("Parameters cannot be null in EventManager.ReplaceRegistrationCollection");
        }
        
        $registrations = $event->getRegistrations();
        $eventqueue = $event->getQueue();                

        // Checking the queue are integers
        foreach($eventqueue as $key => $userid)
            if (!is_integer($eventqueue[$key])) {
               throw new \InvalidArgumentException("Queue should be an array of integer");    
            }
            
        // Checking the Queue matches with the Registration num participants
        foreach($registrations as $key => $registration )
        {
            if ($event->getId() != $registration->getEvent()->getId()) {
                throw new \LogicException("Provided event id does not match with registration id EventManager.ValidateQueue");
            }
            
            if ($registration->getNumparticipant() !== count( array_keys( $eventqueue, $registration->getUser()->getId() ))) {
                throw new \InvalidArgumentException("Queue does not match with RegistrationList");
            }
        }
        
        $this->UpdateCounters($event);

      
    }
    
    protected function FillQueue(EventInterface $event, $num_item, $item_value)
    {
        $old_queue = $event->getQueue();
        $added_queue = array_fill ( 0 , $num_item , $item_value );
        $new_queue = array_merge($old_queue, $added_queue);
        $event->setQueue($new_queue);
    }
    
    
    protected function UpdateCounters(EventInterface $event)
    {
        $queue = $event->getQueue();
        $countTotalAttendees = count($queue);
        $countQueuedAttendees = 0; 
        
     
        $event->setCountTotalAttendees($countTotalAttendees);
        
        $count_organizers = 0;
        // Update queue counters for each registration
        $waiting_attendees = array_slice ( $queue, $event->getMaxnumattendees());
        foreach ($event->getRegistrations() as $registration)
        {
            $count_queued = count(array_keys($waiting_attendees,$registration->getUser()->getId()));
            $registration->setNumqueuedparticipant($count_queued);
            
            $countQueuedAttendees += $count_queued;
            
            // Checking if a non-queued attendee is the organizer
            if (($count_queued < $event->getMaxnumattendees()) && $registration->getOrganizer()){
                $count_organizers++;
            }
        }
        
        $event->setCountQueuedAttendees($countQueuedAttendees);
           
        // Throwing exception if ther is no organizer in the event
        if ($count_organizers == 0)
            throw new \InvalidArgumentException("There should be at least one organizer in an event.");    
    }

    protected function DeleteAllRegistrations(EventInterface $event)
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
}