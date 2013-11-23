<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cpt\BlogBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;

class PostType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                
            ->add('enabled', 'checkbox', array(
                'label'     => 'Publier l\'article',
                'required'  => false,
                'attr' => array(
                    'class' => 'checkbox',
                    )
                ))
             ->add('publishedhomepage', 'checkbox', array(
                'label'     => 'Toujours visible sur la page d\'accueil',
                'required'  => false,
                'attr' => array(
                    'class' => 'checkbox',
                    )
                ))
            ->add('title', null, array(
                'label' => 'form_post_type_title',
                'attr' => array(
                    'maxlength'  => '78',
                    'placeholder' => 'Titre',
                    'class' => 'article_edit_title'
                    ),
             ))
            ->add('rawcontent', 'ckeditor', array( 
                    'config_name' => 'article_config',
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'cpt_blog_edit_post';
    }
  

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cpt\BlogBundle\Entity\Post',
        ));
    }
    
}
