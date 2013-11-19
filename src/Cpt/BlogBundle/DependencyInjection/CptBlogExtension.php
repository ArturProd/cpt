<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cpt\BlogBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

use Sonata\EasyExtendsBundle\Mapper\DoctrineCollector;

/**
 * CptBlogBundleExtension
 *
 * @author      Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class CptBlogExtension extends Extension
{
    /**
     * @throws \InvalidArgumentException
     *
     * @param array                                                   $configs
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('admin.xml');
        $loader->load('orm.xml');
        $loader->load('twig.xml');
        $loader->load('form.xml');
        $loader->load('core.xml');

        if (!isset($config['salt'])) {
            throw new \InvalidArgumentException("The configration node 'salt' is not set for the CptBlogBundle (sonata_news)");
        }

        if (!isset($config['comment'])) {
            throw new \InvalidArgumentException("The configration node 'comment' is not set for the CptBlogBundle (sonata_news)");
        }

        $container->getDefinition('cpt.blog.hash.generator')
            ->replaceArgument(0, $config['salt']);

        $container->getDefinition('cpt.blog.permalink.date')
            ->replaceArgument(0, $config['permalink']['date']);

        $container->setAlias('cpt.blog.permalink.generator', $config['permalink_generator']);

        $container->setDefinition('cpt.blog.blog', new Definition('Cpt\BlogBundle\Model\Blog', array(
            $config['title'],
            $config['link'],
            $config['description'],
            new Reference('cpt.blog.permalink.generator')
        )));

        $container->getDefinition('cpt.blog.hash.generator')
            ->replaceArgument(0, $config['salt']);

        $container->getDefinition('cpt.blog.mailer')
            ->replaceArgument(5, array(
                'notification' => $config['comment']['notification']
            ));

        $this->registerDoctrineMapping($config, $container);
        $this->configureClass($config, $container);
        $this->configureAdmin($config, $container);
    }

    /**
     * @param array                                                   $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function configureClass($config, ContainerBuilder $container)
    {
        // admin configuration
        $container->setParameter('cpt.blog.admin.post.entity',       $config['class']['post']);
        $container->setParameter('cpt.blog.admin.comment.entity',    $config['class']['comment']);
        $container->setParameter('cpt.blog.admin.category.entity',   $config['class']['category']);

        // manager configuration
        $container->setParameter('cpt.blog.manager.post.entity',     $config['class']['post']);
        $container->setParameter('cpt.blog.manager.comment.entity',  $config['class']['comment']);
        $container->setParameter('cpt.blog.manager.category.entity', $config['class']['category']);
    }

    /**
     * @param array                                                   $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function configureAdmin($config, ContainerBuilder $container)
    {
        $container->setParameter('cpt.blog.admin.post.class',              $config['admin']['post']['class']);
        $container->setParameter('cpt.blog.admin.post.controller',         $config['admin']['post']['controller']);
        $container->setParameter('cpt.blog.admin.post.translation_domain', $config['admin']['post']['translation']);

        $container->setParameter('cpt.blog.admin.category.class',              $config['admin']['category']['class']);
        $container->setParameter('cpt.blog.admin.category.controller',         $config['admin']['category']['controller']);
        $container->setParameter('cpt.blog.admin.category.translation_domain', $config['admin']['category']['translation']);

        $container->setParameter('cpt.blog.admin.comment.class',              $config['admin']['comment']['class']);
        $container->setParameter('cpt.blog.admin.comment.controller',         $config['admin']['comment']['controller']);
        $container->setParameter('cpt.blog.admin.comment.translation_domain', $config['admin']['comment']['translation']);
     
    }

    /**
     * @param array $config
     */
    public function registerDoctrineMapping(array $config)
    {
        $collector = DoctrineCollector::getInstance();

        foreach ($config['class'] as $type => $class) {
            if (!class_exists($class)) {
                return;
            }
        }


        $collector->addAssociation($config['class']['post'], 'mapOneToMany', array(
             'fieldName' => 'comments',
             'targetEntity' => $config['class']['comment'],
             'cascade' =>
             array(
                 0 => 'remove',
                 1 => 'persist',
             ),
             'mappedBy' => 'post',
             'orphanRemoval' => true,
             'orderBy' =>
             array(
                 'createdAt' => 'DESC',
             ),
        ));

        $collector->addAssociation($config['class']['post'], 'mapManyToOne', array(
            'fieldName' => 'image',
            'targetEntity' => $config['class']['media'],
            'cascade' =>
            array(
                0 => 'remove',
                1 => 'persist',
                2 => 'refresh',
                3 => 'merge',
                4 => 'detach',
            ),
            'mappedBy' => NULL,
            'inversedBy' => NULL,
            'joinColumns' =>
            array(
                array(
                    'name' => 'image_id',
                    'referencedColumnName' => 'id',
                ),
            ),
            'orphanRemoval' => false,
        ));

        $collector->addAssociation($config['class']['post'], 'mapManyToOne', array(
             'fieldName' => 'author',
             'targetEntity' => $config['class']['user'],
             'cascade' =>
             array(
                 1 => 'persist',
             ),
             'mappedBy' => NULL,
             'inversedBy' => NULL,
             'joinColumns' =>
             array(
                 array(
                     'name' => 'author_id',
                     'referencedColumnName' => 'id',
                 ),
             ),
             'orphanRemoval' => false,
        ));

        $collector->addAssociation($config['class']['post'], 'mapManyToOne', array(
             'fieldName' => 'category',
             'targetEntity' => $config['class']['category'],
             'cascade' =>
             array(
                 1 => 'persist',
             ),
             'mappedBy' => NULL,
             'inversedBy' => NULL,
             'joinColumns' =>
             array(
                 array(
                     'name' => 'category_id',
                     'referencedColumnName' => 'id',
                 ),
             ),
             'orphanRemoval' => false,
        ));

       

        $collector->addAssociation($config['class']['comment'], 'mapManyToOne', array(
             'fieldName' => 'post',
             'targetEntity' => $config['class']['post'],
             'cascade' => array(
             ),
             'mappedBy' => NULL,
             'inversedBy' => 'comments',
             'joinColumns' =>
             array(
                 array(
                     'name' => 'post_id',
                    'referencedColumnName' => 'id',
                 ),
             ),
             'orphanRemoval' => false,
        ));
        
        // Adding comment => Author association    
        // Many to one is always the owning side of an association and should use InversedBy
         $collector->addAssociation($config['class']['comment'], 'mapManyToOne', array(
             'fieldName' => 'author',
             'targetEntity' => $config['class']['user'],
             'cascade' =>
             array(
                 1 => 'persist',
             ),
             'mappedBy' => NULL,
             'inversedBy' => 'comments',
             'joinColumns' =>
             array(
                 array(
                     'name' => 'author_id',
                     'referencedColumnName' => 'id',
                 ),
             ),
             'orphanRemoval' => false,
        ));
        

        $collector->addAssociation($config['class']['user'], 'mapOneToMany', array(
             'fieldName' => 'comments',
             'targetEntity' => $config['class']['comment'],
             'cascade' =>
             array(
                 0 => 'remove',
                 1 => 'persist',
             ),
             'mappedBy' => 'author',
             'orphanRemoval' => true,
             'orderBy' =>
             array(
                 'createdAt' => 'DESC',
             ),
        ));
    }
}
