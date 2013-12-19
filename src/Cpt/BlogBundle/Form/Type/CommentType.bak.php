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
use Symfony\Component\Form\AbstractType;

class CommentType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('message', 'textarea', array('max_length' => '512',  'attr' => array('class' => 'comment_textarea', 'cols' => '50', 'rows' => '3', 'placeholder' => 'Entrez un commentaire')))
            ->add('post_id', 'hidden', array('mapped' => false))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sonata_post_comment';
    }
}
