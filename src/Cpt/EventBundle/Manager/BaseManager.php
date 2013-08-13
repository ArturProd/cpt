<?php
namespace Cpt\EventBundle\Manager;


use Symfony\Component\DependencyInjection\ContainerAware;

abstract class BaseManager extends ContainerAware
{
    protected function persistAndFlush($entity)
    {

        $this->em->persist($entity);
        $this->em->flush();
        
                                         var_dump($entity);
                 exit(die);
    }
}

?>
