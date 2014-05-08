<?php

namespace Cpt\MainBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphObject;
use Facebook\FacebookRequestException;


/**
 * Description of FacebookManager
 *
 * @author cyril
 */
class FacebookManager extends BaseManager {
    
    protected $facebooksession;
    
    public function __construct(Container $container) {
        parent::__construct($container);
        
    }
            
    public function PublishToFacebook($publication){
        $this->GetFacebookSession();
        
        if($this->facebooksession) {

          try {

            $response = (new FacebookRequest(
              $this->facebooksession, 'POST', '/me/feed', array(
                'link' => 'www.example.com',
                'message' => 'User provided message'
              )
            ))->execute()->getGraphObject();

            echo "Posted with id: " . $response->getProperty('id');

          } catch(FacebookRequestException $e) {

            echo "Exception occured, code: " . $e->getCode();
            echo " with message: " . $e->getMessage();

          }   

        }

    }
    
    public function GetFacebookSession()
    {
        FacebookSession::setDefaultApplication('1460184204218456', '64d6da69398ffa21119324edb406aef6');

        // If you're making app-level requests:
        // 
        // $this->facebooksession = FacebookSession::newAppSession();
        $this->facebooksession  = new FacebookSession('1460184204218456|_d9xQLRiprK5dF7yhLb7PMY2-zU');
        $this->facebooksession->validate();


    }
}
