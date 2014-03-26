<?php

namespace Cpt\MainBundle\Manager;

use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Core\Exception\AccessDeniedException as AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException as NotFoundHttpException;

class PermissionsManager extends BaseManager {


    public function __construct(Container $container) {
        parent::__construct($container);
            }

            
    public function RestrictResourceNotFound($ressource = null) {
        if (!$ressource) {
            throw new NotFoundHttpException("Resource not found.");
        }
    }

    public function RestrictPageNotFound($message = "Page not found") {
        throw new NotFoundHttpException($message);
    }

    public function RestrictAccessToAjax($request) {
        if ($this->container->getParameter('kernel.environment') == "dev"){
            return;
        }
        
        if (!$request->isXmlHttpRequest()) {
             throw new AccessDeniedException("Access denied. Please request through Ajax");
            //throw new SymfonyException\ForbiddenHttpException("Ressource cannot be accessed this way.");    
        }
    }
    
    public function isLoggedIn()
    {
        return $this->getSecurityContext()->isGranted('ROLE_USER');
    }

    public function RestrictAccessToLoggedIn() {
        $this->RestrictAccessDenied($this->isLoggedIn());
    }

    public function RestrictAccessDenied($allow_access = false) {
        if (!$allow_access) {
            throw new AccessDeniedException();
        }
    }

    public function RestrictAccessToUser($userid, $authorizeAdmin = true) {
        $this->RestrictAccessToLoggedIn();

        if (($authorizeAdmin) && $this->getSecurityContext()->isGranted('ROLE_ADMIN')) {
            return;
        } else if ($userid == $this->getSecurityContext()->getToken()->getUser()->getId()) {
            return;
        }

        throw new SymfonyException\ForbiddenHttpException("Access denied.");
    }

    public function ensureGrantedPublication($permission, $domainobject=null) {
        if ($this->getSecurityContext()->isGranted($permission, $this->getPublicationClassIdentity())) {
            return;
        }
        
        $this->ensureGranted($permission, $domainobject);
    }

    public function ensureGrantedComment($permission, $domainobject=null) {
        if ($this->getSecurityContext()->isGranted($permission, $this->getCommentClassIdentity())) {
            return;
        }
        
        $this->ensureGranted($permission, $domainobject);
    }

    protected function ensureGranted($permission, $domainobject) {
        $objectIdentity = ObjectIdentity::fromDomainObject($domainobject);

        if (!$this->getSecurityContext()->isGranted($permission, $objectIdentity)){
            throw new AccessDeniedException();
        }
    }

    public function getPublicationClassIdentity() {
        return new ObjectIdentity('class', 'Cpt\\PublicationBundle\\Entity\\Publication');
    }

    public function getCommentClassIdentity() {
        return new ObjectIdentity('class', 'Cpt\\PublicationBundle\\Entity\\Comment');
    }

    public function isUserInRole($user, $role) {
        $roles = $this->GetReachableRoles($user);

        foreach ($roles as $oneRole) {
            if ($oneRole->getRole() === $role) {
                return true;
            }
        }

        return false;
    }

    public function setUserRole(UserInterface $user, $role) {
        $roles = Array(new Role($role));
        $user->setRoles($roles);

        $this->getUserManager()->updateUser($user);
    }

    public function GetUsersInRole($role) {
        $tmp_users = $this->getUserManager()->findUsers();

        $users = array();

        foreach ($tmp_users as $user) {
            if ($this->isUserInRole($user, $role)) {
                $users[] = $user;
            }
        }

        return $users;
    }

    private function GetReachableRoles($user) {
        $userRoles = array();
        foreach ($user->getRoles() as $role) {
            $userRoles[] = new Role($role);
        }

        return array_values($this->GetContainer()->get('security.role_hierarchy')->getReachableRoles($userRoles));
    }

}
