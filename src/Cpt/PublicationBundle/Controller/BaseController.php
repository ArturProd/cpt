<?php

namespace Cpt\PublicationBundle\Controller;

use Cpt\MainBundle\Controller\BaseController as CptBaseController;


/**
 * Description of BasePostController
 *
 * @author cyril
 */
class BaseController  extends CptBaseController {
    
    protected function CanSeePost(PostInterface $post)
    {
        $user= $this->get('security.context')->getToken()->getUser();

        $result =  $post->isPublic()
                || $post->getAuthor()->getId()
                || $this->get('security.context')->isGranted('ROLE_ADMIN', $user);
        
        return $result;

    }
    
       /*
     * Only logged in users can comment post
     */
    protected function CanCommentPost($post, $user)
    {        
        if (!$post)
            return false;
        
        if (!$user)
            return false;
        
        if (!$post->isCommentable())
            return false;
        
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED', $user))
            return false;
        
        return true;
    }
    
     
    protected function CanModifyPost($post_id)
    {
         $usr= $this->get('security.context')->getToken()->getUser();
        
        // Check if logged in user has admin rights
        if ($this->get('security.context')->isGranted('ROLE_ADMIN'))
                return true;
        
        // Excepted admin, only publishers can modify a post
        // TODO: unless associated with an event, in that case event publisher can modify it too
        if (!$this->get('security.context')->isGranted('ROLE_USER'))
                return false;
        
         // Retreiving a post from database (to check that it exists)
         $existing_post = $this->getPostManager()->findOneBy( array('id' => $post_id) );
         
        // Checking logged in user is the author, in  the case the post exists in db
        if ($existing_post && ($existing_post->getAuthor()->getId() == $usr->getId()))
            return true;
        
       return false;
    }
            
    protected function EnsureCanModifyPost($post_id)
    {
        if (!$this->CanModifyPost($post_id))
            throw new AccessDeniedException("You do not have the authorization to modify this article.");
    }
    
 }

?>
