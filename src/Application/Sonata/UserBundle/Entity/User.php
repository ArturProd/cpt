<?php

/**
 * This file is part of the <name> project.
 *
 * (c) <yourname> <youremail>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Sonata\UserBundle\Entity;

use Sonata\UserBundle\Entity\BaseUser as BaseUser;

/**
 * This file has been generated by the Sonata EasyExtends bundle ( http://sonata-project.org/bundles/easy-extends )
 *
 * References :
 *   working with object : http://www.doctrine-project.org/projects/orm/2.0/docs/reference/working-with-objects/en
 *
 * @author <yourname> <youremail>
 */
class User extends BaseUser
{
    
    
    /**
     * @var integer $id
     */
    protected $id;
    
    protected $comments;
    
    protected $registrations;
    
    protected $events;
    
    public function getDisplayName()
    {
        return $this->getUsername() . ($this->getLastname() ? " ".$this->getLastname() : "");
    }
    
    public function getEvents() {
        return $this->events;
    }

    public function setEvents($events) {
        $this->events = $events;
    }

        public function getRegistrations() {
        return $this->registrations;
    }

    public function setRegistrations($registrations) {
        $this->registrations = $registrations;
    }
    
    public function addRegistration(\Cpt\EventBundle\Entity\Registration $registrations)
    {
        $this->registrations[] = $registrations;

        return $this;
    }

    public function removeRegistration(\Cpt\EventBundle\Entity\Registration $registrations)
    {
        $this->registrations->removeElement($registrations);
    }

    public function getComments()
    {
        return $this->comments;
    }
    
    public function setComments($comments)
    {
        $this->comments = $comments;
    }
    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function User()
    {
        parent::User();
        
        $this->comments = new Doctrine\Common\Collection\ArrayCollection();
    
    }
}