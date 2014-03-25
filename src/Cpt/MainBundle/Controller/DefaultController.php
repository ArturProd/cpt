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
    
    /**
     *  Shows a single article
     * 
     * @param type $article_permalink
     * @return type
     */
    public function showArticleAction($article_permalink)
    {
        $params = array(
            'article_permalink' => $article_permalink,
            'event_permalink' => null,
            );
        
        return $this->render('CptMainBundle:Default:index.html.twig', $params );
        
    }
    
     /**
     *  Shows a single event
     * 
     * @param type $article_permalink
     * @return type
     */
    public function showEventAction($event_permalink)
    {
        $params = array(
            'article_permalink' => null,
            'event_permalink' => $event_permalink,
            );
        
        return $this->render('CptMainBundle:Default:index.html.twig', $params );
        
    }
    
}
