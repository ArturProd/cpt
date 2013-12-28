<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cpt\PublicationBundle\Manager;

use Cpt\PublicationBundle\Interfaces\Manager\CommentManagerInterface as CommentManagerInterface;
use Cpt\PublicationBundle\Interfaces\Entity\CommentInterface as CommentInterface;
use Cpt\PublicationBundle\Interfaces\Entity\PublicationInterface as PublicationInterface;
use Cpt\PublicationBundle\Entity\Comment as Comment;
use FOS\UserBundle\Model\UserInterface as UserInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\Pager;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

class CommentManager extends BaseManager implements CommentManagerInterface {

    public function create(PublicationInterface $publication, UserInterface $author) {
        $comment = new Comment();
        $comment->setPublication($publication);
        $comment->setStatus($publication->getCommentsDefaultStatus());
        $comment->setAuthor($author);
        return $comment;
    }

    /**
     * {@inheritDoc}
     */
    public function save(CommentInterface $comment) {
        $this->getPermissionManager()->ensureGrantedPublication('COMMENT', $comment->getPublication());

        $this->em->persist($comment);
        $this->em->flush();

        $this->updateCommentsCount($comment->getPublication());
        
        // creating the ACL
        $aclProvider = $this->getAclProvider();
        $objectIdentity = ObjectIdentity::fromDomainObject($comment);
        $acl = $aclProvider->createAcl($objectIdentity);
        $userIdentity = UserSecurityIdentity::fromAccount($this->getUser());
        $acl->insertObjectAce($userIdentity, MaskBuilder::MASK_OPERATOR);
        $aclProvider->updateAcl($acl);
    }

    /**
     * Update the number of comment for a comment
     *
     * @param null|\Cpt\BlogBundle\Model\PostInterface $post
     *
     * @return void
     */
    public function updateCommentsCount(PublicationInterface $publication = null) {
        $commentTableName = "cpt_publication_comment"; //$this->em->getClassMetadata($this->getClass())->table['name'];
        $publicationTableName = "cpt_publication_publication"; //$this->em->getClassMetadata($this->postManager->getClass())->table['name'];

        $this->em->getConnection()->beginTransaction();
        $this->em->getConnection()->query(sprintf('UPDATE %s p SET p.comments_count = 0', $publicationTableName));

        $this->em->getConnection()->query(sprintf(
                        'UPDATE %s p, (SELECT c.publication_id, count(*) as total FROM %s as c WHERE c.status = 1 GROUP BY c.publication_id) as count_comment
            SET p.comments_count = count_comment.total
            WHERE p.id = count_comment.publication_id'
                        , $publicationTableName, $commentTableName));

        $this->em->getConnection()->commit();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(CommentInterface $comment) {
        $this->getPermissionManager()->ensureGrantedComment('DELETE',$comment);

        $publication = $comment->getPublication();
        $this->em->remove($comment);
        $this->em->flush();

        $this->updateCommentsCount($publication);
    }

    public function get_newer_comments($publicationid, $newerthanid) {
        if (!is_numeric($newerthanid)) {
            throw new \InvalidArgumentException("newerthanid must be numeric");
        }

        if (!is_numeric($publicationid)) {
            throw new \InvalidArgumentException("publication id is not numeric");
        }

        $parameters['status'] = CommentInterface::STATUS_VALID;
        $parameters['publicationid'] = $publicationid;
        $parameters['newerthanid'] = $newerthanid;


        $query = $this->em->getRepository($this->class)
                ->createQueryBuilder('c')
                ->orderby('c.id', 'ASC')
                ->andWhere('c.status = :status')
                ->andWhere('c.publication = :publicationid')
                ->setMaxResults(100)
                ->andWhere('c.id > :newerthanid');

        return $query->setParameters($parameters)->getQuery()->getResult();
    }

    public function get_older_comments($publicationid, $number, $olderthanid = -1) {
        $parameters['status'] = CommentInterface::STATUS_VALID;
        $parameters['publicationid'] = $publicationid;

        if (!is_numeric($publicationid)) {
            throw new \InvalidArgumentException("publication id is not numeric");
        }

        $query = $this->em->getRepository($this->class)
                ->createQueryBuilder('c')
                ->orderby('c.createdAt', 'DESC')
                ->andWhere('c.status = :status')
                ->andWhere('c.publication = :publicationid')
                ->setMaxResults($number);

        if ((!empty($olderthanid)) && ($olderthanid > -1)) {
            if (!is_numeric($olderthanid)) {
                throw new \InvalidArgumentException("olderthanid is not numeric");
            }

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
    public function getPager(array $criteria, $page, $maxPerPage = 10) {
        $parameters = array();

        $query = $this->em->getRepository($this->class)
                ->createQueryBuilder('c')
                ->orderby('c.createdAt', 'DESC');

        $criteria['status'] = isset($criteria['status']) ? $criteria['status'] : CommentInterface::STATUS_VALID;
        $query->andWhere('c.status = :status');
        $parameters['status'] = $criteria['status'];

        if (isset($criteria['publicationId'])) {
            $query->andWhere('c.publication = :publicationId');
            $parameters['publicationId'] = $criteria['publicationId'];
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
