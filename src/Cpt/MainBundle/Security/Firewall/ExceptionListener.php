<?php

namespace Cpt\MainBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Firewall\ExceptionListener as BaseExceptionListener;

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