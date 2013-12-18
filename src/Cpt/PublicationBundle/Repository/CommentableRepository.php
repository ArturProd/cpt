<?php


namespace Cpt\PublicationBundle\Repository;

use Doctrine\ORM\EntityRepository;


class CommentableRepository extends EntityRepository
{
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
?>
