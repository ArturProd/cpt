<?php

namespace Cpt\MainBundle\Entity;

use Doctrine\ORM\Query;
use Cpt\MainBundle\Interfaces\Entity\PagerInterface;
use JMS\Serializer\Annotation\ExclusionPolicy as ExclusionPolicy;
use JMS\Serializer\Annotation\Expose as Expose;
use JMS\Serializer\Annotation\VirtualProperty as VirtualProperty;
use JMS\Serializer\Annotation\SerializedName as SerializedName;


/**
 * @ExclusionPolicy("all")
 */
class Pager implements \Iterator, \Countable, \Serializable, PagerInterface
{
    /**
     * @Expose
     */
    protected $page = 1;
    /**
     * @Expose
     */
    protected $maxPerPage = 0;
    /**
     * @Expose
     */
    protected $lastPage = 1;
    /**
     * @Expose
     */
    protected $nbResults = 0;
    protected $cursor = 1;
    protected $parameters = array();
    /**
     * @Expose
     */
    protected $currentMaxLink = 1;
    protected $maxRecordLimit = false;
    /**
     * @Expose
     */
    protected $maxPageLinks = 0;

    // used by iterator interface
    protected $results = null;
    protected $resultsCounter = 0;
    protected $query = null;
    protected $countColumn = array('id');

    /**
     * Constructor.
     *
     * @param integer $maxPerPage Number of records to display per page
     */
    public function __construct($maxPerPage = 10)
    {
        $this->setMaxPerPage($maxPerPage);
    }


    /**
     * Returns the current pager's max link.
     *
     * @return integer
     */
    public function getCurrentMaxLink()
    {
        return $this->currentMaxLink;
    }

    /**
     * Returns the current pager's max record limit.
     *
     * @return integer
     */
    public function getMaxRecordLimit()
    {
        return $this->maxRecordLimit;
    }

    /**
     * Sets the current pager's max record limit.
     *
     * @param integer $limit
     */
    public function setMaxRecordLimit($limit)
    {
        $this->maxRecordLimit = $limit;
    }

    /**
     * Returns an array of page numbers to use in pagination links.
     *
     * @param integer $nbLinks The maximum number of page numbers to return
     *
     * @return array
     */
    public function getLinks($nbLinks = null)
    {
        if ($nbLinks == null) {
            $nbLinks = $this->getMaxPageLinks();
        }
        $links = array();
        $tmp   = $this->page - floor($nbLinks / 2);
        $check = $this->lastPage - $nbLinks + 1;
        $limit = $check > 0 ? $check : 1;
        $begin = $tmp > 0 ? ($tmp > $limit ? $limit : $tmp) : 1;

        $i = (int) $begin;
        while ($i < $begin + $nbLinks && $i <= $this->lastPage) {
            $links[] = $i++;
        }

        $this->currentMaxLink = count($links) ? $links[count($links) - 1] : 1;

        return $links;
    }

    /**
     * Returns true if the current query requires pagination.
     *
     * @return boolean
     */
     /**
     * @VirtualProperty
     * @SerializedName("havetopaginate")
     */
    public function haveToPaginate()
    {
        return $this->getMaxPerPage() && $this->getNbResults() > $this->getMaxPerPage();
    }

    /**
     * Returns the current cursor.
     *
     * @return integer
     */
    public function getCursor()
    {
        return $this->cursor;
    }

    /**
     * Sets the current cursor.
     *
     * @param integer $pos
     */
    public function setCursor($pos)
    {
        if ($pos < 1) {
            $this->cursor = 1;
        } else {
            if ($pos > $this->nbResults) {
                $this->cursor = $this->nbResults;
            } else {
                $this->cursor = $pos;
            }
        }
    }

    /**
     * Returns an object by cursor position.
     *
     * @param integer $pos
     *
     * @return mixed
     */
    public function getObjectByCursor($pos)
    {
        $this->setCursor($pos);

        return $this->getCurrent();
    }

