<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cpt\EventBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Intl\Intl;

class CountryType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $countries = Intl::getRegionBundle()->getCountryNames();
        
        $builder->add('country', 'country', array(
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
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'cpt_country';
    }
  

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cpt\EventBundle\Entity\Country',
        ));
    }
    
}
