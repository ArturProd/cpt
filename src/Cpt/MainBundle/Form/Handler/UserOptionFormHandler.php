<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cpt\MainBundle\Form\Handler;

use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Mailer\MailerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class UserOptionFormHandler
{
    protected $requestStack;
    protected $userManager;
    protected $form;
    protected $mailer;
    protected $tokenGenerator;

    public function __construct(FormInterface $form, RequestStack $requestStack, UserManagerInterface $userManager, MailerInterface $mailer)
    {
        $this->form = $form;
        $this->requestStack = $requestStack;
        $this->userManager = $userManager;
        $this->mailer = $mailer;
    }


    public function process(UserInterface $user)
    {
        $request = $this->requestStack->getCurrentRequest();
        $useroptions = $user->getOptions();
        
        $this->form->setData($useroptions);
        
       // $useroptionform = $this->get('form.factory')->createNamed('useroption', 'cpt.useroption.form.type', $useroptions, Array('attr' => Array('id' => 'useroptionform')));

        if ($request->isMethod('POST')) {
            $this->form->bind($request);
            if ($this->form->isValid()) {
                $this->userManager->updateUser($user);
                return true;
                
            }            
        } 
        
        return false;
    }
    
  
}
