<?php

namespace Cpt\EventBundle\Manager;

use Cpt\EventBundle\Manager\BaseManager as BaseManager;
use Cpt\EventBundle\Interfaces\Manager\EventManagerInterface as EventManagerInterface;
use Cpt\EventBundle\Entity\Event as Event;
use Cpt\EventBundle\Interfaces\Entity\EventInterface as EventInterface;
use FOS\UserBundle\Model\UserInterface as UserInterface;
use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;
use Cpt\PublicationBundle\Manager\PermalinkDateManager as PermalinkDateManager;


class EventManager extends BaseManager implements EventManagerInterface {

    // <editor-fold defaultstate="collapsed" desc="Event ProviderInterface">
    /**
     * Returns the events between in a provided time frame.
     * These events have the ParticipationLevel correctly initialized.
     * 
     * Options: 
     *      'myevents' => only returns my events
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param array $options
     * @return type
     */
    public function getEvents(\DateTime $begin, \DateTime $end, array $options = array()) {
        

        if (!$this->getPermissionManager()->isLoggedIn()) {   // Not logged in, the event participation levels will be set to unknown by event constructor            
            
            return $this->getEventsBeginingBetween($begin, $end, $options);
        } else {
            // User is logged in, we want to calculate the participation level as we retreive the events
            return $this->getEventsBeginingBetweenForLoggedinUser($begin, $end, $this->getUser(), $options);
        }
    }
    // </editor-fold>
  
    // <editor-fold defaultstate="collapsed" desc="Public: Event related">

    public function getAllEvents()
    {
        return $this->getEventRepository()->findAll();
    }
    
    public function createEvent(UserInterface $author, $enabled = true, $approved = true, $restricted = false) {
        $event = new Event();

        if (!$author) {
            throw new \InvalidArgumentException("Cannot create an event without creator (null)");
        }

        $this->getRegistrationManager()->AddDefaultRegistration($event, $author);

        $event->setAuthor($author);
        $event->setEnabled($enabled);
        $event->setRestricted($restricted);
        $event->setApproved($approved);

        return $event;
    }

    public function SaveEvent(EventInterface $event) {
        try {
        $event->UpdateCounters();

        $this->em->persist($event);

        $this->em->flush();
        
            
        } catch (\Exception $e) {
            $this->em->getConnection()->rollback();
            $this->em->close();
            throw $e;
        }
    }

    public function CopyEvent(EventInterface $event) {
        if (($event) && ($event->getId() != -1)) {
            $event = clone $event;
            $event->setId(-1);
            $event->setRegistrations(new ArrayCollection());
            $event->setComments(new ArrayCollection());
            $event->setQueue(Array());
            $this->GetRegistrationManager()->AddDefaultRegistration($event);
            $event->setBegin(new \DateTime);
            $event->setEnd(new \DateTime);
        }

        return $event;
    }

    /**
     * Returns a single event using it's id.
     * The event must exists or an exception will be thrown.
     *
     * @param integer $id
     * @return type
     * @throws SymfonyException\NotFoundHttpException
     */
    public function getEventById($id) {
        $event = $this->getEventRepository()->find($id);

        if (!$event) {
            throw new SymfonyException\NotFoundHttpException("Resource not found.");
        }

        return $event;
    }

    public function cancelEvent(EventInterface $event){
        // To do: add email sending
        
        $this->em->remove($event);
        $this->em->flush();
    }
    
    public function findOneByPermalink($permalink) {
        try {
            $repository = $this->em->getRepository($this->class);

            $query = $repository->createQueryBuilder('p');
            $query->select('p.id');
            $PermalinkGenerator = new PermalinkDateManager();
            $urlParameters = $PermalinkGenerator->getParameters($permalink);

            $parameters = array();

            if (isset($urlParameters['year']) && isset($urlParameters['month']) && isset($urlParameters['day'])) {
                $pdqp = $this->getPublicationDateQueryParts(sprintf('%d-%d-%d', $urlParameters['year'], $urlParameters['month'], $urlParameters['day']), 'day');

                $parameters = array_merge($parameters, $pdqp['params']);

                $query->andWhere($pdqp['query']);
            }

            if (isset($urlParameters['slug'])) {
                $query->andWhere('p.slug = :slug');
                $parameters['slug'] = $urlParameters['slug'];
            }

            if (count($parameters) == 0) {
                throw new SymfonyException\NotFoundHttpException("Resource not found.");
            }

            $query->setParameters($parameters);
            $entity = $query->getQuery()->getSingleResult();

            if (!$entity) {
                throw new SymfonyException\NotFoundHttpException("Resource not found.");
            }

            if (!$this->getSecurityContext()->isGranted('VIEW', $entity)) {
                throw new AccessDeniedException();
            }

            return $entity;
        } catch (\Doctrine\Orm\NoResultException $e) {
            $this->getPermissionManager()->RestrictResourceNotFound();
        }
    }

    public function getCountries()
    {
        return $this->getCountryRepository()->findAll();
    }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Protected">

