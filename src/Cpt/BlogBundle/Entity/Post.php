<?php

/**
 * This file is part of the <name> project.
 *
 * (c) <yourname> <youremail>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cpt\BlogBundle\Entity;
use Cpt\PublicationBundle\Entity\Publication as Publication;

use Cpt\BlogBundle\Interfaces\Entity\CategoryInterface as CategoryInterface;
use Cpt\BlogBundle\Interfaces\Entity\PostInterface as PostInterface;
use Cpt\PublicationBundle\Interfaces\Entity\CommentInterface as CommentInterface;


/**
 * This file has been generated by the Sonata EasyExtends bundle ( http://sonata-project.org/bundles/easy-extends )
 *
 * References :
 *   working with object : http://www.doctrine-project.org/projects/orm/2.0/docs/reference/working-with-objects/en
 *
 * @author <yourname> <youremail>
 */
class Post extends Publication implements PostInterface 
{
    
    public function toViewArray()
    {
    return Array(
        "id" => $this->getId(),
        "PublishedHomePage" => $this->getPublishedHomePage(),
        "CommentsCount" => $this->getCommentsCount(),
        "AuthorName" => $this->getAuthor()->getDisplayName(),
        "title" => $this->getTitle(),
        "Content" => $this->getContent()
        );
    }

    protected $link;

    /**
     * @var PermalinkInterface
     */
    protected $permalinkGenerator;
    
    protected $canBeCommented; // Unmapped field
    
    protected $canbemodified; // unmapped field

    protected $publishedhomepage;
 
    protected $image;

    protected $category;
    
    
 
    
    /**
     * @param string             $title
     * @param string             $link
     * @param string             $description
     * @param PermalinkInterface $permalinkGenerator
     */
    public function __construct($author, $publishedhomepage=false, $enabled=true, $title="")
    {
        $this->setAuthor($author);
        $this->setTitle($title);
        $this->link        = "";
        $this->permalinkGenerator = null;
        $this->publishedhomepage = $publishedhomepage;
        $this->enabled = $enabled;
        $this->canBeCommented = true;
        
        
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection;
        $this->setPublicationDateStart(new \DateTime);
        $this->setCategory();
        $this->setCommentsDefaultStatus(CommentInterface::STATUS_VALID);
 
        $this->setContent("");
        $this->id = -1;
    }

    /**
     * {@inheritdoc}
     */
    public function getPermalinkGenerator()
    {
        return $this->permalinkGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * {@inheritdoc}
     */
    public function getLink()
    {
        return $this->link;
    }

  
 

    
    public function getPublishedHomePage()
    {
        return $this->publishedhomepage;
    }

    public function setPublishedHomePage($value)
    {
        $this->publishedhomepage = $value;
    }
    
    public function getCanBeCommented()
    {
        return $this->canBeCommented;
    }
    
    public function setCanBeCommented($value)
    {
        $this->canBeCommented = $value;
    }
    
    public function getCanBeModified()
    {
        return $this->canbemodified;
    }
    
    public function setCanBeModified($value)
    {
        $this->canbemodified = $value;
    }
    
  
   
  
    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getTitle() ?: 'n/a';
    }

    /**
     * {@inheritdoc}
     */
    public function isCommentable()
    {
        if (!$this->getCommentsEnabled() || !$this->getEnabled()) {
            return false;
        }

        if ($this->getCommentsCloseAt() instanceof \DateTime) {
            return $this->getCommentsCloseAt()->diff(new \DateTime)->invert == 1 ? true : false;
        }

        return true;
    }


    /**
     * {@inheritdoc}
     */
    public function setCategory(CategoryInterface $category = null)
    {
        $this->category = $category;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategory()
    {
        return $this->category;
    }


}