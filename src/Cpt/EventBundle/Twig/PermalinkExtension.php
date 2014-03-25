<?php

/*
 * This file is part of sonata-project.
 *
 * (c) 2010 Thomas Rabaix
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cpt\EventBundle\Twig;

use Cpt\EventBundle\Interfaces\Entity\EventInterface as EventInterface;
use Cpt\PublicationBundle\Manager\PermalinkDateManager as PermalinkDateManager;

class PermalinkExtension extends \Twig_Extension
{


    public function __construct()
    {
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'event_permalink'    => new \Twig_Function_Method($this, 'generatePermalink'),
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
        return 'cpt_event_permalink';
    }


    /**
     * @param \Cpt\BlogBundle\Model\PostInterface $post
     *
     * @return string|Exception
     */
    public function generatePermalink(EventInterface $event)
    {
        $permalinkgenerator = new PermalinkDateManager();
        return $permalinkgenerator->generate($event);
    }
}
