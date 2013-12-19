<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cpt\BlogBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

use Cpt\PublicationBundle\Interfaces\Manager\CommentManagerInterface as CommentManagerInterface;

class CommentAdmin extends Admin
{
    protected $parentAssociationMapping = 'post';

    protected $commentManager;

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        if (!$this->isChild()) {
            $formMapper->add('post', 'sonata_type_model_list');
//            $formMapper->add('post', 'sonata_type_admin', array(), array('edit' => 'inline'));
        }

        $commentClass = $this->commentManager->getClass();

        $formMapper
            ->add('message')
            ->add('status', 'choice', array('choices' => $commentClass::getStatusList(), 'expanded' => true, 'multiple' => false))
            ->add('author', 'sonata_type_model')

          ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('message')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('getStatusCode', 'text', array('label' => 'status_code', 'sortable' => 'status'))
            ->add('author')
        ;

        if (!$this->isChild()) {
            $listMapper->add('post');
        }

        $listMapper
            ->add('message');

    }

    /**
     * {@inheritdoc}
     */
    public function getBatchActions()
    {
        $actions = parent::getBatchActions();

        $actions['enabled'] = array(
            'label'            => $this->trans($this->getLabelTranslatorStrategy()->getLabel('enable', 'batch', 'comment')),
            'ask_confirmation' => false,
        );

        $actions['disabled'] = array(
            'label'            =>  $this->trans($this->getLabelTranslatorStrategy()->getLabel('disable', 'batch', 'comment')),
            'ask_confirmation' => false
        );

        return $actions;
    }

    /**
     * Update the count comment
     */
    private function updateCountsComment()
    {
        $this->commentManager->updateCommentsCount();
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist($object)
    {
        $this->updateCountsComment();
    }

    /**
     * {@inheritdoc}
     */
    public function postRemove($object)
    {
        $this->updateCountsComment();
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate($object)
    {
        $this->updateCountsComment();
    }

    /**
     * @param CommentManagerInterface $commentManager
     *
     * @return void
     */
    public function setCommentManager(CommentManagerInterface $commentManager)
    {
        $this->commentManager = $commentManager;
    }
}
