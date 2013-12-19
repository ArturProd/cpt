<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cpt\BlogBundle\Interfaces\Entity;

use Cpt\BlogBundle\Interfaces\Entity\CommentInterface as CommentInterface;

interface PostInterface
{
  
 
    /**
     * Add tags
     *
     * @param \Cpt\BlogBundle\Model\TagInterface $tags
     */
    //public function addTags(TagInterface $tags);

    /**
     * Get tags
     *
     * @return array $tags
     */
    //public function getTags();

    /**
     * @param $tags
     *
     * @return mixed
     */
    //public function setTags($tags);

    /**
     * @return string
     */
    public function getYear();

    /**
     * @return string
     */
    public function getMonth();

    /**
     * @return string
     */
    public function getDay();

    /**
     * Set comments_enabled
     *
     * @param boolean $commentsEnabled
     */
    public function setCommentsEnabled($commentsEnabled);

    /**
     * Get comments_enabled
     *
     * @return boolean $commentsEnabled
     */
    public function getCommentsEnabled();

    /**
     * Set comments_close_at
     *
     * @param \DateTime $commentsCloseAt
     */
    public function setCommentsCloseAt(\DateTime $commentsCloseAt = null);

    /**
     * Get comments_close_at
     *
     * @return \DateTime $commentsCloseAt
     */
    public function getCommentsCloseAt();

    /**
     * Set comments_default_status
     *
     * @param integer $commentsDefaultStatus
     */
    public function setCommentsDefaultStatus($commentsDefaultStatus);

    /**
     * Get comments_default_status
     *
     * @return integer $commentsDefaultStatus
     */
    public function getCommentsDefaultStatus();

    /**
     * Set comments_count
     *
     * @param integer $commentsDefaultStatus
     */
    public function setCommentsCount($commentscount);

    /**
     * Get comments_count
     *
     * @return integer $commentsCount
     */
    public function getCommentsCount();

    /**
     * @return boolean
     */
    public function isCommentable();

    /**
     * @return boolean
     */
    public function isPublic();

    /**
     * @param mixed $author
     *
     * @return mixed
     */
    public function setAuthor($author);

    /**
     * @return mixed
     */
    public function getAuthor();

  
}