    /**
     * Returns the current object.
     *
     * @return mixed
     */
    public function getCurrent()
    {
        return $this->retrieveObject($this->cursor);
    }

    /**
     * Returns the next object.
     *
     * @return mixed|null
     */
    public function getNext()
    {
        if ($this->cursor + 1 > $this->nbResults) {
            return null;
        } else {
            return $this->retrieveObject($this->cursor + 1);
        }
    }

    /**
     * Returns the previous object.
     *
     * @return mixed|null
     */
    public function getPrevious()
    {
        if ($this->cursor - 1 < 1) {
            return null;
        } else {
            return $this->retrieveObject($this->cursor - 1);
        }
    }

    /**
     * Returns the first index on the current page.
     *
     * @return integer
     */
    public function getFirstIndice()
    {
        if ($this->page == 0) {
            return 1;
        } else {
            return ($this->page - 1) * $this->maxPerPage + 1;
        }
    }

    /**
     * Returns the last index on the current page.
     *
     * @return integer
     */
    public function getLastIndice()
    {
        if ($this->page == 0) {
            return $this->nbResults;
        } else {
            if ($this->page * $this->maxPerPage >= $this->nbResults) {
                return $this->nbResults;
            } else {
                return $this->page * $this->maxPerPage;
            }
        }
    }

    /**
     * Returns the number of results.
     *
     * @return integer
     */
    public function getNbResults()
    {
        return $this->nbResults;
    }

    /**
     * Sets the number of results.
     *
     * @param integer $nb
     */
    protected function setNbResults($nb)
    {
        $this->nbResults = $nb;
    }

    /**
     * Returns the first page number.
     *
     * @return integer
     */
     /**
     * @VirtualProperty
     * @SerializedName("first_page")
     */
    public function getFirstPage()
    {
        return 1;
    }

    /**
     * Returns the last page number.
     *
     * @return integer
     */
    public function getLastPage()
    {
        return $this->lastPage;
    }

    /**
     * Sets the last page number.
     *
     * @param integer $page
     */
    protected function setLastPage($page)
    {
        $this->lastPage = $page;

        if ($this->getPage() > $page) {
            $this->setPage($page);
        }
    }

    /**
     * Returns the current page.
     *
     * @return integer
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Returns the next page.
     *
     * @return integer
     */
    public function getNextPage()
    {
        return min($this->getPage() + 1, $this->getLastPage());
    }

    /**
     * Returns the previous page.
     *
     * @return integer
     */
    public function getPreviousPage()
    {
        return max($this->getPage() - 1, $this->getFirstPage());
    }

