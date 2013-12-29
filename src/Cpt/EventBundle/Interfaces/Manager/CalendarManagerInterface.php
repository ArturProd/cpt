<?php

namespace Cpt\EventBundle\Interfaces\Manager;

/**
 *
 * @author cyril
 */
interface CalendarManagerInterface {

  public function isFutureEventBeforeMonth($year, $month, &$myevent);
  
  public function isFutureEventAfterMonth($year, $month, &$myevent);
  
  public function GetNextEventDateOrCurrent(\DateTime $current);
    
}
