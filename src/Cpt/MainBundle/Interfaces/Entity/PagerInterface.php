<?php

namespace Cpt\MainBundle\Interfaces\Entity;

interface PagerInterface
{
    /**
     * Initialize the Pager.
     *
     * @return void
     */
    public function init();

    /**
     * Returns the maximum number of results per page.
     *
     * @return integer
     */
    public function getMaxPerPage();

    /**
     * Sets the maximum number of results per page.
     *
     * @param integer $max
     */
    public function setMaxPerPage($max);

    /**
     * Sets the current page.
     *
     * @param integer $page
     */
    public function setPage($page);

    /**
     * Set query
     *
     * @param mixed $query
     */
    public function setQuery($query);

    /**
     * Returns an array of results on the given page.
     *
     * @return array
     */
    public function getResults();
}

?>
