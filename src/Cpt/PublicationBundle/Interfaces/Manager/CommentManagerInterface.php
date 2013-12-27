<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cpt\PublicationBundle\Interfaces\Manager;

use Cpt\PublicationBundle\Interfaces\Entity\CommentInterface as CommentInterface;
use Cpt\PublicationBundle\Interfaces\Entity\PublicationInterface as PublicationInterface;
use FOS\UserBundle\Model\UserInterface as UserInterface;

interface CommentManagerInterface
{
    /**
     * Creates an empty comment instance
     *
     * @return Comment
     */
    public function create(PublicationInterface $publication, UserInterface $author);

    /**
     * Deletes a comment
     *
     * @param CommentInterface $comment
     *
     * @return void
     */
    public function delete(CommentInterface $comment);

    /**
     * Finds one comment by the given criteria
     *
     * @param array $criteria
     *
     * @return CommentInterface
     */
    public function findOneBy(array $criteria);

    /**
     * Finds one comment by the given criteria
     *
     * @param array $criteria
     *
     * @return CommentInterface
     */
    public function findBy(array $criteria);

    /**
     * Returns the comment's fully qualified class name
     *
     * @return string
     */
    public function getClass();

    /**
     * Save a post
     *
     * @param CommentInterface $comment
     *
     * @return void
     */
    public function save(CommentInterface $comment);

    /**
     * Update the number of comment for a comment
     *
     * @return void
     */
    public function updateCommentsCount(PublicationInterface $publication = null);
}
