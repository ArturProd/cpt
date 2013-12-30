<?php

namespace Cpt\EventBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Cpt\PublicationBundle\Entity\Publication as Publication;
use Cpt\EventBundle\Interfaces\Entity\EventInterface as EventInterface;

use CalendR\Period\PeriodInterface as PeriodInterface;

use JMS\Serializer\Annotation\ExclusionPolicy as ExclusionPolicy;
use JMS\Serializer\Annotation\Expose as Expose;
use JMS\Serializer\Annotation\VirtualProperty as VirtualProperty;
use JMS\Serializer\Annotation\SerializedName as SerializedName;

/**
 * @ExclusionPolicy("all")
 */
class Event extends Publication implements EventInterface
{
    public function __construct()
    {
        parent::__construct(null);
        
        $this->allowedattendees = new ArrayCollection();
        //$this->comments = new ArrayCollection();
        $this->begin = new \DateTime;
        $this->end = new \DateTime;
        $this->maxnumattendees = 3;
        $this->count_queued_attendees = 0;
        $this->count_total_attendees = 0;
        $this->published = true;
        $this->restricted = false;
        $this->approved = false;
        $this->country_code = "FR";
        $this->city_name = "";
        $this->city_postal_code = "";
        $this->address = "";
        $this->corporate_name = "";
        $this->cpt_event = true;
        $this->registrations = new ArrayCollection();
        $this->queue = Array();
        $this->allowedattendees = true;
        $this->registration_allowed = true;
        $this->location_show_map = true;
        $this->location_long = "";
        $this->location_lat = "";
        
        $this->ismyevent = EventInterface::MYEVENT_UNKNOWN;
    }
    
    /* Unmapped */
    protected $ismyevent;
    
    public function isMyEvent()
    {
        return $this->ismyevent;
    }
    
    public function setMyEvent($ismyevent)
    {
        $this->ismyevent = $ismyevent;
    }
       
    public function getUid()
    {
        return $this->getId();
    }

        /**
     * Set begindatetime
     *
     * @param \DateTime $begindatetime
     * @return BaseEvent
     */
    public function setBegin(\DateTime $begin)
    {
        $this->begin = $begin;

        return $this;
    }

    /**
     * Get begindatetime
     *
     * @return \DateTime 
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * Set enddatetime
     *
     * @param \DateTime $enddatetime
     * @return BaseEvent
     */
    public function setEnd(\DateTime $end)
    {
        $this->end = $end;

        return $this;
    }

    /**
     * Get enddatetime
     *
     * @return \DateTime 
     */
    public function getEnd()
    {
        return $this->end;
    }
    
    public function contains(\DateTime $datetime)
    {
        return $this->getBegin()->diff($datetime)->invert == 0 && $this->getEnd()->diff($datetime)->invert == 1;
    }


    public function containsPeriod(PeriodInterface $period)
    {
       /* return $this->getBegin()->diff($period->getBegin())->invert == 0
            && $this->getEnd()->diff($period->getEnd())->invert == 1; */
        $value = $this->getBegin()->diff($period->getBegin())->invert == 0
            && $this->getBegin()->diff($period->getEnd())->invert == 1;
        
        return $value;
    }

    public function isDuring(PeriodInterface $period)
    {
         // return $this->getBegin() >= $period->getBegin() && $this->getEnd() < $period->getEnd();
        // Event is during a period if event begining is before period end, and event end is after period begin
        $value = $this->getBegin() < $period->getEnd() && $this->getEnd() >= $period->getBegin();
        return $value;
    }
    //  <editor-fold defaultstate="collapsed" desc="Attributes">

    /**
     * @Expose
     */
    protected $registrations;
     
    /**
     * @var \DateTime
     * @Expose
     */
    protected $begin;

    /**
     * @var \DateTime
     * @Expose
     */
    protected $end;

    /**
     * @var integer
     * @Expose
     */
    protected $maxnumattendees;

    /**
     * @var integer
     * @Expose
     */
    protected $count_queued_attendees;

    /**
     * @var integer
     * @Expose
     */
    protected $count_total_attendees;

    /**
     * @var boolean
     * @Expose
     */
    protected $published;

