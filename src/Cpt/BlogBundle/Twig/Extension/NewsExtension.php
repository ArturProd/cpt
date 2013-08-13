<?php

/*
 * This file is part of sonata-project.
 *
 * (c) 2010 Thomas Rabaix
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cpt\BlogBundle\Twig\Extension;

use Symfony\Component\Routing\RouterInterface;
use Cpt\BlogBundle\Model\TagManagerInterface;
use Cpt\BlogBundle\Model\BlogInterface;
use Cpt\BlogBundle\Model\PostInterface;
use Cpt\BlogBundle\Model\PostManagerInterface;

class NewsExtension extends \Twig_Extension
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var CmsManagerSelectorInterface
     */
    private $tagManager;
    
    
    private $postmanager;

    /**
     * @param \Symfony\Component\Routing\RouterInterface   $router
     * @param \Cpt\BlogBundle\Model\TagManagerInterface $tagManager
     * @param \Cpt\BlogBundle\Model\BlogInterface       $blog
     */
    public function __construct(RouterInterface $router, TagManagerInterface $tagManager, BlogInterface $blog)
    {
        $this->router     = $router;
        $this->tagManager = $tagManager;
        $this->blog       = $blog;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'sonata_news_link_tag_rss' => new \Twig_Function_Method($this, 'renderTagRss', array('is_safe' => array('html'))),
            'sonata_news_permalink'    => new \Twig_Function_Method($this, 'generatePermalink'),
        ); 
    }


    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'sonata_news';
    }

    /**
     * @return string
     */
    public function renderTagRss()
    {
        $rss = array();
        foreach ($this->tagManager->findBy(array('enabled' => true)) as $tag) {
            $rss[] = sprintf('<link href="%s" title="%s : %s" type="application/rss+xml" rel="alternate" />',
                $this->router->generate('sonata_news_tag', array('tag' => $tag->getSlug(), '_format' => 'rss'), true),
                $this->blog->getTitle(),
                $tag->getName()
            );
        }

        return implode("\n", $rss);
    }

    /**
     * @param \Cpt\BlogBundle\Model\PostInterface $post
     *
     * @return string|Exception
     */
    public function generatePermalink(PostInterface $post)
    {
        return $this->blog->getPermalinkGenerator()->generate($post);
    }
}
