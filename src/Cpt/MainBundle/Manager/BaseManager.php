<?php

namespace Cpt\MainBundle\Manager;

use Symfony\Component\DependencyInjection\Container as Container;
use Cpt\MainBundle\Manager\PermissionsManager as PermissionManager;

abstract class BaseManager {

    /**
     * @var string
     */
    protected $class;
    protected $em;
    protected $container;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     * @param string                      $class
     */
    public function __construct(Container $container, $class = null) { //EntityManager $em, SecurityContextInterface $securityContext, AclProviderInterface $aclprovider, $class)
        $this->class = $class;
        $this->container = $container;
        $this->em = $this->GetEntityManager();
    }

    
    public function getSecurityContext() {
        return $this->GetContainer()->get('security.context');
    }

    public function getAclProvider() {
        return $this->GetContainer()->get('security.acl.provider');
    }

    public function GetEntityManager() {
        return $this->GetContainer()->get('cpt.entity_manager');
    }

    public function getUserManager() {
        return $this->GetContainer()->get('fos_user.user_manager');
    }

    public function getMailManager(){
        return $this->GetContainer()->get('cpt.mailer.manager');
    }
            
    /**
     * 
     * @return PermissionManager
     */
    public function getPermissionManager() {
        return $this->GetContainer()->get('cpt.permission.manager');
    }

    protected function getEventRepository() {
        return $this->em->getRepository('CptEventBundle:Event');
    }
    
    protected function getCountryRepository() {
        return $this->em->getRepository('CptEventBundle:Country');
    }

    protected function getRegistrationRepository() {
        return $this->em->getRepository('CptEventBundle:Registration');
    }

    protected function getUserRepository() {
        return $this->em->getRepository('ApplicationSonataUserBundle:User');
    }

    /**
     * {@inheritDoc}
     */
    public function getClass() {
        return $this->class;
    }

    public function getUser() {
        return $this->getSecurityContext()->getToken()->getUser();
    }

    public function isUserAdmin() {
        return $this->getSecurityContext()->isGranted('ROLE_ADMIN');
    }

    public function getOneById($id) {
        $entity = $this->findOneBy(array('id' => $id));

        $this->getPermissionManager()->RestrictResourceNotFound($entity);

        return $entity;
    }

    public function findOneBy(array $criteria) {
        $entity = $this->em->getRepository($this->class)->findOneBy($criteria);

        $this->getPermissionManager()->RestrictResourceNotFound($entity);

        return $entity;
    }

    /**
     * {@inheritDoc}
     */
    public function findBy(array $criteria) {
        return $this->em->getRepository($this->class)->findBy($criteria);
    }

    protected function GetContainer() {
        return $this->container;
    }

}
