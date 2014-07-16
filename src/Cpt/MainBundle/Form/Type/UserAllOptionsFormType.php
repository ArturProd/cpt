<?php

namespace Cpt\MainBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;

class UserAllOptionsFormType extends AbstractType {
    
    private $class;
    private $useroptionformtype;
    
    public function __construct($userclass, $useroptionformtype)
    {
        $this->class = $userclass;
        $this->useroptionformtype = $useroptionformtype;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('locale', 'language', array(
                    'label'     => 'user.locale',
                    'required'  => true,
                    'choices'   => array('fr' => 'French', 'en' => 'English'),
                ))
                ->add('options', $this->useroptionformtype);
    }

    public function getName() {
        return 'cpt_useralloptions_form_type';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => $this->class,
            'cascade_validation' => true,            
        ));
    }
}