    /**
     * @var boolean
     * @Expose
     */
    protected $restricted;

    /**
     * @var boolean
     * @Expose
     */
    private $cpt_event;
    
    /**
     * @var string
     * @Expose
     */
    protected $country_code;

    /**
     * @var string
     * @Expose
     */
    protected $city_name;

    /**
     * @var string
     * @Expose
     */
    protected $city_postal_code;

    /**
     * @var string
     * @Expose
     */
    protected $address;

    /**
     * @var string
     * @Expose
     */
    protected $corporate_name;

     /**
     * @var boolean
     * @Expose
     */
    protected $location_show_map;

    /**
     * @var string
     * @Expose
     */
    protected $location_long;

    /**
     * @var string
     * @Expose
     */
    protected $location_lat;

    /**
     * @var boolean
     * @Expose
     */
    protected $approved;

    
    /**
     * @var array
     * @Expose
     */
    protected $queue;

    /**
     * @var boolean
     * @Expose
     */
    protected $registration_allowed;

    /**
     * @Expose
     */
    protected $allowedattendees;


        /**
     * Add registrations
     *
     * @param \Cpt\EventBundle\Entity\Registration $registrations
     * @return Event
     */
    public function addRegistration(\Cpt\EventBundle\Entity\Registration $registrations)
    {
        $this->registrations[] = $registrations;

        return $this;
    }

