<?php

namespace Cpt\EventBundle\Controller;

use Cpt\MainBundle\Controller\BaseController as BaseController;
use Cpt\EventBundle\Entity as Entity;
use Cpt\EventBundle\Form\Type;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Ivory\GoogleMap\Events\Event as GMapEvent;

class EventController extends BaseController {

    public function indexAction() {
        return $this->render(
                        'CptEventBundle:Event:index.html.twig', array('name' => 'Toto!')
        );
    }

    /**
     * 
     * @param type $id
     * @return type
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
                if (($event->getId() != -1) && ( $request->request->get('copy_field') == 1))
                    $event = $this->getEventManager()->CopyEvent($event);
                else { // If not, process the event queue
                    $registrationlist_json_array = json_decode($request->get('registration_list_json'));
                    $eventqueue_json_array = json_decode($request->get('event_queue_json'));

                    if ((!$registrationlist_json_array ) || (!$eventqueue_json_array))
                        throw new \InvalidArgumentException("decoded json is null");

                    $event->setQueue($eventqueue_json_array);
                    $this->SetJsonRegistrationCollection($event, $registrationlist_json_array);
                }
            } catch (Exception $e) {
                return new Response("Paramètre incorrects", 400);
            }

            if ($form->isValid()) {

                $this->getEventManager()->SaveEvent($event);
            }

            return $this->GetEventEditView($event, $form);
        }
        // ****************************************************        
        // Display page
        return $this->GetEventEditView($event, $form);
    }

    /*
     * Compares the last update timestamp of an event with the provided timestamp.
     * Returns a json object with value "true" if the event was updated in db since the provided timestamp, "false" otherwise
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

    public function downloadAttendeesAction($eventid) {
        $this->getPermissionManager()->RestrictAccessToLoggedIn();

        $event = $this->getEventManager()->getEventById($eventid);
        $this->RestrictResourceNotFound($event);

        $filename = "attendees.csv";
        $content = $this->render('CptEventBundle:Event:attendees_list.html.twig', array('event' => $event));

        $this->SendCsvFileResponse($content);
    }

    public function viewCalendarAction($year = null, $month = null) {
        if ((!$year) || (!$month)) {
            $showdate = $this->getEventManager()->GetNextEventDateOrCurrent(new \Datetime);
        } else {
            if ($month > 12)
                $this->RestrictResourceNotFound();

            $showdate = mktime(0, 0, 0, $month, $year);
        }

        return $this->render('CptEventBundle:Event:calendar.html.twig', array(
                    'currentdate' => $showdate
        ));
    }

    public function getEventsForMonthAction($year, $month) {
        if (($year < 2012) || ($month > 12))
            $this->ThrowBadRequestException();

        $month = $this->getCalendR()->getMonth($year, $month);
        $eventCollection = $this->getCalendR()->getEvents($month);

        $serializer = $this->getSerializer();
        $serializedevents = $serializer->serialize($eventCollection, 'json');

        return $this->CreateJsonOkResponse($serializedevents);
    }

    public function getEventAction($id) {
        $event = $this->getEventManager()->getEventById($id);

        $html_string = $this->renderView('CptEventBundle:Event:eventdisplay.html.twig', array(
            'event' => $event,
        ));

        return $this->CreateJsonOkResponse($html_string);
    }

    protected function GetEventEditView($event, $form) {
        $map = $this->get('ivory_google_map.map');
        $map->setLanguage($this->get('request')->getLocale());

        return $this->render('CptEventBundle:Event:new.html.twig', array(
                    'event' => $event,
                    'eventform' => $form->createView(),
                    'map' => $map
        ));
    }

    protected function set_event_registration_from_json($event, $registration_json_array, $eventqueue_json_array) {
        
    }

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

        return $this->getEventManager()->CreateRegistration($event, $user, $registration_json->numparticipants, $registration_json->organizer ? true : false );
    }

    public function userSearchAction() {
        $params = Array();
        return $this->render('CptMainBundle:Default:searchuser.html.twig', $params);
    }

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

    protected function setFlashMessage($title, $content) {
        $this->get('session')->getFlashBag()->add(
                'popup_message', $title
        );
        $this->get('session')->getFlashBag()->add(
                'popup_message', $content
        );
    }

    protected function getUserManager() {
        return $this->get('fos_user.user_manager');
    }

}
