<?php

namespace Cpt\TestBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Cpt\BlogBundle\Entity\Post as Post;
/**
 * Description of LoadAboutArticles
 *
 * @author cyril
 */
class LoadAboutArticles implements FixtureInterface, ContainerAwareInterface
 {
   /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    { 

        $manager->persist($adminuser);

        $manager->flush();
        
    }
    
    protected function CreatePost($title,$content,$author){
        $post = new Post();
        $post->setAuthor($author);
        $post->setTitle($title);
        $post->setCon
    }
    
}
