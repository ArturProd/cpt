<?php

namespace Cpt\MainBundle\Controller;


class DefaultController extends BaseController
{
    
    public function aboutAction()
    {
       return $this->render('CptMainBundle:Default:about.html.twig');     
    }
        
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
    
    public function sendNewsLetterAction()
    {
        if (!$this->getParameter('cpt.newsletter.send'))
                return;
        $interval = $this->getParameter('cpt.newsletter.interval');
        
        $currentdate = new \DateTime();
        $fromdate = new \DateTime();
        $diffDays = new DateInterval($interval);        
        $fromdate->sub($diffDays);     
        
        
        $recipients = $this->getUserManager()->findNewsLetterRecipients();
        $posts = $this->getPostManager()->getPusblishedBetween($fromdate, $currentdate);
        $events = $this->getEventManager()->getPusblishedBetween($fromdate, $currentdate);

        $this->getMailManager()->sendNewsLetterEmail($content, $events, $posts, $recipients);
    }
    
}
