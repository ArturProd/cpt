<?php
// src/Cpt/MainBundle/Entity/User.php

namespace Cpt\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_test")
 */
class Test
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /** @var boolean $totodata
    *
    * @ORM\Column(name="totodata", type="boolean")
    */
    protected $totodata;
    
       /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Get Totodata
     *
     * @return boolean $totodata
     */
    public function getTotodata()
    {
        return $this->totodata;
    }
    
    /**
     * Get Totodata
     *
     * @param bool $pTotoData
     */
    public function setTotodata($pTotoData)
    {
        $this->totodata = $pTotoData;
    }
    
    public function __construct()
    {
        $this->totodata     = false;
    }

}