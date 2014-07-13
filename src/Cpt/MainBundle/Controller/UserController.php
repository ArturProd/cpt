<?php

namespace Cpt\MainBundle\Controller;


class UserController extends BaseController
{
    public function showProfileAction($userid)
    {
        $this->getPermissionManager()->RestrictAccessToLoggedIn();
        
        $userform = $this->get('form.factory')->createNamed('userform', 'cpt_form_user', null, Array('attr' => Array('id' => 'userform')));
                    
        $user = $this->getUserManager()->findUserBy(Array("id" => $userid));
        
        // Show only for existing, enabled users
        $this->getPermissionManager()->RestrictResourceNotFound($user);
        if (!$user->isEnabled()){
            $this->getPermissionManager()->RestrictResourceNotFound($user);           
        }
        
        $myarticles = $this->getPostManager()->getPublishedArticles($user->getId());
        $myevents = $this->getEventManager()->getPublishedEvents($user->getId());

        $params = array(
            'userform' => $userform->createView(),
            'user' => $user,
            'articles' => $myarticles,
            'events' => $myevents
            );
        
        return $this->render('CptMainBundle:User:profile_show.html.twig', $params );
    }
}