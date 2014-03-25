<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Sonata Project
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cpt\PublicationBundle\Interfaces\Manager;

use Cpt\PublicationBundle\Interfaces\Entity\PublicationInterface as PublicationInterface;

interface PermalinkManagerInterface
{
    /**
     * @param \Cpt\BlogBundle\Model\PostInterface $post
     */
    public function generate(PublicationInterface $publication);

    /**
     * @param string $permalink
     *
     * @return array
     */
    public function getParameters($permalink);
}
