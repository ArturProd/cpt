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

use Doctrine\ORM\EntityRepository;

use Cpt\BlogBundle\Model\PostInterface;

class BasePostRepository extends EntityRepository
{

    /**
     * return last post query builder
     *
     * @param int $limit
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findLastPostQueryBuilder($limit)
    {
        return $this->createQueryBuilder('p')
            ->where('p.enabled = true')
            ->orderby('p.createdAt', 'DESC');

    }

    /**
     * return count comments QueryBuilder
     *
     * @param  Cpt\BlogBundle\Model\PostInterface
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function countCommentsQuery($post)
    {
        return $this->getEntityManager()->createQuery('SELECT COUNT(c.id)
                                          FROM Cpt\BlogBundle\Entity\Comment c
                                          WHERE c.status = 1
                                          AND c.post = :post')
                    ->setParameters(array('post' => $post));
    }
}
