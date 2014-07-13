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
    // <editor-fold defaultstate="collapsed" desc="Main Actions">

    /**
     * Displays the whole Event Section
     * 
     * @return type
     */
    public function indexAction($event_permalink) {
        $currentdate = $this->getCalendarManager()->GetNextEventDateOrCurrent(new \Datetime);
        $update_ajax_delay = $this->container->getParameter("cpt.event.update_ajax_delay");
        $permalink_event_id = null;

        if (!empty($event_permalink)) { // Permalink was provided
            if (!preg_match('/.+?/', $event_permalink)) {
                $this->GetPermissionManager()->RestrictResourceNotFound();
            }

            $events = Array();
            $events[] = $this->getEventManager()->findOneByPermalink($event_permalink);
            $permalink_event_id = $events[0]['id'];
        }

        //$countries = $this->getEventManager()->getCountries();
        $countryform = $this->get('form.factory')->createNamed('country', 'cpt_country', null, Array('attr' => Array('id' => 'countryform')));

        return $this->render('CptEventBundle:Event:index.html.twig', array(
                    'currentdate' => $currentdate,
                    'update_ajax_delay' => $update_ajax_delay,
                    'permalink_event_id' => $permalink_event_id,
                    //  'countries' => $countries,
                    'countryform' => $countryform->createView()
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
        $oldevent = null;
        if ((null != $id) && (-1 != $id)) {
            $oldevent = $this->getEventManager()->getDetachedEvent($id);
            $event = $this->getEventManager()->getEventById($id);
            $this->getPermissionManager()->RestrictAccessToUsers($event->getResponsibleUsersIds());
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
                if (($event->getId() != -1) && ( $request->request->get('copy_field') == 1)) {
                    $event = $this->getEventManager()->CopyEvent($event);
                    $form = $this->get('form.factory')->createNamed('event', 'cpt_edit_event', $event, Array('attr' => Array('id' => 'eventform')));
                    return $this->CreateJsonResponse($this->GetEventEditView($event, $form), "copy");
                }

                // If not, process the event queue
                $eventqueue_json_array = json_decode($request->get('event_queue_json'));
                $organizers_json_array = json_decode($request->get('event_organizers_json'));

                if ((!$organizers_json_array ) || (!$eventqueue_json_array)) {
                    throw new \InvalidArgumentException("decoded json is null");
                }

                $event->setQueue($eventqueue_json_array);
                $this->setRegistrations($event, $eventqueue_json_array, $organizers_json_array);

                if ($form->isValid()) {
                    // Save events
                    $changes = $this->getEventManager()->SaveEvent($event, $oldevent);

                    // Send emails
                    $userid = $this->getUser()->getId();
                    $this->sendEventModificationEmails($event, $changes, $userid);
                    $authorregistration = $event->getRegistration($userid);
                    $this->getMailManager()->sendEventSubscriptionEmailMessage($authorregistration);

                    return $this->CreateJsonOkResponse(null);
                } else {
                    return $this->CreateJsonFailedResponse($this->GetEventEditView($event, $form));
                }
            } catch (Exception $e) {
                return new Response("Paramètre incorrects", 400);
            }
        }
        // ****************************************************        
        // Display page
        return new Response($this->GetEventEditView($event, $form));
    }

    public function registerForEventAction($eventid, $numattendees) {
        try {
            $request = $this->getRequest();

            $this->getPermissionManager()->RestrictAccessToLoggedIn();
            $this->getPermissionManager()->RestrictAccessToAjax($request);

            $numattendees = intval($numattendees);
            $eventid = intval($eventid);

            $oldevent = $this->getEventManager()->getDetachedEvent($event->getId());

            $event = $this->getEventManager()->getEventById($eventid);
            $this->GetPermissionManager()->RestrictResourceNotFound($event);

            $user = $this->getUser();

            $changes = $this->getRegistrationManager()
                    ->RegisterUserForEvent($event, $user, $numattendees, false, $oldevent);

            //$event = $this->getEventManager()->getEventById($eventid);

            $registration = $event->getRegistration($user->getId());

            $this->getMailManager()->sendEventSubscriptionEmailMessage($registration);
            $this->sendEventModificationEmails($event, $changes, $user->getId());

            $html_string = $this->renderView('CptEventBundle:Event:eventdisplay.html.twig', array(
                'event' => $event,
            ));

            return $this->CreateJsonOkResponse($html_string);
        } catch (\Exception $e) {
            return $this->CreateJsonFailedResponse();
        }

        //$responsedata = $this->getSerializer()->serialize($registration, 'json');
        // return $this->CreateJsonOkResponse($responsedata);
        //return $this->getEventAction($eventid);
    }

    public function cancelEventAction($id) {
        try {
            $request = $this->getRequest();

            $this->getPermissionManager()->RestrictAccessToLoggedIn();
            $this->getPermissionManager()->RestrictAccessToAjax($request);


            $event = $this->getEventManager()->getEventById($id);
            $this->GetPermissionManager()->RestrictResourceNotFound($event);

            foreach ($event->getRegistrations() as $registration) {
                $this->getMailManager()->sendEventCancelledEmailMessage($event, $registration->getUser());
            }

            $result = $this->getEventManager()->cancelEvent($event);

            return $this->CreateJsonOkResponse($result);
        } catch (\Exception $e) {
            return $this->CreateJsonFailedResponse();
        }
    }

    public function cancelRegistrationAction($eventid) {
        try {
            $request = $this->getRequest();

            $this->getPermissionManager()->RestrictAccessToLoggedIn();
            $this->getPermissionManager()->RestrictAccessToAjax($request);

            $user = $this->getUser();
            $oldevent = $this->getEventManager()->getDetachedEvent($eventid); // Always get the detached event before the new event!
            $event = $this->getEventManager()->getEventById($eventid);

            $changes = $this->getRegistrationManager()->CancelRegistration($event, $user, $oldevent);

            $this->getMailManager()->sendEventCancelRegistrationEmailMessage($event, $user);
            $this->sendEventModificationEmails($event, $changes, $user->getId());


            $this->get('session')->getFlashBag()->add(
                    'notice', $this->get('translator')->trans('registration.has_been_cancelled')
            );

            $html_string = $this->renderView('CptEventBundle:Event:eventdisplay.html.twig', array(
                'event' => $event,
            ));

            return $this->CreateJsonOkResponse($html_string);
        } catch (\Exception $e) {
            return $this->CreateJsonFailedResponse();
        }
    }

    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Other Actions">

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
        $request = $this->getRequest();

        $this->getPermissionManager()->RestrictAccessToLoggedIn();
        $this->getPermissionManager()->RestrictAccessToAjax($request);

        $event = $this->getEventManager()->getEventById($id);
        $this->GetPermissionManager()->RestrictResourceNotFound($event);

        $this->getPermissionManager()->RestrictAccessToUsers($event->getResponsibleUsersIds());

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
        $this->GetPermissionManager()->RestrictResourceNotFound($event);

        $filename = "attendees.csv";
        $content = $this->render('CptEventBundle:Event:attendees_list.html.twig', array('event' => $event));

        return $this->CreateCsvFileResponse($content);
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
        if ($month > 12) {
            $this->ThrowBadRequestException();
        }

        $options = Array();
        if ($request->query->has('filter')) {
            switch ($request->query->get('filter')) {
                case 'myevents': $options['myevents'] = true;
                    break;
                case 'pastevents': $options['pastevents'] = true;
                    break;
                case 'futureevents': $options['futureevents'] = true;
                    break;
            }
        }

        if ($request->query->has('country_code')) {
            $options['country_code'] = $request->query->get('country_code');
        }

        $month = $this->getCalendR()->getMonth($year, $month);
        $eventCollection = $this->getCalendR()->getEvents($month, $options);

        $serializer = $this->getSerializer();
        $serializedevents = $serializer->serialize($eventCollection, 'json');

        return $this->CreateJsonOkResponse($serializedevents);
    }

    /**
     * Get one event per id
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
    public function getEventsForIdAction(Request $request, $id) {
        $eventCollection = Array();
        $eventCollection['events'] = Array();
        $eventCollection['events'][0] = $this->getEventManager()->getEventById($id);
        $this->GetPermissionManager()->RestrictResourceNotFound($eventCollection['events'][0]);


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

    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Protected">

    /**
     * Sends emails according to the event Registrations modifications
     * If $not_for_userid is specified, emails will not be sent to this user
     * 
     * @param type $event
     * @param type $changes
     * @param type $not_for_userid
     */
    protected function sendEventModificationEmails($event, $changes, $not_for_userid = null) {
        foreach ($changes as $userid => $change) {
            if ($userid != $not_for_userid) {

                $to_orga = REGISTRATION_CHANGE_ORGA_ADDED & $change;
                $to_notorga = REGISTRATION_CHANGE_ORGA_REMOVED & $change;
                $queue_changed = REGISTRATION_CHANGE_NUMQUEUED & $change;
                $num_attendeechanged = REGISTRATION_CHANGE_NUMATT & $change;
                $reg_cancelled = REGISTRATION_CHANGE_REMOVED & $change;
                $reg_added = REGISTRATION_CHANGE_ADDED & $change;

                if ($to_orga || $to_notorga || $queue_changed || $num_attendeechanged || $reg_cancelled || $reg_added) {
                    $registration = $event->getRegistration($userid);
                    $user = $this->getUserManager()->findUserBy(Array("id" => $userid));
                    $this->getMailManager()->sendEventSubscriptionModificationEmailMessage($user, $event, $registration, $to_orga, $to_notorga, $queue_changed, $num_attendeechanged, $reg_cancelled, $reg_added);
                }
            }
        }
    }

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
     * Sets the Registration for the event for a given queue and organizers array
     * 
     * @param type $event
     * @param type $queue
     * @param type $organizers array userid => bool (true: is organizer, false: is not organizer)
     * @return array
     */
    protected function setRegistrations($event, $queue, $organizers) {
        $registrations = Array();

        $this->getRegistrationManager()->DeleteAllRegistrations($event);

        $numattendees = array_count_values($queue);

        foreach ($numattendees as $user_id => $num_attendees) {
            $user = $this->getUserManager()->findUserBy(Array("id" => $user_id));

            if (!$user) {
                $this->RestrictBusinessRuleError("User with id " . $user_id . " does not exists.");
            }

            $registration = $this->getRegistrationManager()->CreateRegistration($event, $user, $num_attendees, $organizers[$user_id] ? true : false );
            $event->addRegistration($registration);
        }

        return $registrations;
    }

    // </editor-fold>
}
