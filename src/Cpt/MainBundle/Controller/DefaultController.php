<?php

namespace Cpt\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction()
    {
      
        // The url to get a raw html article. The url is made to be included in the javascript.
      //  $ajax_article_load_url = \str_replace("____", "'+postid+'", "'".$this->generateUrl('cpt_blog_post_getpreview_plainhtml', array( 'id' => '____' ))."'");

        $articles_alaune_array = Array();
        $articles_array = Array();
        
        // Get the homepage "a la une" articles   
        $pager = $this->getPostManager()->getHomePager(true, 1, 10);
        $posts = $pager->getResults();
   
        foreach($posts as $post)
            $articles_alaune_array[] = $post->getId();

        
        // Get the homepage other articles
         $pager = $this->getPostManager()->getHomePager(false, 1, 10);
         $posts = $pager->getResults();
  
         $logger = new \Doctrine\DBAL\Logging\DebugStack();
         
         foreach($posts as $post)
             $articles_array[] = $post->getId();
         
         
        $params = array(
            'pager' => $pager,
            'articles_alaune_array' => json_encode($articles_alaune_array),
            'articles_array' =>  json_encode($articles_array),
           // 'ajax_article_load_url' => $ajax_article_load_url,
            );
        
        return $this->render('CptMainBundle:Default:index.html.twig', $params );
    }
    
 
    
    public function maquetteAction()
    {
        
        return $this->render('CptMainBundle:Default:maquette.html.twig' );
    }
    
    protected function getPostManager()
    {
        return $this->get('cpt.blog.manager.post');
    }
    
    
}