    /**
     * {@inheritdoc}
     */
    public function setPage($page)
    {
        $this->page = intval($page);

        if ($this->page <= 0) {
            // set first page, which depends on a maximum set
            $this->page = $this->getMaxPerPage() ? 1 : 0;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxPerPage()
    {
        return $this->maxPerPage;
    }

    /**
     * {@inheritdoc}
     */
    public function setMaxPerPage($max)
    {
        if ($max > 0) {
            $this->maxPerPage = $max;
            if ($this->page == 0) {
                $this->page = 1;
            }
        } else {
            if ($max == 0) {
                $this->maxPerPage = 0;
                $this->page       = 0;
            } else {
                $this->maxPerPage = 1;
                if ($this->page == 0) {
                    $this->page = 1;
                }
            }
        }
    }

    /**
     * Returns the maximum number of page numbers.
     *
     * @return integer
     */
    public function getMaxPageLinks()
    {
        return $this->maxPageLinks;
    }

    /**
     * Sets the maximum number of page numbers.
     *
     * @param integer $maxPageLinks
     */
    public function setMaxPageLinks($maxPageLinks)
    {
        $this->maxPageLinks = $maxPageLinks;
    }

    /**
     * Returns true if on the first page.
     *
     * @return boolean
     */
    public function isFirstPage()
    {
        return 1 == $this->page;
    }

    /**
     * Returns true if on the last page.
     *
     * @return boolean
     */
    public function isLastPage()
    {
        return $this->page == $this->lastPage;
    }

    /**
     * Returns the current pager's parameter holder.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Returns a parameter.
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getParameter($name, $default = null)
    {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : $default;
    }

    /**
     * Checks whether a parameter has been set.
     *
     * @param string $name
     *
     * @return boolean
     */
    public function hasParameter($name)
    {
        return isset($this->parameters[$name]);
    }

    /**
     * Sets a parameter.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }

       protected $queryBuilder = null;

    /**
     * {@inheritdoc}
     */
    public function computeNbResult()
    {
        $countQuery = clone $this->getQuery();

        if (count($this->getParameters()) > 0) {
            $countQuery->setParameters($this->getParameters());
        }

        $countQuery->select(sprintf('count(DISTINCT %s.%s) as cnt', $countQuery->getRootAlias(), current($this->getCountColumn())));

        return $countQuery->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getResults($hydrationMode = Query::HYDRATE_OBJECT)
    {
        return $this->getQuery()->execute(array(), $hydrationMode);
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->resetIterator();

        $this->setNbResults($this->computeNbResult());

        $this->getQuery()->setFirstResult(null);
        $this->getQuery()->setMaxResults(null);

        if (count($this->getParameters()) > 0) {
            $this->getQuery()->setParameters($this->getParameters());
        }

        if (0 == $this->getPage() || 0 == $this->getMaxPerPage() || 0 == $this->getNbResults()) {
            $this->setLastPage(0);
        } else {
            $offset = ($this->getPage() - 1) * $this->getMaxPerPage();

            $this->setLastPage(ceil($this->getNbResults() / $this->getMaxPerPage()));

            $this->getQuery()->setFirstResult($offset);
            $this->getQuery()->setMaxResults($this->getMaxPerPage());
        }
    }
    
    /**
     * Returns true if the properties used for iteration have been initialized.
     *
     * @return boolean
     */
    protected function isIteratorInitialized()
    {
        return null !== $this->results;
    }

    /**
     * Loads data into properties used for iteration.
     */
    protected function initializeIterator()
    {
        $this->results        = $this->getResults();
        $this->resultsCounter = count($this->results);
    }

    /**
     * Empties properties used for iteration.
     */
    protected function resetIterator()
    {
        $this->results        = null;
        $this->resultsCounter = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        if (!$this->isIteratorInitialized()) {
            $this->initializeIterator();
        }

        return current($this->results);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        if (!$this->isIteratorInitialized()) {
            $this->initializeIterator();
        }

        return key($this->results);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        if (!$this->isIteratorInitialized()) {
            $this->initializeIterator();
        }

        --$this->resultsCounter;

        return next($this->results);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        if (!$this->isIteratorInitialized()) {
            $this->initializeIterator();
        }

        $this->resultsCounter = count($this->results);

        return reset($this->results);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        if (!$this->isIteratorInitialized()) {
            $this->initializeIterator();
        }

        return $this->resultsCounter > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->getNbResults();
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        $vars = get_object_vars($this);
        unset($vars['query']);

        return serialize($vars);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        $array = unserialize($serialized);

        foreach ($array as $name => $values) {
            $this->$name = $values;
        }
    }

    /**
     * @return array
     */
    public function getCountColumn()
    {
        return $this->countColumn;
    }

    /**
     * @param array $countColumn
     *
     * @return array
     */
    public function setCountColumn(array $countColumn)
    {
        return $this->countColumn = $countColumn;
    }

    /**
     * Retrieve the object for a certain offset
     *
     * @param integer $offset
     *
     * @return object
     */
    protected function retrieveObject($offset)
    {
        $queryForRetrieve = clone $this->getQuery();
        $queryForRetrieve
            ->setFirstResult($offset - 1)
            ->setMaxResults(1);

        $results = $queryForRetrieve->execute();

        return $results[0];
    }

    /**
     * {@inheritdoc}
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

}
?>
