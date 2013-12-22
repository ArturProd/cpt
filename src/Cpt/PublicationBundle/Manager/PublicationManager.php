<?php

namespace Cpt\PublicationBundle\Manager;

use Cpt\PublicationBundle\Interfaces\Entity\PublicationInterface as PublicationInterface;
use Sonata\UserBundle\Model\UserInterface as UserInterface;
use Symfony\Component\Security\Core\SecurityContext as SecurityContext;
/**
 * Description of PublicationManager
 *
 * @author cyril
 */
class PublicationManager extends BaseManager {

       /*
     * Only logged in users can comment post
     */
    public function CanCommentPublication(PublicationInterface $publication, SecurityContext $security, UserInterface $user = null)
    {        
        if ((!$publication)||(!$security))
            return false;
        
        if (!$user)
            $user = $security->getToken()->getUser();
        
        if (!$publication->isCommentable())
            return false;
        
        if (!$security->isGranted('IS_AUTHENTICATED_REMEMBERED', $user))
            return false;
        
        return true;
    }
    
    
    public function CanSeePublication(PublicationInterface $publication, SecurityContext $security, UserInterface $user = null)
    {
        if (!$user)
            $user = $security->getToken()->getUser();
        
        $result =  $publication->isPublic()
                || ($publication->getAuthor()->getId() == $user-getID())
                || $security->isGranted('ROLE_ADMIN', $user);
        
        return $result;

    }
    
  
     
    public function CanModifyPublication(PublicationInterface $publication, SecurityContext $security)
    {
        if ((!$publication)||(!$security))
            return false;
        
        // Check if logged in user has admin rights
        if ($security->isGranted('ROLE_ADMIN'))
                return true;
        
        // Excepted admin, only publishers can modify a post
        // TODO: unless associated with an event, in that case event publisher can modify it too
        if (!$security->isGranted('ROLE_USER'))
                return false;
        
         
        // Checking logged in user is the author, in  the case the post exists in db
        $usr= $security->getToken()->getUser();
        if ($publication->getAuthor()->getId() == $usr->getId())
            return true;
        
       return false;
    }
            
    public function EnsureCanModifyPublication(PublicationInterface $publication, SecurityContext $security)
    {
        if (!$this->CanModifyPublication($publication,$security))
            throw new AccessDeniedException("You do not have the authorization to modify this article.");
    }
}

?>
