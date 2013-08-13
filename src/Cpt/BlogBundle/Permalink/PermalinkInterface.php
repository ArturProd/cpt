<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Sonata Project
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cpt\BlogBundle\Permalink;

use Cpt\BlogBundle\Model\PostInterface;

interface PermalinkInterface
{
    /**
     * @param \Cpt\BlogBundle\Model\PostInterface $post
     */
    public function generate(PostInterface $post);

    /**
     * @param string $permalink
     *
     * @return array
     */
    public function getParameters($permalink);
}
