<?php

namespace Cpt\EventBundle\Manager;

use Doctrine\ORM\EntityManager;
use Cpt\EventBundle\Manager\BaseManager;
use Cpt\EventBundle\Entity\Event as Event;
use Cpt\EventBundle\Entity\Registration as Registration;

class EventManager
{
    protected $em;

    
    /**
     * @param \Doctrine\ORM\EntityManager $em
     * @param string                      $class
     */
    public function __construct(EntityManager $em)
    {
        $this->em    = $em;
    }
    
    // <editor-fold defaultstate="collapsed" desc="Public: Event related">

    public function createEvent($author)
    {
        $event = new Event();
        
         if (!$author)
             throw new \InvalidArgumentException("Cannot create an event without creator (null)");

        $event->setAuthor($author);
        
        // By default, creator is also an organizer
        $registration = new Registration($author, $event, 1, true);
        
        $this->AddRegistration($event,$registration);

        return $event;
    }
    
    public function SaveEvent(Event $event)
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
    
    public function getEventById($id)
    {
        return $this->getEventRepository()->find($id);
    }
    
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Public: Registration related">
    
    public function AddRegistration(Event $event, Registration $registration)
    {
        $event->addRegistration($registration);
        $this->FillQueue($event, $registration->getNumparticipant(),$registration->getUser()->getId());
        $this->ValidateBusinessRules($event);
        $this->UpdateCounters($event);
    }
    
    
    public function getRegistration(Event $event, $user)
    {
        return $this->getRegistrationRepository()
            ->createQueryBuilder('u')
                ->Where('u.user = :user_id AND u.event = :event_id')
                ->setParameter('user_id', $user->getId())
                ->setParameter('event_id', $event->getId())
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    public function RemoveRegistration(Event $event, $user)
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

    public function CreateRegistration(Event $event, $user, $numparticipants, $organizer)
    {
        $registration = new Registration($user, $event, $numparticipants, $organizer);
  
        return $registration; 
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Public: User related">
    public function getOrganizers(Event $event)
    {
        $organizers = Array();
        foreach ($event->getRegistrations() as $registration)
            if ($registration->getOrganizer())    
                $organizers[] = $registration->getUser();
       
        return $organizers;
    }
    

    public function setOrganizers(Event $event, $user_array)
    {
       foreach ($user_array as $user)
       {
           $registration = $this->getRegistration($event, $user);

           if (!$registration)
               $registration = new Registration($user, $event, true);
           else
                $registration->getOrganizer(true);
           
           $this->em->persist($registration);

           $this->em->flush();
       }

    }    
    // </editor-fold>    
        
    // <editor-fold defaultstate="collapsed" desc="Protected">

    protected function ValidateBusinessRules(Event $event)
    {
        // Checking parameters
        if (($event === null))
            throw new \InvalidArgumentException("Parameters cannot be null in EventManager.ReplaceRegistrationCollection");

        $registrations = $event->getRegistrations();
        $eventqueue = $event->getQueue();                

        // Checking the queue are integers
        foreach($eventqueue as $key => $userid)
            if (!is_integer($eventqueue[$key]))
               throw new \InvalidArgumentException("Queue should be an array of integer");    
            
        // Checking the Queue matches with the Registration num participants
        foreach($registrations as $key => $registration )
        {
            if ($event->getId() != $registration->getEvent()->getId())
                throw new \LogicException("Provided event id does not match with registration id EventManager.ValidateQueue");
            
            if ($registration->getNumparticipant() !== count( array_keys( $eventqueue, $registration->getUser()->getId() )))
                throw new \InvalidArgumentException("Queue does not match with RegistrationList");
            
        }
        
        $this->UpdateCounters($event);

      
    }
    
    protected function FillQueue(Event $event, $num_item, $item_value)
    {
        $old_queue = $event->getQueue();
        $added_queue = array_fill ( 0 , $num_item , $item_value );
        $new_queue = array_merge($old_queue, $added_queue);
        $event->setQueue($new_queue);
    }
    
    
    protected function UpdateCounters(Event $event)
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
            if (($count_queued < $event->getMaxnumattendees()) && $registration->getOrganizer())
                $count_organizers++;
        }
        
        $event->setCountQueuedAttendees($countQueuedAttendees);
           
        // Throwing exception if ther is no organizer in the event
        if ($count_organizers == 0)
            throw new \InvalidArgumentException("There should be at least one organizer in an event.");    
    }

    protected function DeleteAllRegistrations(Event $event)
    {
            return $this->getRegistrationRepository()
            ->createQueryBuilder('u')
                ->delete()
                ->Where('u.event = :event_id')
                ->setParameter('event_id', $event->getId())
            ->getQuery()
            ->execute();    
    }
   
    protected function getEventRepository()
    {
        return $this->em->getRepository('CptEventBundle:Event');
    }
    
    protected function getRegistrationRepository()
    {
        return $this->em->getRepository('CptEventBundle:Registration');
    }
    
        // </editor-fold>
}
?>