    /**
     * Returns all enabled events begining in provided time interval
     *
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param Array $options: 
     *              - 'pastevents' : only returns past events
     *              - 'futureevents' : only returns future events
     * @return Array of events
     */
    protected function getEventsBeginingBetween(\DateTime $begin, \DateTime $end, $options) {
        $qb = $this->getEventRepository()
                        ->createQueryBuilder('e')
                        ->Select('e')
                        ->Where('(e.begin >= :from AND e.begin < :to) OR (e.end >= :from AND e.end < :to)')
                        ->AndWhere('e.enabled = :enabled');
        
        if (array_key_exists('country_code', $options)){
            $qb->AndWhere('e.country_code = :country_code')
                ->setParameter('country_code', $options['country_code']);
        }
                        
                        $qb->setParameter('from', $begin)
                        ->setParameter('to', $end)
                        ->setParameter('enabled', true);
        
            if (array_key_exists('pastevents', $options)){
                $this->getPastEventQueryPart($qb);
            }
            else if (array_key_exists('futureevents', $options)){
               $this->getFutureEventQueryPart($qb); // Default is to display future events only
            }
      
      return $qb->getQuery()->getResult();
    }

    /**
     * Returns all the events begining in provided time interval and additionally computes the participation level of each event
     *
     *      * @param \DateTime $begin
     * @param \DateTime $end
     * @param \FOS\UserBundle\Model\UserInterface $user
     * @param Array $options: 
     *              - 'pastevents' : only returns past events
     *              - 'futureevents' : only returns future events
     *              - 'myevents' : only returns my events
     */
    protected function getEventsBeginingBetweenForLoggedinUser(\DateTime $begin, \DateTime $end, UserInterface $user, $options) {
        // Doctrine fetch join: Event#registrations is hydrated with what was found in 'r'
        $qb = $this->getEventRepository()
                ->createQueryBuilder('e')
                ->Select('e', 'r')
                ->leftJoin('e.registrations', 'r', 'WITH', 'IDENTITY(r.user) = :userid')
                ->Where('(e.begin >= :from AND e.begin < :to) OR (e.end >= :from AND e.end < :to)');
        
        if (array_key_exists('country_code', $options)){
            $qb->AndWhere('e.country_code = :country_code')
                ->setParameter('country_code', $options['country_code']);
        }
        
                $qb->orderby('e.publicationDateStart', 'DESC')
                ->setParameter('from', $begin)
                ->setParameter('to', $end)
                ->setParameter('userid', $user->getId());

        
            if (array_key_exists('myevents', $options)){
                $qb->AndWhere('(IDENTITY(e.author) = :userid) OR (IDENTITY(r.user) = :userid)'); // Filter to get only "my events"
            } else {
                $this->getPublicOnlyQueryPart($qb,'e');
            }
        
            if (array_key_exists('pastevents', $options)){
                $this->getPastEventQueryPart($qb);
            } 
            else if (array_key_exists('futureevents', $options)){
                $this->getFutureEventQueryPart($qb);
            }
                
         $events = $qb->getQuery()->getResult();

        $this->updateEventArrayParticipationLevel($events, $user);

        // If accessing the registrations it will be totally reloaded, as it was only partially loaded above
        foreach($events as $event){
            $event->getRegistrations()->setDirty(true); 
        }
        
        return $events;
     }

    /**
     * Updates the participation level (author, attendee, organizer..) of each event in provided collection
     * for user provided user
     *
     * @param array $events
     * @param \FOS\UserBundle\Model\UserInterface $user
     */
    protected function updateEventArrayParticipationLevel(Array $events, UserInterface $user) {
        foreach ($events as $event) {
            $is_author = $this->isAuthor($event, $user);
            $is_attendee = $this->isAttendee($event, $user);
            $is_organizer = $this->isAttendee($event, $user, $is_attendee);

            $event->computeParticipationLevel($is_author, $is_attendee, $is_organizer);
        }
    }

    /**
     * Indicates if provided user is the author of provided event
     *
     * @param \Cpt\EventBundle\Interfaces\Entity\EventInterface $event
     * @param \FOS\UserBundle\Model\UserInterface $user
     * @return boolean
     */
    protected function isAuthor(EventInterface $event, UserInterface $user) {
        return ($event->getAuthor()->getId() == $user->getId());
    }

    /**
     * Indicates if provided user is an attendee of provided event
     *
     * @param \Cpt\EventBundle\Interfaces\Entity\EventInterface $event
     * @param \FOS\UserBundle\Model\UserInterface $user
     * @return boolean
     */
    protected function isAttendee(EventInterface $event, UserInterface $user) {
        $registrations = $event->getRegistrations();
        foreach ($registrations as $registration) {
            if ($registration->getUser()->getId() == $user->getId()) {
                return true;
            }
        }

        return false;

    }

    /**
     * Indicates if provided user is an organizer of provided event
     *
     * @param \Cpt\EventBundle\Interfaces\Entity\EventInterface $event
     * @param \FOS\UserBundle\Model\UserInterface $user
     * @param boolean $is_attendee if we already know if the user is at least an attendee, then it can be provided to improve performances
     * @return boolean
     */
    protected function isOrganizer(EventInterface $event, UserInterface $user, $is_attendee = true) {
        if (!$is_attendee) { // If we already know that the user is not an attendee, he cannot be an organizer
            return false;
        }

        $registrations = $event->getRegistrations();
        foreach ($registrations as $registration) {
            if (($registration->getOrganizer()) && ($registration->getUser()->getId() == $user->getId())) {
                return true;
            }
        }

        return false;
    }

    // </editor-fold>
}
