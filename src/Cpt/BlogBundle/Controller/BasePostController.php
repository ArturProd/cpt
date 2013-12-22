<?php

namespace Cpt\BlogBundle\Controller;
use Cpt\MainBundle\Controller\BaseController as BaseController;
use Cpt\BlogBundle\Interfaces\Entity\PostInterface as PostInterface;


/**
 * Description of BasePostController
 *
 * @author cyril
 */
class BasePostController  extends BaseController {
    
   
    
    protected function getPostById($id)
    {
        $post = $this->getPostManager()->findOneBy( array('id' => $id) );

        return $post;
    }
    
 }

?>
