<?php


namespace Cpt\MainBundle\Manager;

use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

class PermissionsManager 
{
    private $userManager;
    
    private $service_container;
    
    public function __construct(Container $container) {
        $this->service_container = $container;
        $this->userManager =  $this->service_container->get('fos_user.user_manager');
    }
    
    public function isUserInRole($user, $role)
    {
        $roles = $this->GetReachableRoles($user);
        
        foreach( $roles as $oneRole)
            if ($oneRole->getRole() === $role)
                    return true;
          
        return false;
    }
    
    public function setUserRole(UserInterface $user, $role)
    {
        $roles = Array(new Role($role));
        $user->setRoles($roles);
        
        $this->userManager->updateUser($user);
    }
    
    public function GetUsersInRole($role)
    {
        $tmp_users = $this->userManager->findUsers();
        
        $users = array();
        
        foreach ($tmp_users as $user)
        {
           if ($this->isUserInRole($user, $role))
            $users[] = $user;
        }
        
        return $users;
    }

    
    private function GetReachableRoles($user)
    {
        $userRoles = array();
        foreach($user->getRoles() as $role)
            $userRoles[] = new Role($role);
        
        return array_values($this->service_container->get('security.role_hierarchy')->getReachableRoles($userRoles));
    }
}
?>
