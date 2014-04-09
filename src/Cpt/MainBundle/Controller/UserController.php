<?php

namespace Cpt\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class UserController extends Controller
{
    public function showProfileAction($userid)
    {
        $userform = $this->get('form.factory')->createNamed('userform', 'cpt_form_user', null, Array('attr' => Array('id' => 'userform')));

        $params = array(
            'userform' => $userform->createView()
            );
        
        return $this->render('CptMainBundle:User:profile_show.html.twig', $params );
    }
}