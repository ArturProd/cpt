<?php

namespace Cpt\EventBundle\Controller;

use Cpt\MainBundle\Controller\BaseController as BaseController;
use Cpt\EventBundle\Entity as Entity;
use Cpt\EventBundle\Form\Type;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response;
use Ivory\GoogleMap\Events\Event as GMapEvent;
//use Ivory\GoogleMap\Places\Autocomplete;
//use Ivory\GoogleMap\Places\AutocompleteType;
//use Ivory\GoogleMap\Helper\Places\AutocompleteHelper;
use Ivory\GoogleMap\Helper\MapHelper;

class EventController extends BaseController {

    // <editor-fold defaultstate="collapsed" desc="Actions">

    /**
     * Displays the whole Event Section
     * 
     * @return type
     */
    public function indexAction() {
        $currentdate = $this->getCalendarManager()->GetNextEventDateOrCurrent(new \Datetime);
        $update_ajax_delay = $this->container->getParameter("cpt.event.update_ajax_delay");

        
        return $this->render('CptEventBundle:Event:index.html.twig', array(
                    'currentdate' => $currentdate,
                    'update_ajax_delay' => $update_ajax_delay
        ));
    }

    /**
     * Form to create or edit an event
     * 
     * @param type $id
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \InvalidArgumentException
     */
    public function newAction($id = null) {
        $request = $this->getRequest();

        $this->getPermissionManager()->RestrictAccessToLoggedIn();
        $this->getPermissionManager()->RestrictAccessToAjax($request);


        // ****************************************************
        // Checking if new event or if it has to be loaded from db
        $event = null;
        if ((null != $id) && (-1 != $id)) {
            $event = $this->getEventManager()->getEventById($id);

            $this->getPermissionManager()->RestrictAccessToUser($event->getAuthor()->getId());
        } else {
            $author = $this->container->get('security.context')->getToken()->getUser();
            $event = $this->getEventManager()->createEvent($author);
        }


        // ****************************************************
        // Creating the form
        $form = $this->get('form.factory')->createNamed('event', 'cpt_edit_event', $event, Array('attr' => Array('id' => 'eventform')));


        // ****************************************************        
        // POSTing form: save the event
        if ($request->isMethod('POST')) {

            $form->bind($request);


            // Get the organizers and event queue
            try {
                // If it is a copy action, copy the event
                if (($event->getId() != -1) && ( $request->request->get('copy_field') == 1)){
                    $event = $this->getEventManager()->CopyEvent($event);
                }
                else { // If not, process the event queue
                    $registrationlist_json_array = json_decode($request->get('registration_list_json'));
                    $eventqueue_json_array = json_decode($request->get('event_queue_json'));

                    if ((!$registrationlist_json_array ) || (!$eventqueue_json_array)){
                        throw new \InvalidArgumentException("decoded json is null");
                    }
                    
                    $event->setQueue($eventqueue_json_array);
                    $this->SetJsonRegistrationCollection($event, $registrationlist_json_array);
                }
            } catch (Exception $e) {
                return new Response("Paramètre incorrects", 400);
            }

            if ($form->isValid()) {

                $this->getEventManager()->SaveEvent($event);
                return $this->CreateJsonOkResponse(null);
            }

           return $this->CreateJsonFailedResponse($this->GetEventEditView($event, $form));
        }
        // ****************************************************        
        // Display page
        return new Response($this->GetEventEditView($event, $form));
    }

    /*

    /**
     * Compares the last update timestamp of an event with the provided timestamp.
     * Returns a json object with value "true" if the event was updated in db since the provided timestamp, "false" otherwise
     *
     * @param type $id
     * @param type $unixtimestamp
     * @return type
     */
    public function wasEventUpdatedAction($id, $unixtimestamp) {
        $this->getPermissionManager()->RestrictAccessToLoggedIn();
        $this->getPermissionManager()->RestrictAccessToAjax();

        $event = $this->getEventManager()->getEventById($id);
        $this->RestrictResourceNotFound($event);

        $this->getPermissionManager()->RestrictAccessToUser($event->getAuthor()->getId());

        $lastupdatedate = $event->getUpdatedAt()->getTimestamp();

        return $this->CreateJsonOkResponse($lastupdatedate > $unixtimestamp);
    }

    /**
     * Downloads the list of attendees for a given event
     * 
     * @param type $eventid
     */
    public function downloadAttendeesAction($eventid) {
        $this->getPermissionManager()->RestrictAccessToLoggedIn();

        $event = $this->getEventManager()->getEventById($eventid);
        $this->RestrictResourceNotFound($event);

        $filename = "attendees.csv";
        $content = $this->render('CptEventBundle:Event:attendees_list.html.twig', array('event' => $event));

        $this->SendCsvFileResponse($content);
    }

    /**
     * Get the list of Events for a month
     * Returns a Json response
     * 
     * Request parameters:
     *      - 'myevents': if present, retreives only "myevents"
     *      - 'pastevents": if present, retreives only past events
     * 
     * @param type $year
     * @param type $month
     * @return JsonResponse
     */
    public function getEventsForMonthAction(Request $request, $year, $month) {
        if ($month > 12){
            $this->ThrowBadRequestException();
        }
        
        $options = Array();
        if ($request->query->has('filter')){
            switch($request->query->get('filter')){
                case 'myevents':  $options['myevents'] = true; break;
                case 'pastevents':  $options['pastevents'] = true; break;
                case 'futureevents':  $options['futureevents'] = true; break;
            }
        }
        
        $month = $this->getCalendR()->getMonth($year, $month);
        $eventCollection = $this->getCalendR()->getEvents($month, $options);

        $serializer = $this->getSerializer();
        $serializedevents = $serializer->serialize($eventCollection, 'json');

        return $this->CreateJsonOkResponse($serializedevents);
    }

