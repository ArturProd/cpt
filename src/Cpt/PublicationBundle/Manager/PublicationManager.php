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
    protected function CanCommentPublication(PublicationInterface $publication, UserInterface $user, SecurityContext $security)
    {        
        if (!$publication)
            return false;
        
        if (!$user)
            return false;
        
        if (!$publication->isCommentable())
            return false;
        
        if (!$security->isGranted('IS_AUTHENTICATED_REMEMBERED', $user))
            return false;
        
        return true;
    }
}

?>
