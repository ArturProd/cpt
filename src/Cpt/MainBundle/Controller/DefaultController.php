<?php

namespace Cpt\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    public function indexAction()
    {
         
        $params = array(
            'article_permalink' => null,
            'event_permalink' => null,
            );
        
        return $this->render('CptMainBundle:Default:index.html.twig', $params );
    }
    
    public function showArticleAction($article_permalink)
    {
        $params = array(
            'article_permalink' => $article_permalink,
            'event_permalink' => null,
            );
        
        return $this->render('CptMainBundle:Default:index.html.twig', $params );
        
    }
    
}
