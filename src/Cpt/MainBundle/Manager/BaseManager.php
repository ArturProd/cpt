<?php
namespace Cpt\MainBundle\Manager;
use Doctrine\ORM\EntityManager as EntityManager;

abstract class BaseManager
{
    /**
     * @var string
     */
    protected $class;

        /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     * @param string                      $class
     */
    public function __construct(EntityManager $em, $class)
    {
        $this->em    = $em;
        $this->class = $class;
    }
    
    
    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        return $this->class;
    }
    
    public function getOneById($id)
    {
        $entity = $this->findOneBy( array('id' => $id) );

        return $entity;
    }
    
    public function findOneBy(array $criteria)
    {
        return $this->em->getRepository($this->class)->findOneBy($criteria);
    }
    
      /**
     * {@inheritDoc}
     */
    public function findBy(array $criteria)
    {
        return $this->em->getRepository($this->class)->findBy($criteria);
    }
}
?>