    /**
     * Single Event Display
     * 
     * Returns the Json response with html for a single event display
     * 
     * @param type $id
     * @return JsonResponse
     */
    public function getEventAction($id) {
        $event = $this->getEventManager()->getEventById($id);

        $html_string = $this->renderView('CptEventBundle:Event:eventdisplay.html.twig', array(
            'event' => $event,
        ));

        return $this->CreateJsonOkResponse($html_string);
    }

    /**
     * Displays a user search field
     * 
     * @return type
     */
    public function userSearchAction() {
        $params = Array();
        return $this->render('CptMainBundle:Default:searchuser.html.twig', $params);
    }

    /**
     * Returns the user search results
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userGetSearchResultAction() {
        $request = $this->getRequest();

        if ($request->isXmlHttpRequest()) {
            $search_string = $request->query->get('search_string');
            //$exclude_id = $request->query->get('exclude_id');

            $users = $this->getUserManager()->searchUser($search_string);

            $result = Array();
            foreach ($users as $user) {
                $result[] = Array("id" => $user->getId(), "username" => $user->getUserName(), "firstname" => $user->getFirstname(), "lastname" => $user->getLastname());
            }

            $response = new Response(json_encode($result));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else {
            return new Response("Mauvaise méthode d accés", 404);
        }
    }
    
    public function registerForEventAction($eventid, $numparticipants)
    {        
        $this->getPermissionManager()->RestrictAccessToLoggedIn();
        
        $event = $this->getEventManager()->getEventById($eventid);
        $user = $this->getUser();
        
        $registration = $this->getRegistrationManager()
                ->RegisterUserForEvent($event, $user, $numparticipants, false);
        
        $responsedata = $this->getSerializer()->serialize($registration, 'json');

        return $this->CreateJsonOkResponse($responsedata);
    }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Protected">

    /**
     * Get the view to edit an event
     * 
     * @param type $event
     * @param type $form
     * @return type
     */
    protected function GetEventEditView($event, $form) {
    //    $map = $this->get('ivory_google_map.map');
    //    $map->setLanguage($this->get('request')->getLocale());
//        $autocomplete = new Autocomplete();
//
//        $autocomplete->setPrefixJavascriptVariable('place_autocomplete_');
//        $autocomplete->setInputId('place_autocomplete');
//
//      //  $autocomplete->setInputAttributes(array('class' => 'my-class'));
//      //  $autocomplete->setInputAttribute('class', 'my-class');
//
//        $autocomplete->setTypes(array(AutocompleteType::CITIES));
//        $autocomplete->setAsync(true);
//        $autocomplete->setLanguage('en');
        //$autocompleteHelper = new AutocompleteHelper();
        
      //  $mapHelper = new MapHelper();
       // $mapHelper->setExtensionHelper('places', $autocompleteHelper);


       // $googlejavascript = $mapHelper->renderJavascripts($map);

       // $autocompleteHTML = $autocompleteHelper->renderHtmlContainer($autocomplete);
       // $autocompleteJS = $autocompleteHelper->renderJavascripts($autocomplete);
        return $this->renderView('CptEventBundle:Event:edit.html.twig', array(
                    'event' => $event,
                    'eventform' => $form->createView(),
      //              'map' => $map,
        ));
    }

    /**
     * Set the Event#Registrations from a json encoded array (??)
     * 
     * @param type $event
     * @param type $registration_json_array
     * @throws \InvalidArgumentException
     */
    protected function SetJsonRegistrationCollection($event, $registration_json_array) {
        if ($registration_json_array === null) {
            throw new \InvalidArgumentException("Registration list is null or is not an array");
        }
//        if ($eventqueue_json_array === null)
//            throw new \InvalidArgumentException("Event queue is null or is not an array");
        // Creates Registration entity list from the json array (throws exceptions)
        $RegistrationList = null;
        foreach ($registration_json_array as $userid => $registration_json) {
            $RegistrationList[] = $this->get_registration_from_json($registration_json, $event);
        }

        $event->setRegistrations($RegistrationList);
//        $this->get("EventManager")->ReplaceRegistrationCollection($event, $RegistrationList, $eventqueue_json_array);
    }

    protected function get_registration_from_json($registration_json, $event) {
        if ((!is_integer($registration_json->user)) || (!is_integer($registration_json->event)) || (!is_integer($registration_json->numparticipants)) || (!is_integer($registration_json->numqueuedparticipants)) || (!is_integer($registration_json->organizer))
        ) {
            $this->RestrictBusinessRuleError("Registration_json_array is malformed");
        }

        if ($registration_json->numparticipants <= 0) {
            $this->RestrictBusinessRuleError("numparticipants for a registration cannot be <=0 (" . $registration_json->numparticipants . " given)");
        }

        $user = $this->getUserManager()->findUserBy(Array("id" => $registration_json->user));
        if (!$user) {
            $this->RestrictBusinessRuleError("User with id " . $registration_json->user . " does not exists.");
        }

        return $this->getRegistrationManager()->CreateRegistration($event, $user, $registration_json->numparticipants, $registration_json->organizer ? true : false );
    }
  
    // </editor-fold>
}
