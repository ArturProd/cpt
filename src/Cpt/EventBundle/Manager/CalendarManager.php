<?php

namespace Cpt\EventBundle\Manager;

use Cpt\EventBundle\Manager\BaseManager as BaseManager;
use Cpt\EventBundle\Interfaces\Entity\EventInterface as EventInterface;

class CalendarManager extends BaseManager
{
  public function isFutureEventBeforeMonth($year, $month, $options=Array())
  {
      $monthdate = new \DateTime();
      $monthdate->setDate($year, $month, 1);
      $currentdate = new \DateTime();
      
      if ($monthdate<$currentdate){
          return false;
      }
      
     $qb = $this->getEventRepository()
        ->createQueryBuilder('e')
            ->Select('e.id')
            ->Where('(e.begin < :monthdate) OR (e.end < :monthdate)') // The event begins or end before current month
            ->AndWhere('(e.end > :currentdatetime) OR (e.begin > :currentdatetime)') // The event ends or begin after current datetime (it is a future event)
            ->setParameter('monthdate', $monthdate)
            ->setParameter('currentdatetime', $currentdate)
            ->setMaxResults( 1 );
     
        if (array_key_exists('country_code', $options)){
            $qb->AndWhere('e.country_code = :country_code')
                ->setParameter('country_code', $options['country_code']);
        }
        
        $eventid = $qb->getQuery()
        ->getArrayResult();

      return (!empty($eventid));

  }
  
  public function isMyFutureEventBeforeMonth($year, $month, $options=Array())
  {
      if (!$this->getPermissionManager()->isLoggedIn()) {
              return false;
      }
      
      $user = $this->getUser();
      
      $monthdate = new \DateTime();
      $monthdate->setDate($year, $month, 1);
      $monthdate->setTime(0, 0, 0);
      $currentdate = new \DateTime();
      
      if ($monthdate<$currentdate){
          return false;
      }
      
     $qb = $this->getEventRepository()
        ->createQueryBuilder('e')
            ->Select('e.id')
            ->innerJoin('e.registrations', 'r')
            ->Where('(e.begin < :monthdate) OR (e.end < :monthdate)') // The event begins or end before current month
            ->AndWhere('(e.end > :currentdatetime) OR (e.begin > :currentdatetime)') // The event ends or begin after current datetime (it is a future event)
            ->setParameter('monthdate', $monthdate)
            ->setParameter('currentdatetime', $currentdate)
            ->AndWhere('(IDENTITY(e.author) = :userid) OR (IDENTITY(r.user) = :userid)') // it is my event
            ->setParameter('monthdate', $monthdate)
            ->setParameter('currentdatetime', $currentdate)
            ->setParameter('userid', $user->getId())
            ->setMaxResults( 1 );
     
        if (array_key_exists('country_code', $options)){
            $qb->AndWhere('e.country_code = :country_code')
                ->setParameter('country_code', $options['country_code']);
        }
        
        $eventid = $qb->getQuery()
        ->getArrayResult();

      return (!empty($eventid));

  }

  public function isFutureEventAfterMonth($year, $month, $options=Array())
  {
      $monthdate = new \DateTime();
      $monthdate->setDate($year, $month+1, 1); // Adding 1 to the provided month
      $currentdate = new \DateTime();
    
      
      $qb = $this->getEventRepository()
        ->createQueryBuilder('e')
            ->Select('e.id')
            ->Where('(e.end >= :monthdate) OR (e.begin >= :monthdate)') // The event ends or begin after current month (remember that event.end can be null!!)
            ->AndWhere('(e.end > :currentdatetime) OR (e.begin > :currentdatetime)') // The event ends or begin after current datetime (it is a future event)
            ->setParameter('monthdate', $monthdate)
            ->setParameter('currentdatetime', $currentdate)
            ->setMaxResults( 1 );
      
        if (array_key_exists('country_code', $options)){
            $qb->AndWhere('e.country_code = :country_code')
                ->setParameter('country_code', $options['country_code']);
        }
        
        $eventid = $qb->getQuery()
          ->getArrayResult();

      return (!empty($eventid));
  }
  
  public function isMyFutureEventAfterMonth($year, $month, $options=Array())
  {
      if (!$this->getPermissionManager()->isLoggedIn()) {
              return false;
      }
      
      $user = $this->getUser();
          
      $monthdate = new \DateTime();
      $monthdate->setDate($year, $month+1, 1); // Adding 1 to the provided month      
      $monthdate->setTime(0, 0, 0);
      $currentdate = new \DateTime();
      
      $qb = $this->getEventRepository()
        ->createQueryBuilder('e')
            ->Select('e.id')
            ->innerJoin('e.registrations', 'r')
            ->Where('(e.end >= :monthdate) OR (e.begin >= :monthdate)') // The event ends or begin after current month (remember that event.end can be null!!)
            ->AndWhere('(e.end > :currentdatetime) OR (e.begin > :currentdatetime)') // The event ends or begin after current datetime (it is a future event)
            ->AndWhere('(IDENTITY(e.author) = :userid) OR (IDENTITY(r.user) = :userid)') // it is my event
            ->setParameter('monthdate', $monthdate)
            ->setParameter('currentdatetime', $currentdate)
            ->setParameter('userid', $user->getId())
            ->setMaxResults( 1 );

        if (array_key_exists('country_code', $options)){
            $qb->AndWhere('e.country_code = :country_code')
                ->setParameter('country_code', $options['country_code']);
        }

        $eventid = $qb->getQuery()
          ->getArrayResult();

        return (!empty($eventid));
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
 
 
 /**
  * Returns the next date where an event is happening, or current date otherwise.
  * 
  * @param \DateTime $current
  * @return \DateTime
  */
  public function GetNextEventDateOrCurrent(\DateTime $current, $options=Array())
    {
          $qb = $this->getEventRepository()
            ->createQueryBuilder('e')
                ->Where('e.end >= :from')
                ->setMaxResults(1)
                ->orderBy('e.end', 'ASC')
                ->setParameter('from', $current);
          
            if (array_key_exists('country_code', $options)){
                $qb->AndWhere('e.country_code = :country_code')
                ->setParameter('country_code', $options['country_code']);
            }
        
            $event = $qb->getQuery()
            ->getOneOrNullResult();
          
          if ($event){
              if ($event->getBegin()<=$current){
                return $current;              
              } else {
                return $event->getBegin();
              }
          } else{
              return $current;
          }
    }
}