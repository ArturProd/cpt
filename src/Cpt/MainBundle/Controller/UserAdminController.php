<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserAdminController
 *
 * @author cyril
 */

namespace Cpt\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Symfony\Component\Security\Core\Role\Role;

class UserAdminController extends Controller {
    
    public function indexAction()
    {
        $tmp_users = $this->container->get('fos_user.user_manager')->findUsers();
        $permissions = array();
        $users = array();
        
        foreach ($tmp_users as $user)
        {
            // TODO: This DOES NOT WORK!!! However this is used in the "Comment" permission for post, need to check this out!
//           $evtcreator = $this->isGranted('ROLE_EVT_CREATOR', $user);
//           $publisher = $this->isGranted('ROLE_PUBLISHER', $user);
//           $orga = $this->isGranted('ROLE_ORGA', $user);
//           $admin = $this->isGranted('ROLE_ADMIN', $user);
           
           $user_roles = $user->getRoles();

           // Getting all reachable roles for this user
           $userRoles = array();
           foreach($user->getRoles() as $role)
               $userRoles[] = new Role($role);
           $reachableroles = array_values($this->get('security.role_hierarchy')->getReachableRoles($userRoles));
           
           $highest_role = "ROLE_USER";
           
           // SUPER_ADMIN cannot be managed by this page
           if (in_array("ROLE_SUPER_ADMIN",$user_roles))
                   continue;           
           
           // Retreiving the highest role in the hierarchy for this user
           if (in_array("ROLE_ADMIN",$user_roles))
                   $highest_role = "ROLE_ADMIN";
           
           $permissions[$user->getId()] = $highest_role;
           $users[] = $user;
        }

        $params = array("users" => $users, "permissions" => $permissions);
        
        return $this->render('CptMainBundle:Admin:userlist.html.twig', $params );
    }
    
    
}

?>
