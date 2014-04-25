<?php

namespace Cpt\MainBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Firewall\ExceptionListener as BaseExceptionListener;

/**
 * Fixes the bug of redirection to the last ajax request after login => this was because the TargetPath in the session was set to the last request, wether it was ajax or not
 */
class ExceptionListener extends BaseExceptionListener
{
    protected function setTargetPath(Request $request)
    {
        // Ne conservez pas de chemin cible pour les requêtes XHR et non-GET
        // Vous pouvez ajouter n'importe quelle logique supplémentaire ici
        // si vous le voulez
        if ($request->isXmlHttpRequest() || 'GET' !== $request->getMethod()) {
            return;
        }

        $request->getSession()->set('_security.target_path', $request->getUri());
    }
}