<?php

namespace Cpt\PublicationBundle\Interfaces\Entity;

/**
 *
 * @author cyril
 */
interface PublicationInterface {
    
    
    /**
     * @return boolean
     */
    public function isCommentable();
    /**
     * @return boolean
     */
    public function isPublic();
  /**
     * @return mixed
     */
    public function getId();

    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title);

    /**
     * Get title
     *
     * @return string $title
     */
    public function getTitle();


  
    /**
     * Set content
     *
     * @param string $content
     */
    public function setContent($content);

    /**
     * Get content
     *
     * @return string $content
     */
    public function getContent();

    /**
     * Set enabled
     *
     * @param boolean $enabled
     */
    public function setEnabled($enabled);

    /**
     * Get enabled
     *
     * @return boolean $enabled
     */
    public function getEnabled();

    /**
     * Set slug
     *
     * @param integer $slug
     */
    public function setSlug($slug);

    /**
     * Get slug
     *
     * @return integer $slug
     */
    public function getSlug();

    /**
     * Set publication_date_start
     *
     * @param \DateTime $publicationDateStart
     */
    public function setPublicationDateStart(\DateTime $publicationDateStart = null);

    /**
     * Get publication_date_start
     *
     * @return \DateTime $publicationDateStart
     */
    public function getPublicationDateStart();

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt = null);

    /**
     * Get created_at
     *
     * @return \DateTime $createdAt
     */
    public function getCreatedAt();

    /**
     * Set updated_at
     *
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt = null);

    /**
     * Get updated_at
     *
     * @return \Datetime $updatedAt
     */
    public function getUpdatedAt();

}

?>
