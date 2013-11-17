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

use Cpt\BlogBundle\Model\CommentManager as ModelCommentManager;
use Cpt\BlogBundle\Model\CommentInterface;
use Doctrine\ORM\EntityManager;

use Cpt\BlogBundle\Model\PostManagerInterface;
use Cpt\BlogBundle\Model\PostInterface;

use Sonata\DoctrineORMAdminBundle\Datagrid\Pager;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;

class CommentManager extends ModelCommentManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Cpt\BlogBundle\Model\PostManagerInterface
     */
    protected $postManager;

    /**
     * @param \Doctrine\ORM\EntityManager                   $em
     * @param string                                        $class
     * @param \Cpt\BlogBundle\Model\PostManagerInterface $postManager
     */
    public function __construct(EntityManager $em, $class, PostManagerInterface $postManager)
    {
        $this->em          = $em;
        $this->postManager = $postManager;
        $this->class       = $class;
    }

    /**
     * {@inheritDoc}
     */
    public function save(CommentInterface $comment)
    {
        $this->em->persist($comment);
        $this->em->flush();

        $this->updateCommentsCount($comment->getPost());
    }

    /**
     * Update the number of comment for a comment
     *
     * @param null|\Cpt\BlogBundle\Model\PostInterface $post
     *
     * @return void
     */
    public function updateCommentsCount(PostInterface $post = null)
    {
        $commentTableName = $this->em->getClassMetadata($this->getClass())->table['name'];
        $postTableName    = $this->em->getClassMetadata($this->postManager->getClass())->table['name'];

        $this->em->getConnection()->beginTransaction();
        $this->em->getConnection()->query(sprintf('UPDATE %s p SET p.comments_count = 0' , $postTableName));

        $this->em->getConnection()->query(sprintf(
            'UPDATE %s p, (SELECT c.post_id, count(*) as total FROM %s as c WHERE c.status = 1 GROUP BY c.post_id) as count_comment
            SET p.comments_count = count_comment.total
            WHERE p.id = count_comment.post_id'
        , $postTableName, $commentTableName));

        $this->em->getConnection()->commit();
    }

    /**
     * {@inheritDoc}
     */
    public function findOneBy(array $criteria)
    {
        return $this->em->getRepository($this->class)->findOneBy($criteria);
    }

    /**
     * {@inheritDoc}
     */
    public function findBy(array $criteria)
    {
        return $this->em->getRepository($this->class)->findBy($criteria);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(CommentInterface $comment)
    {
        $post = $comment->getPost();
        $this->em->remove($comment);
        $this->em->flush();
        
        $this->updateCommentsCount($post);
    }

    public function get_older_comments($postid, $number, $olderthanid = null)
    {
        $parameters['status'] = CommentInterface::STATUS_VALID;
        $parameters['postid'] = $postid;
        
        if (!is_numeric($postid))
            throw new \InvalidArgumentException("post id is not numeric");
        

        $query = $this->em->getRepository($this->class)
            ->createQueryBuilder('c')
            ->orderby('c.createdAt', 'DESC')
            ->andWhere('c.status = :status')            
            ->andWhere('c.post = :postid')
            ->setMaxResults($number);
        
        if (!empty($olderthanid))
        {
            if (!is_numeric($olderthanid))
                throw new \InvalidArgumentException("olderthanid is not numeric");
            
            $query->andWhere('c.id < :olderthanid');
            $parameters['olderthanid'] = $olderthanid;
        }
        

         
        return $query->setParameters($parameters)->getQuery()->getResult();
    }
    
    /**
     * @param array   $criteria
     * @param integer $page
     * @param integer $maxPerPage
     *
     * @return \Sonata\AdminBundle\Datagrid\ORM\Pager
     */
    public function getPager(array $criteria, $page, $maxPerPage = 10)
    {
        $parameters = array();

        $query = $this->em->getRepository($this->class)
            ->createQueryBuilder('c')
            ->orderby('c.createdAt', 'DESC');

        $criteria['status'] = isset($criteria['status']) ? $criteria['status'] : CommentInterface::STATUS_VALID;
        $query->andWhere('c.status = :status');
        $parameters['status'] = $criteria['status'];

        if (isset($criteria['postId'])) {
            $query->andWhere('c.post = :postId');
            $parameters['postId'] = $criteria['postId'];
        }

        $query->setParameters($parameters);

        $pager = new Pager();
        $pager->setMaxPerPage($maxPerPage);
        $pager->setQuery(new ProxyQuery($query));
        $pager->setPage($page);
        $pager->init();

        return $pager;
    }
}
