<?php

namespace Cpt\EventBundle\Manager;

use Cpt\EventBundle\Manager\BaseManager as BaseManager;
use Cpt\EventBundle\Interfaces\Entity\EventInterface as EventInterface;

class CalendarManager extends BaseManager
{
  public function isFutureEventBeforeMonth($year, $month, &$myevent)
  {
      $monthdate = new \DateTime();
      $monthdate->setDate($year, $month, 1);
      $currentdate = new \DateTime();
      
     $event = $this->getEventRepository()
        ->createQueryBuilder('e')
            ->Select('e')
            ->Where('(e.begin < :monthdate) OR (e.end < :monthdate)') // The event begins or end before current month
            ->AndWhere('(e.end > :currentdatetime) OR (e.begin > :currentdatetime)') // The event ends or begin after current datetime (it is a future event)
            ->setParameter('monthdate', $monthdate)
            ->setParameter('currentdatetime', $currentdate)
            ->setMaxResults( 1 )
        ->getQuery()
        ->getOneOrNullResult();

     if (!$event){                                     // No event found
          $myevent = EventInterface::MYEVENT_UNKNOWN;
          return false;
      }

      $myevent = $this->isMyEvent($event) ? EventInterface::MYEVENT_YES : EventInterface::MYEVENT_NO;
      return true;
  }

  public function isFutureEventAfterMonth($year, $month, &$myevent)
  {
      $monthdate = new \DateTime();
      $monthdate->setDate($year, $month+1, 1); // Adding 1 to the provided month
      $currentdate = new \DateTime();
      
      $event = $this->getEventRepository()
        ->createQueryBuilder('e')
            ->Select('e')
            ->Where('(e.end >= :monthdate) OR (e.begin >= :monthdate)') // The event ends or begin after current month (remember that event.end can be null!!)
            ->AndWhere('(e.end > :currentdatetime) OR (e.begin > :currentdatetime)') // The event ends or begin after current datetime (it is a future event)
            ->setParameter('monthdate', $monthdate)
            ->setParameter('currentdatetime', $currentdate)
            ->setMaxResults( 1 )
          ->getQuery()
          ->getOneOrNullResult();

      if (!$event){                                     // No event found
          $myevent = EventInterface::MYEVENT_UNKNOWN;
          return false;
      }

      $myevent = $this->isMyEvent($event) ? EventInterface::MYEVENT_YES : EventInterface::MYEVENT_NO;
      return true;
  }
  
  public function isMyEvent(EventInterface $event)
  {
          if (!$event){
              throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
          }
          
          if (!$this->getPermissionManager()->isLoggedIn()) // Not logged in
          {
              return false;
          }
          
          $user = $this->getUser();
          if ($event->getAuthor()->getId() == $user->getId()) // The event author is current user
          {
            return true;
          }
          
          $countregistration = $this->getRegistrationRepository()->createQueryBuilder('r')
                 ->select('count(r.id)')
                 ->Where('r.user = :user_id AND r.event = :event_id')
                 ->setMaxResults( 1 )
                 ->setParameter('user_id', $user->getId())
                 ->setParameter('event_id', $event->getId())
                 ->getQuery()->getOneOrNullResult();
          
        return ( $countregistration > 0 );
 }
 
  
  public function GetNextEventDateOrCurrent(\DateTime $current)
    {
          $event = $this->getEventRepository()
            ->createQueryBuilder('e')
                ->Where('e.begin >= :from')
                ->setMaxResults(1)
                ->setParameter('from', $current)
            ->getQuery()
            ->getOneOrNullResult();
          
          if ($event)
              return $event->getBegin();
          else
              return $current;
    }
}