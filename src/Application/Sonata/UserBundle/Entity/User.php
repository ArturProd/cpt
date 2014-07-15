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


use JMS\Serializer\Annotation\ExclusionPolicy as ExclusionPolicy;
use JMS\Serializer\Annotation\Expose as Expose;
use JMS\Serializer\Annotation\VirtualProperty as VirtualProperty;
use JMS\Serializer\Annotation\SerializedName as SerializedName;


/**
 * This file has been generated by the Sonata EasyExtends bundle ( http://sonata-project.org/bundles/easy-extends )
 *
 * References :
 *   working with object : http://www.doctrine-project.org/projects/orm/2.0/docs/reference/working-with-objects/en
 *
 * @author <yourname> <youremail>
 */
/**
 * @ExclusionPolicy("all")
 */
class User extends BaseUser
{
    
    
    /**
     * @var integer $id
    */
    protected $id;
    
    protected $comments;
    
    protected $registrations;
    
    protected $publications;
    
    protected $options;
    
    protected $country_code;
    
    protected $city_name;
    
    protected $professional = false;
    
    protected $pro_job = "";
    
    protected $pro_resume = "";
    
    public function setEmail($email)
    {
        $email = is_null($email) ? '' : $email;
        parent::setEmail($email);
        $this->setUsername($email);
    }
    
    // <editor-fold defaultstate="collapsed" desc="Getters & Setters - Pro">

    public function getProJob() {
        return $this->pro_job;
    }
    
    public function setProJob($pro_job) {
        $this->pro_job = $pro_job;
    }

    public function getProResume() {
        return $this->pro_resume;
    }
    
    public function setProResume($pro_resume) {
        $this->pro_resume = $pro_resume;
    }

    // </editor-fold>

    
    public function getProfessional() {
        return $this->professional;
    }
    
    public function setProfessional($professional) {
        $this->professional = $professional;
    }
    
    
    public function getCountryCode() {
        return $this->country_code;
    }

    public function getCityName() {
        return $this->city_name;
    }

    public function setCountryCode($country_code) {
        $this->country_code = $country_code;
    }

    public function setCityName($city_name) {
        $this->city_name = $city_name;
    }

        
    public function getDisplayName()
    {
        return ucwords($this->getFirstname() . " " . $this->getLastname());
    }
    
    public function getPublications() {
        return $this->publications;
    }

    public function setPublications($publications) {
        $this->publications = $publications;
    }

    public function getRegistrations() {
        return $this->registrations;
    }

    public function setRegistrations($registrations) {
        $this->registrations = $registrations;
    }
    
    public function getOptions() {
        return $this->options;
    }

    public function setOptions($options) {
        $this->options = $options;
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
        $this->publications = new Doctrine\Common\Collection\ArrayCollection();
        $this->registrations = new Doctrine\Common\Collection\ArrayCollection();
        $this->options = new UserOption();
    }
}