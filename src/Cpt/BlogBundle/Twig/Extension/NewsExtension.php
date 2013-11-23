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
use Cpt\BlogBundle\Interfaces\Entity\PostInterface as PostInterface;
use Cpt\BlogBundle\Manager\PermalinkDateManager as PermalinkDateManager;

class NewsExtension extends \Twig_Extension
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param \Symfony\Component\Routing\RouterInterface   $router
     * @param \Cpt\BlogBundle\Model\TagManagerInterface $tagManager
     * @param \Cpt\BlogBundle\Model\BlogInterface       $blog
     */
    public function __construct(RouterInterface $router)
    {
        $this->router     = $router;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
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
     * @param \Cpt\BlogBundle\Model\PostInterface $post
     *
     * @return string|Exception
     */
    public function generatePermalink(PostInterface $post)
    {
        $permalinkgenerator = new PermalinkDateManager();
        return $permalinkgenerator->generate($post);
        //return "hello";
    }
}
