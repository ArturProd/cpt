<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\FOS\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use Symfony\Component\Intl\Intl;

class RegistrationFormType extends BaseType
{
    private $class;

    /**
     * @param string $class The User class name
     */
    public function __construct($class)
    {
        parent::__construct($class);
        
        $this->class = $class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       parent::buildForm($builder, $options);
        
         $countries = Intl::getRegionBundle()->getCountryNames();
        $builder->add('firstname', null, array('label' => 'form.registration.firstname'))
                ->add('lastname', null, array('label' => 'form.registration.lastname'))
                ->add('country_code', 'country', array(
                    'label' => 'form.registration.country', 
                    'choices' => $countries,
                    'preferred_choices' => array(
                        'FR', // France
                        'UK', // UK
                        'ES', // Spain
                        'DE', // Germany
                        'BE', // Belgium
                        'DK', // Denmark
                        'EL', // Greece
                        'IT', // Greece
                        'NL', // Netherlands
                        'US', // USA

                    ),
                ));
        

        $builder->remove('username'); // it is added in parent form, but we don't want it
    }

    public function getName()
    {
        return 'application_user_registration';
    }
}
