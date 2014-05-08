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
    
    public function postFacebookAction()
    {
        $this->getFacebookManager()->PublishToFacebook('');
    }
    
    public function sendNewsLetterAction()
    {
        if (!$this->container->getParameter('cpt.newsletter.send'))
                return;
        
        $intervalevent = $this->container->getParameter('cpt.newsletter.interval.event');
        $intervalpost = $this->container->getParameter('cpt.newsletter.interval.post');

        $currentdate = new \DateTime();
        $fromdate_post = new \DateTime();
        $todate_event = new \DateTime();
        
        $diffDaysEvent = new \DateInterval($intervalevent);
        $diffDaysPost = new \DateInterval($intervalpost);

        $fromdate_post->sub($diffDaysPost);     
        $todate_event->add($diffDaysEvent);
        
        $content = "test contenu. test contenu. test contenu. test contenu. test contenu. test contenu. test contenu. test contenu. test contenu. ";
        $topic = "Sujet de la newsletter";
        
        $recipients = $this->getUserManager()->findNewsLetterRecipients();
        $prousers = $this->getUserManager()->findNewsLetterProUsers();
        $posts = $this->getPostManager()->getPusblishedBetween($fromdate_post, $currentdate);
        $events = $this->getEventManager()->getNewsLetterEvents($currentdate, $todate_event);

        $registrationarray = Array(Array());
        foreach($events as $event){
            foreach($event->getRegistrations() as $registration){
               $registrationarray[$event->getId()][$registration->getUser()->getId()] = $registration; 
            }
        }
        
        $this->getMailManager()->sendNewsLetterEmail($topic, $content, $events, $posts, $registrationarray, $prousers, $recipients);
        
                $params = array(
            'article_permalink' => null,
            'event_permalink' => null,
            );
                
                return $this->render('CptMainBundle:Default:index.html.twig', $params );

    }
    
}
