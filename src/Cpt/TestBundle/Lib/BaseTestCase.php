<?php

namespace Cpt\TestBundle\Lib;

class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    protected $em;
    
    public function setUp()
    {
        $this->em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->em
            ->expects($this->any())
            ->method('persist')
            ->will($this->returnValue(true));
        $this->em
            ->expects($this->any())
            ->method('flush')
            ->will($this->returnValue(true));
    }

public function tearDown()
{
    $this->em       = null;
}

}
?>
