<?php

namespace Cpt\EventBundle\Manager;

use Cpt\EventBundle\Manager\BaseManager as BaseManager;

class CalendarManager extends BaseManager
{
  public function isFutureEventBeforeMonth($year, $month)
  {
      $monthdate = new \DateTime();
      $monthdate->setDate($year, $month, 1);
      $currentdate = new \DateTime();
      
         $countevent = $this->getEventRepository()
            ->createQueryBuilder('e')
                ->Select('COUNT(e.id)')
                ->Where('e.begin < :monthdate') // The event begins before current month
                ->AndWhere('e.end > :currentdatetime') // The event ends after current datetime (it is a future event)
                ->setParameter('monthdate', $monthdate)
                ->setParameter('currentdatetime', $currentdate)
                ->setMaxResults( 1 )
            ->getQuery()
            ->getSingleScalarResult();
         
         return ($countevent == 1);
  }

  public function isFutureEventAfterMonth($year, $month)
  {
      $monthdate = new \DateTime();
      $monthdate->setDate($year, $month, 1);
      $currentdate = new \DateTime();
      
         $countevent = $this->getEventRepository()
            ->createQueryBuilder('e')
                ->Select('COUNT(e.id)')
                ->Where('e.end > :monthdate') // The event ends after current month
                ->AndWhere('e.end > :currentdatetime') // The event ends after current datetime (it is a future event)
                ->setParameter('monthdate', $monthdate)
                ->setParameter('currentdatetime', $currentdate)
                ->setMaxResults( 1 )
            ->getQuery()
            ->getSingleScalarResult();
         
         return ($countevent == 1);
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