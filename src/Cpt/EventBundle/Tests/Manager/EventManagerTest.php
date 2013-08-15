<?php
namespace Cpt\EventBundle\Tests\Manager;

use Cpt\EventBundle\Manager\EventManager;
use \Cpt\EventBundle\Tests\BaseTestCase as BaseTestCase;

class EventManagerTest extends BaseTestCase
{
    public function testCreateEvent()
    {
                
        $eventmanager = new EventManager($this->em);
        
        $author = $this->getMock('\Application\Sonata\UserBundle\Entity\User');
        $author->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(666));

        $event = $eventmanager->createEvent($author);
                
        $this->assertInstanceOf("\Cpt\EventBundle\Entity\Event", $event, "Returned object is not an event");
        $this->assertInstanceOf("\Application\Sonata\UserBundle\Entity\User", $event->getAuthor(), "Author is not a user");
        $this->assertInstanceOf("\Cpt\EventBundle\Entity\Registration", $event->getRegistrations()[0], "There is no default registration");
        $this->assertSame($event->getAuthor()->getId(), $author->getId(), "Event author is not the provided user");
        $this->assertSame($event->getRegistrations()[0]->getUser()->getId(), $author->getId(), "Event registration user is not the provided user");
        $this->assertSame($event->getRegistrations()[0]->getEvent()->getId(), $event->getId(), "Registration event is not the created event");
        $this->assertSame($event->getRegistrations()[0]->getOrganizer(), true, "Event default registration must be Organizer");
    }
    
   
}
?>