    /**
     * Remove registrations
     *
     * @param \Cpt\EventBundle\Entity\Registration $registrations
     */
    public function removeRegistration(\Cpt\EventBundle\Entity\Registration $registrations)
    {
        $this->registrations->removeElement($registrations);
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */

        public function getRegistrations() {
        return $this->registrations;
    }

    public function setRegistrations($registrations) {
        $this->registrations = $registrations;
    }

    /**
     * Set maxnumattendees
     *
     * @param integer $maxnumattendees
     * @return BaseEvent
     */
    public function setMaxnumattendees($maxnumattendees)
    {
        $this->maxnumattendees = $maxnumattendees;

        return $this;
    }

    /**
     * Get maxnumattendees
     *
     * @return integer 
     */
    public function getMaxnumattendees()
    {
        return $this->maxnumattendees;
    }


    /**
     * Set count_queued_attendees
     *
     * @param integer $countQueuedAttendees
     * @return BaseEvent
     */
    public function setCountQueuedAttendees($countQueuedAttendees)
    {
        $this->count_queued_attendees = $countQueuedAttendees;

        return $this;
    }

    /**
     * Get count_queued_attendees
     *
     * @return integer 
     */
    public function getCountQueuedAttendees()
    {
        return $this->count_queued_attendees;
    }

    /**
     * Set count_total_attendees
     *
     * @param integer $countTotalAttendees
     * @return BaseEvent
     */
    public function setCountTotalAttendees($countTotalAttendees)
    {
        $this->count_total_attendees = $countTotalAttendees;

        return $this;
    }

    /**
     * Get count_total_attendees
     *
     * @return integer 
     */
    public function getCountTotalAttendees()
    {
        return $this->count_total_attendees;
    }

    /**
     * Set published
     *
     * @param boolean $published
     * @return BaseEvent
     */
    public function setPublished($published)
    {
        $this->published = $published == null ? false : $published;;
        $this->published = true; // TODO: feature not implemented

        return $this;
    }

    /**
     * Get published
     *
     * @return boolean 
     */
    public function getPublished()
    {
        return $this->published;
    }

    /**
     * Set restricted
     *
     * @param boolean $restricted
     * @return BaseEvent
     */
    public function setRestricted($restricted)
    {
        $this->restricted = $restricted == null ? false : $restricted;
        $this->restricted = false; // TODO: feature not implemented
        return $this;
    }

    /**
     * Get restricted
     *
     * @return boolean 
     */
    public function getRestricted()
    {
        return $this->restricted;
    }
 
    /**
     * Set approved
     *
     * @param boolean $approved
     * @return BaseEvent
     */
    public function setApproved($approved)
    {
        $this->approved = $approved == null ? false : $approved;
        $this->approved = true; // TODO: Feature not implemented
        return $this;
    }

    /**
     * Get approved
     *
     * @return boolean 
     */
    public function getApproved()
    {
        return $this->approved;
    }
    
    /**
     * Set country_code
     *
     * @param string $countryCode
     * @return BaseEvent
     */
    public function setCountryCode($countryCode)
    {
        $this->country_code = $countryCode;

        return $this;
    }

    /**
     * Get country_code
     *
     * @return string 
     */
    public function getCountryCode()
    {
        return $this->country_code;
    }

    /**
     * Set city_name
     *
     * @param string $cityName
     * @return BaseEvent
     */
    public function setCityName($cityName)
    {
        $this->city_name = $cityName;

        return $this;
    }

    /**
     * Get city_name
     *
     * @return string 
     */
    public function getCityName()
    {
        return $this->city_name;
    }

    /**
     * Set city_postal_code
     *
     * @param string $cityPostalCode
     * @return BaseEvent
     */
    public function setCityPostalCode($cityPostalCode)
    {
        $this->city_postal_code = $cityPostalCode;

        return $this;
    }

    /**
     * Get city_postal_code
     *
     * @return string 
     */
    public function getCityPostalCode()
    {
        return $this->city_postal_code;
    }

    /**
     * Set Address
     *
     * @param string $Address
     * @return BaseEvent
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get Address
     *
     * @return string 
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set corporate_name
     *
     * @param string $corporateName
     * @return BaseEvent
     */
    public function setCorporateName($corporateName)
    {
        $this->corporate_name = $corporateName;

        return $this;
    }

    /**
     * Get corporate_name
     *
     * @return string 
     */
    public function getCorporateName()
    {
        return $this->corporate_name;
    }

    /**
     * Set cpt_event
     *
     * @param boolean $cptEvent
     * @return BaseEvent
     */
    public function setCptEvent($cptEvent)
    {
        $this->cpt_event = $cptEvent == null ? false : $cptEvent ;
    
        return $this;
    }

    /**
     * Get cpt_event
     *
     * @return boolean 
     */
    public function getCptEvent()
    {
        return $this->cpt_event;
    }
    
    
    
    // </editor-fold>
    
    //  <editor-fold defaultstate="collapsed" desc="Getters and Setters">
 
    public function getAuthor() {
        return $this->author;
    }

    public function setAuthor($author) {
        $this->author = $author;
    }


              // </editor-fold>


    /**
     * Set queue
     *
     * @param array $queue
     * @return Event
     */
    public function setQueue($queue)
    {
        $this->queue = $queue;
    
        return $this;
    }

    /**
     * Get queue
     *
     * @return array 
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * Set registration_allowed
     *
     * @param boolean $registrationAllowed
     * @return Event
     */
    public function setRegistrationAllowed($registrationAllowed)
    {
        $this->registration_allowed = $registrationAllowed;
    
        return $this;
    }

    /**
     * Get registration_allowed
     *
     * @return boolean 
     */
    public function getRegistrationAllowed()
    {
        return $this->registration_allowed;
    }



    /**
     * Set location_show_map
     *
     * @param boolean $locationShowMap
     * @return Event
     */
    public function setLocationShowMap($locationShowMap)
    {
        $this->location_show_map = $locationShowMap;
    
        return $this;
    }

    /**
     * Get location_show_map
     *
     * @return boolean 
     */
    public function getLocationShowMap()
    {
        return $this->location_show_map;
    }

    /**
     * Set location_long
     *
     * @param string $locationLong
     * @return Event
     */
    public function setLocationLong($locationLong)
    {
        $this->location_long = $locationLong;
    
        return $this;
    }

    /**
     * Get location_long
     *
     * @return string 
     */
    public function getLocationLong()
    {
        return $this->location_long;
    }

    /**
     * Set location_lat
     *
     * @param string $locationLat
     * @return Event
     */
    public function setLocationLat($locationLat)
    {
        $this->location_lat = $locationLat;
    
        return $this;
    }

    /**
     * Get location_lat
     *
     * @return string 
     */
    public function getLocationLat()
    {
        return $this->location_lat;
    }
}