<?php

namespace Cpt\MainBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;

class UserOptionFormType extends AbstractType {

    private $class;
    
    public function __construct($class)
    {
        $this->class = $class;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('option_newsletter', 'checkbox', array(
                    'label' => 'useroptionform.option_newsletter',
                    'required' => false,
                ))
                ->add('option_mailoncomment', 'checkbox', array(
                    'label' => 'useroptionform.option_mailoncomment',
                    'required' => false,
                ))
                ->add('option_allow_phonedisplay', 'checkbox', array(
                    'label' => 'useroptionform.option_allow_phonedisplay',
                    'required' => false,
                ))
                ->add('option_emailme_eachevent', 'checkbox', array(
                    'label' => 'useroptionform.option_emailme_eachevent',
                    'required'  => false,
        ));
    }

    public function getName() {
        return 'cpt_useroption_form_type';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => $this->class,
        ));
    }

}
