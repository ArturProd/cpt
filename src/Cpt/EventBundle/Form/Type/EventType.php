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
use Ivory\GoogleMap\Places\AutocompleteType;

class EventType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder/*->add('country', 'places_autocomplete', array(
                    'mapped' => false,
                    // Javascript prefix variable
                    'prefix' => 'googleplace_country',

                    // Autocomplete bound (array|Ivory\GoogleMap\Base\Bound)
                    //'bound'  => $bound,

                    // Autocomplete types
                    'types'  => array(
                        AutocompleteType::CITIES,
                        // ...
                    ),

                    // TRUE if the autocomplete is loaded asynchonously else FALSE
                    'async' => true,

                    // Autocomplete language
                    'language' => 'en',
                ))*/
                
                ->add('save', 'submit', array(
                ))
                

            // ************************
            // GENERAL
            // ************************
                 
            // Enabled
            ->add('enabled', 'checkbox', array(
                'required' => false,
                'attr' => array(
                    'style'=>'display:none;'
                       )
                ))
                
            // Restricted
            ->add('restricted', 'checkbox', array(
                'required' => false,
                'attr' => array(
                    'style'=>'display:none;'
                       )
                ))
                
            // Approved
            ->add('approved', 'checkbox', array(
                'required' => false,
                'attr' => array(
                    'style'=>'display:none;'
                       )
                ))
            // Queue
            ->add('registration_allowed', 'checkbox', array(
                'label'     => 'form.event.authorize_reservations',
                'attr' => array(
                    'class' => 'input',
                    )
                ))
                
            // Event Cpt?
            ->add('cpt_event', 'checkbox', array(
                'label'     => 'form.event.cpt_event',
                'attr' => array(
                    'class' => 'input',
                    )
                ))
            
            // Title
            ->add('title', 'text', array(
                'label' => 'Titre',
                'attr' => array(
                    'maxlength'  => '80',
                    ),
             ))                
             
             // Content
             ->add('content', 'ckeditor', array(
                    'config_name' => 'evt_config',          
            )) 
            

             ->add('maxnumattendees', 'integer', array(
                'label' => 'form.event.num_attendees_max',
                'attr' => array(  
                    'min' => 2
               ),

            ))
            // Begin date time
            ->add('begin', 'datetime', array(
                'label' => 'form.event.begin_date_time',
                'date_widget' => 'single_text',
            ))

            // End date time
            ->add('end', 'datetime', array(
                'label' => 'form.event.end_date_time',
                'date_widget' => 'single_text',
                'required' => false,
            ))
                
                
                
            // ************************
            // LOCATION
            // ************************
            // Show google map
             ->add('location_show_map', 'checkbox', array(
                'required' => false,
                'label' => 'form.event.location.show',

                ))
              // Longitude
              ->add('location_long', 'hidden', array(
                ))
              
              // Latitude
              ->add('location_lat', 'hidden', array(
                ))
            // City
                ->add('city_name', 'text', array(
                'label' => 'form.event.city',
                'attr' => array(
                    'class' => 'input_text',
                    'style'=>'display:none;',
                    'maxlength'  => '80',
                    ),
             ))
             
             // Postal Code
             ->add('city_postal_code', 'text', array(
                'label' => 'form.event.postal_code',
                'required' => false,
                'attr' => array(
                    'class' => 'input_text',
                    'style'=>'display:none;',
                    'maxlength'  => '80',
                    ),
             )) 
             

             // Address
             ->add('address', 'text', array(
                'label' => 'form.event.address',
                'required' => false,
                'attr' => array(
                    'class' => 'input_text',
                    'style'=>'display:none;',
                    'maxlength'  => '50',
                    ),
             )) 
             
             // Country name
             ->add('country_name', 'text', array(
                'label' => 'form.event.address',
                'required' => false,
                'attr' => array(
                    'class' => 'input_text',
                    'style'=>'display:none;',
                    'maxlength'  => '50',
                    ),
              ))
                
             // Country code
             ->add('country_code', 'text', array(
                'label' => 'form.event.address',
                'required' => false,
                'attr' => array(
                    'class' => 'input_text',
                    'style'=>'display:none;',
                    'maxlength'  => '50',
                    ),
              ))
                
              // Address num
             ->add('address_num', 'text', array(
                'label' => 'form.event.address',
                'required' => false,
                'attr' => array(
                    'class' => 'input_text',
                    'style'=>'display:none;',
                    'maxlength'  => '50',
                    ),
              ))
                 
             // Corporate name
             ->add('corporate_name', 'text', array(
                'label' => 'form.event.location_name',
                'attr' => array(
                    'class' => 'input_text',
                    'maxlength'  => '50',
                    ),
             ));

    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'cpt_edit_event';
    }
  

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cpt\EventBundle\Entity\Event',
        ));
    }
    
}
