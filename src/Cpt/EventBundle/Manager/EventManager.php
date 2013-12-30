<?php

namespace Cpt\EventBundle\Manager;

use Doctrine\ORM\EntityManager;
use Cpt\EventBundle\Manager\BaseManager as BaseManager;
use Cpt\EventBundle\Interfaces\Manager\EventManagerInterface as EventManagerInterface;

use Cpt\EventBundle\Entity\Event as Event;
use Cpt\EventBundle\Entity\Registration as Registration;
use Cpt\EventBundle\Interfaces\Entity\EventInterface as EventInterface;
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
         
        $this->getRegistrationManager()->AddDefaultRegistration($event, $author);
         
        $event->setAuthor($author);
        $event->setEnabled($enabled);
        $event->setRestricted($restricted);
        $event->setApproved($approved);
        
         return $event;
    }
    
    public function SaveEvent(EventInterface $event)
    {
         $event->UpdateCounters();
                
        // Deleting all existing registrations, as the new collection will be peristed
         $this->getRegistrationManager()->DeleteAllRegistrations($event);

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
            $this->GetRegistrationManager()->AddDefaultRegistration($event);
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
    

    

   
   

    
        // </editor-fold>
}