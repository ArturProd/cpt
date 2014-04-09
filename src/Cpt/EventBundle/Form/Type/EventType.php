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
                
                /*->add('save', 'submit', array(
                ))*/
                

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
            ->add('registrationallowed', 'checkbox', array(
                'label'     => 'form.event.authorize_reservations',
                'attr' => array(
                    'class' => 'input',
                    )
                ))
                
            // Event Cpt?
            ->add('cptevent', 'checkbox', array(
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
             
                                 
             // Corporate name
             ->add('corporatename', 'text', array(
                'label' => 'form.event.location_name',
                'required' => true,
                'attr' => array(
                    'maxlength'  => '50',
                    ),
             ))
                
             // Content
             ->add('content', 'ckeditor', array(
                    'config_name' => 'evt_config',          
            )) 
            

             ->add('maxnumattendees', 'integer', array(
                'label' => 'form.event.num_attendees_max',
                'attr' => array(  
                    'min' => 2,
                    'class' => 'input_text',
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
                'required' => true,
            ))
                
                
                
            // ************************
            // LOCATION
            // ************************
            // Show google map
             ->add('location_show_map', 'checkbox', array(
                'required' => false,
                'label' => 'form.event.location.show',
                'attr' => array(
                    'style'=>'display:none;'
                 )
                ))
              // Longitude
              ->add('locationlong', 'hidden', array(
                ))
              
              // Latitude
              ->add('locationlat', 'hidden', array(
                ))
            // City
                ->add('cityname', 'text', array(
                'label' => 'form.event.city',
                'attr' => array(
                    'class' => 'input_text',
                    'style'=>'display:none;',
                    'maxlength'  => '80',
                    ),
             ))
             
             // Postal Code
             ->add('citypostalcode', 'text', array(
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
             ->add('countryname', 'text', array(
                'label' => 'form.event.address',
                'required' => false,
                'attr' => array(
                    'class' => 'input_text',
                    'style'=>'display:none;',
                    'maxlength'  => '50',
                    ),
              ))
                
             // Country code
             ->add('countrycode', 'text', array(
                'label' => 'form.event.address',
                'required' => false,
                'attr' => array(
                    'class' => 'input_text',
                    'style'=>'display:none;',
                    'maxlength'  => '50',
                    ),
              ))
                
              // Address num
             ->add('addressnum', 'text', array(
                'label' => 'form.event.address',
                'required' => false,
                'attr' => array(
                    'class' => 'input_text',
                    'style'=>'display:none;',
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
