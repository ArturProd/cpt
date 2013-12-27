<?php
namespace Cpt\PublicationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;
use Cpt\PublicationBundle\Interfaces\Entity\CommentableInterface as CommentableInterface;
use Cpt\PublicationBundle\Interfaces\Entity\CommentInterface as CommentInterface;

class Commentable implements CommentableInterface
{


    public function __construct($commentsEnabled = true, $commentsCloseAt=null, $commentsDefaultStatus=CommentInterface::STATUS_VALID)
    {
        $this->canBeCommented = true;
        
        $this->commentsEnabled = $commentsEnabled;
        $this->comments = new ArrayCollection();
        $this->setCommentsDefaultStatus($commentsDefaultStatus);
        $this->setCommentsCloseAt($commentsCloseAt);
        $this->commentsCount = 0;
    }
    

    
    // <editor-fold defaultstate="collapsed" desc="attributes">
    protected $comments;    
    protected $commentsEnabled = true;
    protected $commentsCloseAt;
    protected $commentsDefaultStatus;
    protected $commentsCount = 0;
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="getters & setters">
     /**
     * {@inheritdoc}
     */
    public function addComments(CommentInterface $comment)
    {
        $this->comments[] = $comment;
        $comment->setCommentable($this);
    }

    /**
     * {@inheritdoc}
     */
    public function setComments($comments)
    {
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection;

        foreach ($comments as $comment) {
            $this->addComments($comment);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getComments()
    {
        return $this->comments;
    }
    
        /**
     * {@inheritdoc}
     */
    public function setCommentsEnabled($commentsEnabled)
    {
        $this->commentsEnabled = $commentsEnabled;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommentsEnabled()
    {
        return $this->commentsEnabled;
    }

    /**
     * {@inheritdoc}
     */
    public function setCommentsCloseAt(\DateTime $commentsCloseAt = null)
    {
        $this->commentsCloseAt = $commentsCloseAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommentsCloseAt()
    {
        return $this->commentsCloseAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setCommentsDefaultStatus($commentsDefaultStatus)
    {
        $this->commentsDefaultStatus = $commentsDefaultStatus;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommentsDefaultStatus()
    {
        return $this->commentsDefaultStatus;
    }

    /**
     * {@inheritdoc}
     */
    public function setCommentsCount($commentsCount)
    {
        $this->commentsCount = $commentsCount;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommentsCount()
    {
        return $this->commentsCount;
    }
        // </editor-fold>

}
?>
