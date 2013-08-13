<?php

namespace Cpt\EventBundle\Entity;


class Registration
{
    

    
    public function __construct($user = null, $event = null, $numparticipant = 1, $organizer = false)
    {
        $this->user = $user;
        $this->event = $event;
        $this->numparticipant = $numparticipant;
        $this->organizer = $organizer;
        $this->numqueuedparticipant = 0;
        $this->id = -1;
    }

   public function prePersist()
    {
        $this->setCreatedAt(new \DateTime);
        $this->setUpdatedAt(new \DateTime);
    }
    
    public function preUpdate()
    {
        $this->setUpdatedAt(new \DateTime);
    }
    
    
    protected $user;
    
    protected $event;
        /**
     * Set event
     *
     * @param \Cpt\EventBundle\Entity\Event $event
     * @return Registration
     */
    public function setEvent(\Cpt\EventBundle\Entity\Event $event = null)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return \Cpt\EventBundle\Entity\Event 
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set user
     *
     * @param \Application\Sonata\UserBundle\Entity\User $user
     * @return Registration
     */
    public function setUser(\Application\Sonata\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Application\Sonata\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
    
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * Nombre TOTAL de participants
     * @var integer
     */
    private $numparticipant;

    /**
     * @var integer
     */
    private $numqueuedparticipant;

    /**
     * @var boolean
     */
    private $organizer;
    

    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return BaseRegistration
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
     * Set numparticipant
     *
     * @param integer $numparticipant
     * @return BaseRegistration
     */
    public function setNumparticipant($numparticipant)
    {
        $this->numparticipant = $numparticipant;

        return $this;
    }

    /**
     * Get numparticipant
     *
     * @return integer 
     */
    public function getNumparticipant()
    {
        return $this->numparticipant;
    }

    /**
     * Set numqueuedparticipant
     *
     * @param integer $numqueuedparticipant
     * @return BaseRegistration
     */
    public function setNumqueuedparticipant($numqueuedparticipant)
    {
        $this->numqueuedparticipant = $numqueuedparticipant;

        return $this;
    }

    /**
     * Get numqueuedparticipant
     *
     * @return integer 
     */
    public function getNumqueuedparticipant()
    {
        return $this->numqueuedparticipant;
    }
    /**
     * @var \DateTime
     */
    private $updatedAt;


    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return BaseRegistration
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
     * Set organizer
     *
     * @param boolean $organizer
     * @return BaseRegistration
     */
    public function setOrganizer($organizer)
    {
        $this->organizer = $organizer;

        return $this;
    }

    /**
     * Get organizer
     *
     * @return boolean 
     */
    public function getOrganizer()
    {
        return $this->organizer;
    }

}
