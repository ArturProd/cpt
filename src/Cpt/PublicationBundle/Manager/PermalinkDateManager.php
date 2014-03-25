<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Sonata Project
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cpt\PublicationBundle\Manager;

use Cpt\PublicationBundle\Interfaces\Manager\PermalinkManagerInterface  as PermalinkInterface;
use Cpt\PublicationBundle\Interfaces\Entity\PublicationInterface as PublicationInterface;

class PermalinkDateManager implements PermalinkInterface
{
    protected $pattern;

    /**
     * @param $pattern
     */
    public function __construct($pattern = '%1$04d/%2$d/%3$d/%4$s')
    {
        $this->pattern = $pattern;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(PublicationInterface $publication)
    {
        return sprintf($this->pattern,
            $publication->getYear(),
            $publication->getMonth(),
            $publication->getDay(),
            $publication->getSlug()
        );
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @param string $permalink
     *
     * @return array
     */
    public function getParameters($permalink)
    {
        $parameters = explode('/', $permalink);

        if (count($parameters) != 4) {
            throw new \InvalidArgumentException('wrong permalink format');
        }

        list($year, $month, $day, $slug) = $parameters;

        return array(
            'year'  => (int) $year,
            'month' => (int) $month,
            'day'   => (int) $day,
            'slug'  => $slug
        );
    }
}
