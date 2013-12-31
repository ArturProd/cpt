<?php
namespace Cpt\EventBundle\Manager;


use Symfony\Component\DependencyInjection\ContainerAware;
use Cpt\PublicationBundle\Manager\BaseManager as CptBaseManager;

abstract class BaseManager extends CptBaseManager
{
    protected function getCalendarManager()
    {
        return $this->container->get('cpt.calendar.manager');
    }
    
    protected function getRegistrationManager()
    {
        return $this->container->get('cpt.registration.manager');
    }
    
    protected function getEventManager()
    {
        return $this->container->get('cpt.event.manager');
    }
    
    /**
     *  Add query part to only select future events
     * 
     * @param type $querybuilder
     */
    protected function getFutureEventQueryPart($querybuilder){            
        $querybuilder->AndWhere('(e.end > :now_forfutureevent) OR (e.begin > :now_forfutureevent)')
            ->setParameter('now_forfutureevent', new \DateTime());            
    }
    
    /**
     * Add query part to only select past events
     * 
     * @param type $querybuilder
     */
    protected function getPastEventQueryPart($querybuilder){
        $querybuilder->AndWhere('e.end < :now_forpastevent')
                    ->setParameter('now_forpastevent', new \DateTime());
    }
            
}
