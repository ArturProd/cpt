<?php
namespace Cpt\EventBundle\Manager;


use Symfony\Component\DependencyInjection\ContainerAware;
use Cpt\PublicationBundle\Manager\BaseManager as CptBaseManager;

abstract class BaseManager extends CptBaseManager
{
    function getCalendarManager()
    {
        return $this->container->get('cpt.calendar.manager');
    }
    
    function getRegistrationManager()
    {
        return $this->container->get('cpt.registration.manager');
    }
    
    function getEventManager()
    {
        return $this->container->get('cpt.event.manager');
    }
}

?>
