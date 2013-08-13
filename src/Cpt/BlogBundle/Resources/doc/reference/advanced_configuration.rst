Advanced Configuration
======================


.. code-block:: yaml

    sonata_news:
        title:        Sonata Project
        link:         http://sonata-project.org
        description:  Cool bundles on top of Symfony2
        salt:         'secureToken'
        permalink_generator: cpt.blog.permalink.date # cpt.blog.permalink.category
        permalink:
            date:     %%1$04d/%%2$02d/%%3$02d/%%4$s => 2012/02/01/slug
        comment:
            notification:
                emails:   [email@example.org, email2@example.org]
                from:     no-reply@sonata-project.org
                template: 'CptBlogBundle:Mail:comment_notification.txt.twig'

        class:
            post:       Cpt\BlogBundle\Entity\Post
            tag:        Cpt\BlogBundle\Entity\Tag
            comment:    Cpt\BlogBundle\Entity\Comment
            category:   Cpt\BlogBundle\Entity\Category
            media:      Application\Sonata\MediaBundle\Entity\Media
            user:       Application\Sonata\UserBundle\Entity\User

        admin:
            post:
                class:       Cpt\BlogBundle\Admin\PostAdmin
                controller:  SonataAdminBundle:CRUD
                translation: CptBlogBundle
            comment:
                class:       Cpt\BlogBundle\Admin\CommentAdmin
                controller:  SonataAdminBundle:CRUD
                translation: CptBlogBundle
            category:
                class:       Cpt\BlogBundle\Admin\CategoryAdmin
                controller:  SonataAdminBundle:CRUD
                translation: CptBlogBundle
            tag:
                class:       Cpt\BlogBundle\Admin\TagAdmin
                controller:  SonataAdminBundle:CRUD
                translation: CptBlogBundle

    doctrine:
        orm:
            entity_managers:
                default:
                    #metadata_cache_driver: apc
                    #query_cache_driver: apc
                    #result_cache_driver: apc
                    mappings:
                        ApplicationCptBlogBundle: ~
                        CptBlogBundle: ~