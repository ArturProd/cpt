<?php

namespace Cpt\PublicationBundle\Entity;

use Cpt\PublicationBundle\Entity\Commentable as Commentable;
use Cpt\PublicationBundle\Interfaces\Entity\PublicationInterface as PublicationInterface;

/**
 * Description of Publication
 *
 * @author cyril
 */
class Publication extends Commentable implements PublicationInterface {

    public function __construct($author = null, $enabled = true, $title = "") {
        parent::__construct();

        $this->setId(-1);
        $this->setAuthor($author);
        $this->setEnabled($enabled);
        $this->setTitle($title);
        $this->setPublicationDateStart(new \DateTime);
        $this->setContent("");
    }

    // <editor-fold defaultstate="collapsed" desc="attributes">
    protected $id;
    protected $author;

    protected $title;
    protected $content;
    protected $enabled;
    protected $publicationDateStart;
    
    protected $slug;
    
    protected $createdAt;
    protected $updatedAt;
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Lifecycle callbacks">
    public function prePersist() {
        if (!$this->getPublicationDateStart()) {
            $this->setPublicationDateStart(new \DateTime);
        }

        $this->setCreatedAt(new \DateTime);
        $this->setUpdatedAt(new \DateTime);
    }

    public function preUpdate() {
        $this->setUpdatedAt(new \DateTime);
    }

    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="getters & setters">
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthor($author) {
        $this->author = $author;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthor() {
        return $this->author;
    }

    /**
     * {@inheritdoc}
     */
    public function setContent($content) {
        $this->content = $content;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabled($enabled) {
        $this->enabled = $enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnabled() {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function setSlug($slug) {
        $this->slug = $slug;
    }

    /**
     * {@inheritdoc}
     */
    public function getSlug() {
        return $this->slug;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return BaseEvent
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return BaseEvent
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt() {
        return $this->updatedAt;
    }

    /**
     * @var string
     */

    /**
     * {@inheritdoc}
     */
    public function setTitle($title) {
        $this->title = $title;

        $this->setSlug(\Cpt\BlogBundle\Manager\BaseManager::slugify($title));
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * {@inheritdoc}
     */
    public function setPublicationDateStart(\DateTime $publicationDateStart = null) {
        $this->publicationDateStart = $publicationDateStart;
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicationDateStart() {
        return $this->publicationDateStart;
    }

    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="helpers">
    public function isPublic() {
        if (!$this->getEnabled()) {
            return false;
        }

        return $this->getPublicationDateStart()->diff(new \DateTime)->invert == 0 ? true : false;
    }

    /**
     * {@inheritdoc}
     */
    public function getYear() {
        return $this->getPublicationDateStart()->format('Y');
    }

    /**
     * {@inheritdoc}
     */
    public function getMonth() {
        return $this->getPublicationDateStart()->format('m');
    }

    /**
     * {@inheritdoc}
     */
    public function getDay() {
        return $this->getPublicationDateStart()->format('d');
    }

    // </editor-fold>
}

?>
