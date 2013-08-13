<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cpt\BlogBundle\Entity;

use Cpt\BlogBundle\Model\Post as ModelPost;

abstract class BasePost extends ModelPost
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();

        $this->tags     = new \Doctrine\Common\Collections\ArrayCollection;
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection;
        $this->setAuthor(null);
        $this->setPublicationDateStart(new \DateTime);
        $this->setCategory();
        $this->setCommentsDefaultStatus(\Cpt\BlogBundle\Entity\Comment::STATUS_VALID);
        $this->setEnabled(true);
        $this->setContent("x");
        $this->setContentFormatter("x");
        $this->id = -1;
    }
}
