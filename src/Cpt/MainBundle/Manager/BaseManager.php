<?php
namespace Cpt\MainBundle\Manager;
use Doctrine\ORM\EntityManager as EntityManager;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Acl\Model\AclProviderInterface as AclProviderInterface;

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

    protected $securitycontext;
    
    protected $aclprovider;
    
    /**
     * @param \Doctrine\ORM\EntityManager $em
     * @param string                      $class
     */
    public function __construct(EntityManager $em, SecurityContextInterface $securityContext, AclProviderInterface $aclprovider, $class)
    {
        $this->em    = $em;
        $this->class = $class;
        $this->securitycontext = $securityContext;
        $this->aclprovider = $aclprovider;
    }
    
    public function getUser()
    {
        return $this->securitycontext->getToken()->getUser();
    }
    
    public function isUserAdmin()
    {
        return $this->securitycontext->isGranted('ROLE_ADMIN');
    }
    
    public function getSecurityContext()
    {
        return $this->securitycontext;
    }
    
    public function getAclProvider()
    {
        return $this->aclprovider;
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
        if (!$entity)
            throw new SymfonyException\NotFoundHttpException("Resource not found.");

        return $entity;
    }
    
    public function findOneBy(array $criteria)
    {
        $entity = $this->em->getRepository($this->class)->findOneBy($criteria);
        if (!$entity)
            throw new SymfonyException\NotFoundHttpException("Resource not found.");
        
        return $entity;
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
