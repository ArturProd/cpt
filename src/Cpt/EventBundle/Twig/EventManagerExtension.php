<?php

/*
 * This file is part of sonata-project.
 *
 * (c) 2010 Thomas Rabaix
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cpt\EventBundle\Twig;

use Cpt\EventBundle\Interfaces\Entity\EventInterface as EventInterface;
use Cpt\EventBundle\Interfaces\Manager\EventManagerInterface as EventManagerInterface;
use Cpt\EventBundle\Interfaces\Manager\RegistrationManagerInterface as RegistrationManagerInterface;
use FOS\UserBundle\Model\UserInterface as UserInterface;


class EventManagerExtension extends \Twig_Extension
{

    private $eventmanager;
    private $registrationmanager;
    
    public function __construct(EventManagerInterface $eventmanager, RegistrationManagerInterface $registrationmanager)
    {
        $this->eventmanager = $eventmanager;
        $this->registrationmanager = $registrationmanager;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'cpt_event_isAuthorOrganizer'    => new \Twig_Function_Method($this, 'isAuthorOrganizer'),
            'cpt_event_isAuthorSingleOrganizer'    => new \Twig_Function_Method($this, 'isAuthorSingleOrganizer'),
            'cpt_event_isAuthor'    => new \Twig_Function_Method($this, 'isAuthor'),
            'cpt_event_isOrganizer'    => new \Twig_Function_Method($this, 'isOrganizer'),
            'cpt_event_isBeginEndSameDay'    => new \Twig_Function_Method($this, 'isBeginEndSameDay'),
            'cpt_event_getRegistration'    => new \Twig_Function_Method($this, 'getRegistration'),
            'cpt_event_hasMyEvent'    => new \Twig_Function_Method($this, 'hasMyEvent'),
        ); 
    }


    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'cpt_event_eventmanager';
    }


    public function hasMyEvent($eventcollection){
        foreach($eventcollection as $event){
            if ($event->isMyEvent()){
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * @param \Cpt\BlogBundle\Model\PostInterface $post
     *
     * @return string|Exception
     */
    public function isAuthorOrganizer(EventInterface $event)
    {
        return $this->registrationmanager->isCreatorAlsoAnimator($event);
    }
    
    
    public function isAuthorSingleOrganizer(EventInterface $event)
    {
        return $this->registrationmanager->isAuthorSingleOrganizer($event);
    }
    
    public function isAuthor(EventInterface $event, UserInterface $user)
    {
        return $this->registrationmanager->isAuthor($event,$user);
    }
    
    public function isOrganizer(EventInterface $event, UserInterface $user)
    {
        return $this->eventmanager->isOrganizer($event,$user);
    }
    
    public function getRegistration(EventInterface $event, UserInterface $user)
    {
        return $this->registrationmanager->getRegistration($event, $user);
    }
    
    public function isBeginEndSameDay(EventInterface $event)
    {
        if ($event->getEnd() == null){
            return true;
        }
        
        return ($event->getBegin()->diff($event->getEnd())->days == 0);
    }
}
