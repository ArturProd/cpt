<?php

namespace Cpt\PublicationBundle\Interfaces\Entity;

/**
 * Description of CommentableInterface
 *
 * @author cyril
 */
interface CommentableInterface {

    public function addComments(CommentInterface $comments);

    public function setComments($comments);

    public function getComments();
    
    public function setCommentsEnabled($commentsEnabled);
    
    public function getCommentsEnabled();
    
    public function setCommentsCloseAt(\DateTime $commentsCloseAt = null);
    
    public function getCommentsCloseAt();
   
    public function setCommentsDefaultStatus($commentsDefaultStatus);
    
    public function getCommentsDefaultStatus();
    
    public function setCommentsCount($commentsCount);
    
    public function getCommentsCount();

}

?>
