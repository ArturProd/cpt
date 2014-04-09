<?php

namespace Cpt\MainBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('firstname', 'text', array(
                'label' => 'user.firstname',
                'attr' => array(
                    'maxlength'  => '15',
                    ),
             ))
            ->add('lastname', 'text', array(
                'label' => 'user.lastname',
                'attr' => array(
                    'maxlength'  => '15',
                    ),
             ))
             ->add('website', 'text', array(
                'label' => 'user.website',
                'attr' => array(
                    'maxlength'  => '150',
                    ),
             ))           
            ->add('biography', 'textarea', array(
                'label' => 'user.biography',
                'attr' => array(
                    ),
             ))
             ->add('phone', 'text', array(
                'label' => 'user.phone',
                'attr' => array(
                    'maxlength'  => '15',
                    ),
             ))
            ->add('address', 'text', array(
                'label' => 'user.address',
             ))
            ->add('locale', 'locale', array(
                'label' => 'user.locale',
             ))
            ->add('email', 'text', array(
                'label' => 'user.email',
             ))
            ->add('password', 'text', array(
                'label' => 'user.password',
             ))
            ->add('passwordconfirm', 'text', array(
                'label' => 'user.passwordconfirm',
             ))
            ->add('newsletter', 'checkbox', array(
                'label' => 'user.passwordconfirm',
             ))
            ->add('professional', 'checkbox', array(
                'label' => 'user.professional',
             ))
            ;   
    }
    
    public function getName()
    {
        return 'cpt_form_user';
    }
  

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Application\Sonata\UserBundle\Entity\User',
        ));
    }
}