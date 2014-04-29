<?php

namespace Cpt\MainBundle\Manager;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;
use Cpt\MainBundle\Interfaces\Manager\MailManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

use FOS\UserBundle\Model\UserInterface;
use Cpt\EventBundle\Interfaces\Entity\RegistrationInterface;

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

    public function sendEventSubscriptionEmailMessage(RegistrationInterface $registration){
        $user = $registration->getUser();
        
        $template = 'CptMainBundle:Emails:subscribe_event_email.html.twig';
        
        $attachements = Array() ;
        $attachements[] = $this->getCptLogo();

        $rendered = $this->templating->render($template, array(
            'registration' => $registration
        ));
        
        $this->sendEmailMessage($rendered, $this->parameters['from_email']['confirmation'], $user->getEmail(), $attachements);
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