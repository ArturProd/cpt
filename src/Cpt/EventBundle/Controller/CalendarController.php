<?php



namespace Cpt\EventBundle\Controller;

use Cpt\MainBundle\Controller\BaseController as BaseController;
use Cpt\EventBundle\Interfaces\Entity\EventInterface as EventInterface;

/**
 * Description of CalendarController
 *
 * @author cyril
 */
class CalendarController extends BaseController {
    
   public function getEventSelectorArrowTypeAction($year, $month)
   {
       $is_eventbefore = $this->getCalendarManager()->isFutureEventBeforeMonth($year, $month);
       $is_eventafter = $this->getCalendarManager()->isFutureEventAfterMonth($year, $month);
       $is_myeventbefore = false;
       $is_myeventafter = false;
       
       if ($is_eventbefore){
        $is_myeventbefore = $this->getCalendarManager()->isMyFutureEventBeforeMonth($year, $month);       
       }
       
       if ($is_eventafter){
        $is_myeventafter = $this->getCalendarManager()->isMyFutureEventAfterMonth($year, $month);
       }
       
       
       $data = Array(
           'is_eventbefore' => $is_eventbefore,
           'is_eventafter' => $is_eventafter,
           'is_myeventbefore' => $is_myeventbefore,
           'is_myeventafter' => $is_myeventafter,
           'year' => $year,  
           'month' => $month,
       );
       
       return $this->CreateJsonOkResponse($data);
   }
   
   protected function getMyEventType($isevent, $myevent)
   {
       if (!$isevent){
           return 'no';
       }
       
       if ($myevent == EventInterface::MYEVENT_YES){
           return 'myevent';
       }
       
       return 'yes';
   }
    
   public function viewCalendarAction($year = 0, $month = 0) {
        $showdate = null;
        
        if (($year==0) || ($month==0)) {
            $showdate = $this->getCalendarManager()->GetNextEventDateOrCurrent(new \Datetime);
        } else {
            if ($month > 12){
                $this->RestrictResourceNotFound();
            }
            $showdate = new \Datetime();
            $showdate->setDate ( $year , $month, 1  );
        }

        $previousmonthdate = clone $showdate;
        $previousmonthdate->sub(new \DateInterval("P1M"));
        $nextmonthdate = clone $showdate;
        $nextmonthdate->add(new \DateInterval("P1M"));
        
                
        return $this->render('CptEventBundle:Event:calendar.html.twig', array(
                    'currentdate' => $showdate,
                    'previousmonthdate' => $previousmonthdate,
                    'nextmonthdate' => $nextmonthdate,
        ));
    }

}
