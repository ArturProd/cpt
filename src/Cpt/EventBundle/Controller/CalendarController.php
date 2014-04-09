<?php



namespace Cpt\EventBundle\Controller;

use Cpt\MainBundle\Controller\BaseController as BaseController;
use Cpt\EventBundle\Interfaces\Entity\EventInterface as EventInterface;
use Symfony\Component\HttpFoundation\Request as Request;

/**
 * Description of CalendarController
 *
 * @author cyril
 */
class CalendarController extends BaseController {
    
   public function getEventSelectorArrowTypeAction(Request $request, $year, $month)
   {
        $options = Array();
        if ($request->query->has('country_code')){
            $options['country_code'] = $request->query->get('country_code');
        }
        
       $is_eventbefore = $this->getCalendarManager()->isFutureEventBeforeMonth($year, $month, $options);
       $is_eventafter = $this->getCalendarManager()->isFutureEventAfterMonth($year, $month, $options);
       $is_myeventbefore = false;
       $is_myeventafter = false;
        
       if ($is_eventbefore){
        $is_myeventbefore = $this->getCalendarManager()->isMyFutureEventBeforeMonth($year, $month, $options);       
       }
       
       if ($is_eventafter){
        $is_myeventafter = $this->getCalendarManager()->isMyFutureEventAfterMonth($year, $month, $options);
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
    
   public function viewCalendarAction(Request $request, $year = 0, $month = 0) {
        $showdate = null;
        
        $options = Array();
        $country_code = 'FR';
        if ($request->query->has('country_code')){
            $options['country_code'] = $request->query->get('country_code');
            $country_code = $options['country_code'];
        }
        
        if (($year==0) || ($month==0)) {
            $showdate = $this->getCalendarManager()->GetNextEventDateOrCurrent(new \Datetime, $options);
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
                    'options' => $options
        ));
    }

}
