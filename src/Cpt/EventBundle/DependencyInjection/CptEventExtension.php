<?php

namespace Cpt\EventBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

use Sonata\EasyExtendsBundle\Mapper\DoctrineCollector;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class CptEventExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        
        $this->registerDoctrineMapping($config, $container);

    }
    
    public function registerDoctrineMapping(array $config)
    {
        $collector = DoctrineCollector::getInstance();


        foreach ($config['class'] as $type => $class) {
            if (!class_exists($class)) {
                return;
            }
        }
        
    //  <editor-fold defaultstate="collapsed" desc="Event <=> Registration">

//        $collector->addAssociation($config['class']['event'], 'mapOneToMany', array(
//             'fieldName' => 'registrations',
//             'targetEntity' => $config['class']['registration'],
//             'cascade' =>
//             array(
//                 0 => 'remove',
//                 1 => 'persist',
//             ),
//             'mappedBy' => 'event',
//             'orphanRemoval' => true,
//             'orderBy' =>
//             array(
//                 'createdAt' => 'DESC',
//             ),
//        ));
//        
//        $collector->addAssociation($config['class']['registration'], 'mapManyToOne', array(
//             'fieldName' => 'event',
//             'targetEntity' => $config['class']['event'],
//             'cascade' => array(
//             ),
//             'mappedBy' => NULL,
//             'inversedBy' => 'registrations',
//             'joinColumns' =>
//             array(
//                 array(
//                     'name' => 'event_id',
//                     'referencedColumnName' => 'id',
//                 ),
//             ),
//             'orphanRemoval' => false,
//        ));

    // </editor-fold>

    //  <editor-fold defaultstate="collapsed" desc="Registration <=> User">

        $collector->addAssociation($config['class']['user'], 'mapOneToMany', array(
             'fieldName' => 'registrations',
             'targetEntity' => $config['class']['registration'],
             'cascade' =>
             array(
                 0 => 'remove',
                 1 => 'persist',
             ),
             'mappedBy' => 'user',
             'orphanRemoval' => true,
             'orderBy' =>
             array(
                 'createdAt' => 'DESC',
             ),
        ));
        
        
        $collector->addAssociation($config['class']['registration'], 'mapManyToOne', array(
             'fieldName' => 'user',
             'targetEntity' => $config['class']['user'],
             'cascade' => array(
             ),
             'mappedBy' => NULL,
             'inversedBy' => 'registrations',
             'joinColumns' =>
             array(
                 array(
                     'name' => 'user_id',
                     'referencedColumnName' => 'id',
                 ),
             ),
             'orphanRemoval' => false,
        ));
        
    // </editor-fold>
     
    //  <editor-fold defaultstate="collapsed" desc="Event <=> User (Author)">
        $collector->addAssociation($config['class']['user'], 'mapOneToMany', array(
             'fieldName' => 'events',
             'targetEntity' => $config['class']['event'],
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
        
        $collector->addAssociation($config['class']['event'], 'mapManyToOne', array(
             'fieldName' => 'author',
             'targetEntity' => $config['class']['user'],
             'cascade' => array(
             ),
             'mappedBy' => NULL,
             'inversedBy' => 'events',
             'joinColumns' =>
             array(
                 array(
                     'name' => 'event_id',
                     'referencedColumnName' => 'id',
                 ),
             ),
             'orphanRemoval' => false,
        ));    
      // </editor-fold>            

    //  <editor-fold defaultstate="collapsed" desc="Event <=> Comment">
          $collector->addAssociation($config['class']['event'], 'mapOneToMany', array(
             'fieldName' => 'comments',
             'targetEntity' => $config['class']['comment'],
             'cascade' =>
             array(
                 0 => 'remove',
                 1 => 'persist',
             ),
             'mappedBy' => 'event',
             'orphanRemoval' => true,
             'orderBy' =>
             array(
                 'createdAt' => 'DESC',
             ),
        ));
          
        $collector->addAssociation($config['class']['comment'], 'mapManyToOne', array(
             'fieldName' => 'event',
             'targetEntity' => $config['class']['event'],
             'cascade' => array(
             ),
             'mappedBy' => NULL,
             'inversedBy' => 'comments',
             'joinColumns' =>
             array(
                 array(
                     'name' => 'event_id',
                     'referencedColumnName' => 'id',
                 ),
             ),
             'orphanRemoval' => false,
        ));
    // </editor-fold>    
         


    }
}
