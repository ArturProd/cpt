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
       $myeventbefore = EventInterface::MYEVENT_UNKNOWN;
       $isbefore = $this->getCalendarManager()->isFutureEventBeforeMonth($year, $month, &$myeventbefore);
       
       $myeventafter = EventInterface::MYEVENT_UNKNOWN;
       $isafter = $this->getCalendarManager()->isFutureEventAfterMonth($year, $month, &$myeventafter);
       
       $data = Array(
           'before' => getMyEventType($isbefore, $myeventbefore),
           'after' => getMyEventType($isafter, $myeventafter),
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
        
        $isfutureventbeforemonth = $this->getCalendarManager()->isFutureEventBeforeMonth($year,$month);
        $isfutureventaftermonth = $this->getCalendarManager()->isFutureEventAfterMonth($year,$month);

                
        return $this->render('CptEventBundle:Event:calendar.html.twig', array(
                    'currentdate' => $showdate,
                    'previousmonthdate' => $previousmonthdate,
                    'nextmonthdate' => $nextmonthdate,
                    'isfutureventbeforemonth' => $isfutureventbeforemonth,
                    'isfutureventaftermonth' => $isfutureventaftermonth,
        ));
    }

}
