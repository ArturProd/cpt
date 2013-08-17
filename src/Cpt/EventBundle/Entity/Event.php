<?php

namespace Cpt\EventBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;



class Event
{
    public function __construct()
    {
        $this->id = -1;
        $this->allowedattendees = new ArrayCollection();
        $this->author = null;
        $this->comments = new ArrayCollection();
        $this->begindatetime = new \DateTime;
        $this->enddatetime = new \DateTime;
        $this->description = "";
        $this->maxnumattendees = 3;
        $this->count_queued_attendees = 0;
        $this->count_total_attendees = 0;
        $this->published = true;
        $this->restricted = false;
        $this->title = "";
        $this->approved = false;
        $this->country_code = "FR";
        $this->city_name = "";
        $this->city_postal_code = "";
        $this->street = "";
        $this->street_number = "";
        $this->additional_address = "";
        $this->corporate_name = "";
        $this->cpt_event = true;
        $this->registrations = new ArrayCollection();
        $this->queue = Array();
        $this->allowedattendees = true;
        $this->registration_allowed = true;
        $this->location_show_map = true;
        $this->location_long = "";
        $this->location_lat = "";

    }
       
    //  <editor-fold defaultstate="collapsed" desc="Attributes">

   public function prePersist()
    {
        $this->setCreatedAt(new \DateTime);
        $this->setUpdatedAt(new \DateTime);
    }
    
    public function preUpdate()
    {
        $this->setUpdatedAt(new \DateTime);
    }
    
    protected $registrations;
        
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $begindatetime;

    /**
     * @var \DateTime
     */
    private $enddatetime;

    /**
     * @var string
     */
    private $description;

    /**
     * @var integer
     */
    private $maxnumattendees;

    /**
     * @var integer
     */
    private $count_queued_attendees;

    /**
     * @var integer
     */
    private $count_total_attendees;

    /**
     * @var boolean
     */
    private $published;

    /**
     * @var boolean
     */
    private $restricted;


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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }
    
    /**
     * Set begindatetime
     *
     * @param \DateTime $begindatetime
     * @return BaseEvent
     */
    public function setBegindatetime($begindatetime)
    {
        $this->begindatetime = $begindatetime;

        return $this;
    }

    /**
     * Get begindatetime
     *
     * @return \DateTime 
     */
    public function getBegindatetime()
    {
        return $this->begindatetime;
    }

    /**
     * Set enddatetime
     *
     * @param \DateTime $enddatetime
     * @return BaseEvent
     */
    public function setEnddatetime($enddatetime)
    {
        $this->enddatetime = $enddatetime;

        return $this;
    }

    /**
     * Get enddatetime
     *
     * @return \DateTime 
     */
    public function getEnddatetime()
    {
        return $this->enddatetime;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return BaseEvent
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
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
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $updatedAt;


    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return BaseEvent
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return BaseEvent
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
    /**
     * @var string
     */
    private $title;


    /**
     * Set title
     *
     * @param string $title
     * @return BaseEvent
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }
    /**
     * @var boolean
     */
    private $approved;


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
     * @var string
     */
    private $country_code;

    /**
     * @var string
     */
    private $city_name;

    /**
     * @var string
     */
    private $city_postal_code;

    /**
     * @var string
     */
    private $street;

    /**
     * @var string
     */
    private $street_number;

    /**
     * @var string
     */
    private $additional_address;

    /**
     * @var string
     */
    private $corporate_name;

  
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
     * Set street
     *
     * @param string $street
     * @return BaseEvent
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get street
     *
     * @return string 
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set street_number
     *
     * @param string $streetNumber
     * @return BaseEvent
     */
    public function setStreetNumber($streetNumber)
    {
        $this->street_number = $streetNumber;

        return $this;
    }

    /**
     * Get street_number
     *
     * @return string 
     */
    public function getStreetNumber()
    {
        return $this->street_number;
    }

    /**
     * Set additional_address
     *
     * @param string $additionalAddress
     * @return BaseEvent
     */
    public function setAdditionalAddress($additionalAddress)
    {
        $this->additional_address = $additionalAddress;

        return $this;
    }

    /**
     * Get additional_address
     *
     * @return string 
     */
    public function getAdditionalAddress()
    {
        return $this->additional_address;
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
     * @var boolean
     */
    private $cpt_event;


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
    
    
    protected $author;
        

   
    
    protected $allowedattendees;
    
    private $comments;
 

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
     * Add comments
     *
     * @param \Cpt\BlogBundle\Entity\Comment $comments
     * @return Event
     */
    public function addComment(\Cpt\BlogBundle\Entity\Comment $comments)
    {
        $this->comments[] = $comments;

        return $this;
    }

    /**
     * Remove comments
     *
     * @param \Cpt\BlogBundle\Entity\Comment $comments
     */
    public function removeComment(\Cpt\BlogBundle\Entity\Comment $comments)
    {
        $this->comments->removeElement($comments);
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getComments()
    {
        return $this->comments;
    }
    
        /**
     * Set comments
     *
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    }
    
    /**
     * @var array
     */
    private $queue;

    /**
     * @var boolean
     */
    private $registration_allowed;


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
     * @var boolean
     */
    private $location_show_map;

    /**
     * @var string
     */
    private $location_long;

    /**
     * @var string
     */
    private $location_lat;


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