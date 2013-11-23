<?php

namespace Cpt\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

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
    
 
    
    protected function getPostManager()
    {
        return $this->get('cpt.blog.manager.post');
    }
    
    
}
