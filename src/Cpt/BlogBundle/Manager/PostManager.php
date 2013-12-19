<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cpt\BlogBundle\Manager;

use Cpt\BlogBundle\Interfaces\Entity\PostInterface as PostInterface;
use Cpt\BlogBundle\Interfaces\Entity\CategoryInterface as CategoryInterface;
use Cpt\BlogBundle\Entity\Post as Post;

use Cpt\BlogBundle\Interfaces\Manager\PostManagerInterface as PostManagerInterface;

use Cpt\BlogBundle\Manager\PermalinkDateManager as DatePermalink;

use Sonata\DoctrineORMAdminBundle\Datagrid\Pager;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr;

use Doctrine\ORM\Query;

class PostManager extends BaseManager implements PostManagerInterface
{

    

    
    
    public function createPostInstance($author, $publishedhomepage=false, $enabled=true, $title="", $content="")
    {
        return new Post($author, $publishedhomepage, $enabled, $title, $content);
    }   
    /**
     * {@inheritDoc}
     */
    public function save(PostInterface $post)
    {
        $this->em->persist($post);
        $this->em->flush();
    }



    /**
     * @param string                                 $permalink
     * @param \Cpt\BlogBundle\Model\BlogInterface $blog
     *
     * @return PostInterface
     */
    public function findOneByPermalink($permalink)
    {
        try {
            $repository = $this->em->getRepository($this->class);

            $query = $repository->createQueryBuilder('p');

            $PermalinkGenerator = new DatePermalink();
            $urlParameters = $PermalinkGenerator->getParameters($permalink);

            $parameters = array();

            if (isset($urlParameters['year']) && isset($urlParameters['month']) && isset($urlParameters['day'])) {
                $pdqp = $this->getPublicationDateQueryParts(sprintf('%d-%d-%d', $urlParameters['year'], $urlParameters['month'], $urlParameters['day']), 'day');

                $parameters = array_merge($parameters, $pdqp['params']);

                $query->andWhere($pdqp['query']);
            }

            if (isset($urlParameters['slug'])) {
                $query->andWhere('p.slug = :slug');
                $parameters['slug'] = $urlParameters['slug'];
            }

            if (isset($urlParameters['category'])) {
                $pcqp = $this->getPublicationCategoryQueryParts($urlParameters['category']);

                $parameters = array_merge($parameters, $pcqp['params']);

                $query
                    ->leftJoin('p.category', 'c')
                    ->andWhere($pcqp['query'])
                ;
            }

            if (count($parameters) == 0) {
                return null;
            }

            $query->setParameters($parameters);

            return $query->getQuery()->getSingleResult();

        } catch (NoResultException $e) {
            return null;
        }
    }

  

//    public function getHomePager($alaune, $page = 0, $maxPerPage = 10)
//    {
//        return $this->getPager( array ('publishedhomepage' => $alaune, 'enabled' => true ), $page, $maxPerPage );
//    }
    

    /**
     * {@inheritDoc}
     */
    public function delete(PostInterface $post)
    {
        $this->em->remove($post);
        $this->em->flush();
    }

    
    public function getAlauneArticlesPager($page = 1, $maxPerPage = 100, $maxPageLinks = 5)
    {
        $criteria['enabled'] = true;    
        $criteria['publishedhomepage'] = true; 

        $pager = $this->getPager($criteria, $page,$maxPerPage );
        $pager->setMaxPageLinks($maxPageLinks);

        return $pager;
    }
        
    public function getMyArticlesPager($userid, $page, $maxPerPage = 10, $maxPageLinks = 5)
    {
       $criteria['author'] = $userid;

       $pager = $this->getPager($criteria, $page,$maxPerPage );
       $pager->setMaxPageLinks($maxPageLinks);
       
       return $pager;
    }
    
    public function getAllArticlesPager($page, $is_user_admin = false, $maxPerPage = 10, $maxPageLinks = 5)
    {
       if (!$is_user_admin)
        $criteria['enabled'] = true;
       
       $criteria['publishedhomepage'] = false;

       $pager = $this->getPager($criteria, $page,$maxPerPage ); 
       $pager->setMaxPageLinks($maxPageLinks);

       return $pager;
    }
    
    /**
     * Retrieve posts, based on the criteria, a page at a time.
     * Valid criteria are:
     *    enabled - boolean
     *    date - query
     *    tag - string
     *    author - 'NULL', 'NOT NULL', id, array of ids
     *
     * @param array   $criteria
     * @param integer $page
     * @param integer $maxPerPage
     *
     * @return \Sonata\AdminBundle\Datagrid\Pager
     */
    protected function getPager(array $criteria, $page, $maxPerPage = 10)
    {
        $parameters = array();
        $query = $this->em->getRepository($this->class)
            ->createQueryBuilder('p')
            ->select('p')
            ->leftJoin('p.author', 'a', Expr\Join::WITH, 'a.enabled = true')
            ->addOrderby('p.publishedhomepage', 'DESC') // "A la une" post come first
            ->addOrderby('p.publicationDateStart', 'DESC');

        // enabled
        if (isset($criteria['enabled'])) // Set enabled = 'all' to not select using enabled criteria
        {
            $query->andWhere('p.enabled = :enabled');
            $parameters['enabled'] = $criteria['enabled'];
        }

        // publishedhomepage
        if (isset($criteria['publishedhomepage'])) {
            $query->andWhere('p.publishedhomepage = :publishedhomepage');
            $parameters['publishedhomepage'] = $criteria['publishedhomepage'];
        }
        
        if (isset($criteria['date'])) {
            $query->andWhere($criteria['date']['query']);
            $parameters = array_merge($parameters, $criteria['date']['params']);
        }


        if (isset($criteria['author'])) {
            if (!is_array($criteria['author']) && stristr($criteria['author'], 'NULL')) {
                $query->andWhere('p.author IS '.$criteria['author']);
            } else {
                $query->andWhere(sprintf('p.author IN (%s)', implode((array) $criteria['author'], ',')));
            }
        }

        if (isset($criteria['category']) && $criteria['category'] instanceof CategoryInterface) {
            $query->andWhere('p.category = :categoryid');
            $parameters['categoryid'] = $criteria['category']->getId();
        }

        $query->setParameters($parameters);
                        
        $pager = new Pager();
        $pager->setMaxPerPage($maxPerPage);
        $pager->setQuery(new ProxyQuery($query));
        $pager->setPage($page);
        $pager->init();
        

        return $pager;
    }

    /**
     * @param string $date  Date in format YYYY-MM-DD
     * @param string $step  Interval step: year|month|day
     * @param string $alias Table alias for the publicationDateStart column
     *
     * @return array
     */
    public function getPublicationDateQueryParts($date, $step, $alias = 'p')
    {
        return array(
            'query'  => sprintf('%s.publicationDateStart >= :startDate AND %s.publicationDateStart < :endDate', $alias, $alias),
            'params' => array(
                'startDate' => new \DateTime($date),
                'endDate'   => new \DateTime($date . '+1 ' . $step)
            )
        );
    }

    /**
     * @param string $category
     *
     * @return array
     */
    public function getPublicationCategoryQueryParts($category)
    {
        $pcqp = array('query' => '', 'params' => array());

        if (null === $category) {
            $pcqp['query'] = 'p.category IS NULL';
        } else {
            $pcqp['query'] = 'c.slug = :category';
            $pcqp['params'] = array('category' => $category);
        }

        return $pcqp;
    }
}
