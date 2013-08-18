<?php

namespace Cpt\EventBundle\Entity;

use Cpt\EventBundle\Entity\Event as Event;

class CalendarDay{
    
    protected $event = null;
    protected $date = null;
    
    public function __construct(int $year,int $month,int $day) {
        $this->date = new DateTime;
        
            $this->date->setDate($year, $month, $day);
    }
    
    public function setEvent(Event $event)
    {
        if (!$event)
            throw new \InvalidArgumentException("Cannot set a null event in a CalendarDay");
        
        $begindate = clone $event->getBegindatetime();
        $enddate = clone $event->getEnddatetime();
        $begindate->setTime(0,0);
        $enddate->setTime(0,0);
        
        if ($begindate>$this->date)
            throw new \LogicException("The event you are setting starts after the date of this CalendarDay");

        if ($enddate<$this->date)
            throw new \LogicException("The event you are setting ends before the date of this CalendarDay");
        
        $this->event = $event;
    }
    
    public function hasEvent()
    {
        return $this->event != null;
    }
    
    public function getEvent()
    {
        return $this->event;
    }
    
    public function isInMonth(int $month)
    {
        
    }
    
}
?>
