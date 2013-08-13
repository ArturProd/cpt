<?php

/*
 * This file is part of sonata-project.
 *
 * (c) 2010 Thomas Rabaix
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cpt\BlogBundle\Util;

use Cpt\BlogBundle\Model\CommentInterface;

interface HashGeneratorInterface
{
    /**
     * @param \Cpt\BlogBundle\Model\CommentInterface $comment
     *
     * @return string
     */
    public function generate(CommentInterface $comment);
}
