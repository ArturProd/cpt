<?php

namespace Cpt\MainBundle\Manager;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;
use Cpt\MainBundle\Interfaces\Manager\MailManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

use FOS\UserBundle\Model\UserInterface;
use Cpt\EventBundle\Interfaces\Entity\RegistrationInterface;
use Cpt\EventBundle\Interfaces\Entity\EventInterface;
use Cpt\PublicationBundle\Interfaces\Entity\CommentInterface;

/**
 * Description of MailManager
 *
 * @author cyril
 */
class MailManager extends BaseManager implements MailManagerInterface
{
    protected $mailer;
    protected $router;
    protected $templating;
    protected $parameters;

    public function __construct(Container $container, $mailer, RouterInterface $router, EngineInterface $templating, array $parameters)
    {
        parent::__construct($container);
        $this->mailer = $mailer;
        $this->router = $router;
        $this->templating = $templating;
        $this->parameters = $parameters;
    }

    /**
     * Email after sign-up to website
     * 
     * @param \FOS\UserBundle\Model\UserInterface $user
     */
    public function sendConfirmationEmailMessage(UserInterface $user)
    {
        $template = $this->parameters['confirmation.template'];
        $url = $this->router->generate('fos_user_registration_confirm', array('token' => $user->getConfirmationToken()), true);
        
        $attachements = Array() ;
        $attachements[] = $this->getCptLogo();

        $rendered = $this->templating->render($template, array(
            'user' => $user,
            'confirmationUrl' =>  $url
        ));
        $this->sendEmailMessage($rendered, $this->parameters['from_email']['confirmation'], $user->getEmail(), $attachements);
    }

    /**
     * 
     * Email for resetting password
     * 
     * @param \FOS\UserBundle\Model\UserInterface $user
     */
    public function sendResettingEmailMessage(UserInterface $user)
    {
        $template = $this->parameters['resetting.template'];
        $url = $this->router->generate('fos_user_resetting_reset', array('token' => $user->getConfirmationToken()), true);
        $rendered = $this->templating->render($template, array(
            'user' => $user,
            'confirmationUrl' => $url
        ));
        $this->sendEmailMessage($rendered, $this->parameters['from_email']['resetting'], $user->getEmail());
    }

    /**
     * 
     * Email when registering to a new event (or creating an event, or being leader for an event)
     * 
     * @param \Cpt\EventBundle\Interfaces\Entity\RegistrationInterface $registration
     */
    public function sendEventSubscriptionEmailMessage(RegistrationInterface $registration){
        $user = $registration->getUser();
        
        $template = 'CptMainBundle:Emails:registration_register_email.html.twig';
        
        $attachements = Array() ;
        $attachements[] = $this->getCptLogo();

        $rendered = $this->templating->render($template, array(
            'registration' => $registration
        ));
        
        $this->sendEmailMessage($rendered, $this->parameters['from_email']['confirmation'], $user->getEmail(), $attachements);
    }
    
    /**
     * Email when canelling the regitration to an event
     * 
     * @param \Cpt\EventBundle\Interfaces\Entity\EventInterface $event
     * @param \FOS\UserBundle\Model\UserInterface $user
     */
    public function sendEventCancelRegistrationEmailMessage(EventInterface $event, UserInterface $user){
       
        $template = 'CptMainBundle:Emails:registration_cancel_email.html.twig';
        
        $attachements = Array() ;
        $attachements[] = $this->getCptLogo();

        $rendered = $this->templating->render($template, array(
            'event' => $event,
            'user' => $user
        ));
        
        $this->sendEmailMessage($rendered, $this->parameters['from_email']['confirmation'], $user->getEmail(), $attachements);
    }
    
    /**
     * 
     * Email when an event is cancelled
     * 
     * @param \Cpt\EventBundle\Interfaces\Entity\EventInterface $event
     * @param \FOS\UserBundle\Model\UserInterface $user
     */
    public function sendEventCancelledEmailMessage(EventInterface $event, UserInterface $user){
       
        $template = 'CptMainBundle:Emails:event_cancelled_email.html.twig';
        
        $attachements = Array() ;
        $attachements[] = $this->getCptLogo();

        $rendered = $this->templating->render($template, array(
            'event' => $event,
            'user' => $user
        ));
        
        $this->sendEmailMessage($rendered, $this->parameters['from_email']['confirmation'], $user->getEmail(), $attachements);
    }
    
    /**
     * 
     * Email when a new comment is received
     * 
     * @param \Cpt\MainBundle\Manager\CommentInterface $user
     */
    public function sendPublicationNewCommentEmailMessage(CommentInterface $comment){
       
        $template = 'CptMainBundle:Emails:comment_new_email.html.twig';
        
        $attachements = Array() ;
        $attachements[] = $this->getCptLogo();

        $rendered = $this->templating->render($template, array(
            'comment' => $comment,
        ));

        $receiveremail = $comment->getPublication()->getAuthor()->getEmail();
        
        $this->sendEmailMessage($rendered, $this->parameters['from_email']['confirmation'], $receiveremail, $attachements);
    }
    
     /**
     * 
     * Email when a new comment is received
     * 
     * @param \Cpt\MainBundle\Manager\CommentInterface $user
     */
    public function sendNewsLetterEmail($topic, $content, $events, $posts, $registrationarray, $prousers, $recipients){
       
        $template = 'CptMainBundle:Emails:newsletter_email.html.twig';
        
        $attachements = Array() ;
        $attachements[] = $this->getCptLogo();

        // Do not send newsletter if no content!
        if (empty($content) && (count($events) == 0) && (count($posts) == 0)){
            return false;
        }
        
        foreach($recipients as $user){
            $rendered = $this->templating->render($template, array(
                'content' => $content,
                'posts' => $posts,
                'events' => $events,
                'topic' => $topic,
                'user' => $user,
                'prousers' => $prousers,
                'registrationarray' => $registrationarray,
            ));
                    
            $this->sendEmailMessage($rendered, $this->parameters['from_email']['confirmation'], $user->getEmail(), $attachements);
        }
        
        return true;
    }
    
    /**
     * @param string $renderedTemplate
     * @param string $toEmail
     */
    protected function sendEmailMessage($renderedTemplate, $fromEmail, $toEmail, $attachements = Array() )
    {
        // Render the email, use the first line as the subject, and the rest as the body
        $renderedLines = explode("\n", trim($renderedTemplate));
        $subject = $renderedLines[0];
        $body = implode("\n", array_slice($renderedLines, 1));

        
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($toEmail);
            
        // Attach asset from $attachment and replace attachmenturl[ .... ] by the url in the body message
        $count = 0;
        foreach ($attachements as $path){
            $url = $message->embed(\Swift_Image::fromPath($path)); 
            $body = str_replace ("attachmenturl[".$count."]" , $url  ,$body);
            ++$count;
        }
        
        $message->setBody($body, "text/html");
        
        $this->mailer->send($message);
    }
    
    protected function getCptLogo()
    {
       return $this->container->get('kernel')->locateResource('@CptMainBundle/Resources/views/Emails/logo-email.png');
    }
}