<?php

namespace Application\Sonata\UserBundle\Controller;

use FOS\UserBundle\Controller\SecurityController as BaseController;
use Symfony\Component\HttpFoundation\Response as Response;

use Symfony\Component\Security\Core\SecurityContext;

class SecurityController extends BaseController
{

    public function loginAction()
    {
        $request = $this->container->get('request');

        /**** The following is to handle ajax request that are made when the user is disconnected (for ex. after time out) ******/
        // if the request is an ajax request...
//        if ($request->isXmlHttpRequest()) {
// 
//        // response to the ajax request : code http 401 (access unauthorized)
//        $content = json_encode(array('message' => 'Cannot loging through ajax'));
//        return new Response($content, 301);
//        }
        /*************************************************************************************************************************/

        /* @var $request \Symfony\Component\HttpFoundation\Request */
        $session = $request->getSession();
        /* @var $session \Symfony\Component\HttpFoundation\Session */

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } elseif (null !== $session && $session->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = '';
        }

        if ($error) {
            // TODO: this is a potential security risk (see http://trac.symfony-project.org/ticket/9523)
            $error = $error->getMessage();
        }
        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get(SecurityContext::LAST_USERNAME);

        $csrfToken = $this->container->get('form.csrf_provider')->generateCsrfToken('authenticate');

        return $this->renderLogin(array(
            'last_username' => $lastUsername,
            'error'         => $error,
            'csrf_token' => $csrfToken,
        ));
    }
    
    protected function renderLogin(array $data)
    {
        $template = sprintf('SonataUserBundle:Security:login.html.%s', $this->container->getParameter('fos_user.template.engine'));

        return $this->container->get('templating')->renderResponse($template, $data);
    }
}