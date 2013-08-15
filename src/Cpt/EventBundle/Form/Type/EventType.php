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
        $builder
                ->add('save', 'submit', array(
                ))
            // ************************
            // GENERAL
            // ************************
 
            ->add('queue', 'hidden')    
                
            // Published
            ->add('published', 'checkbox', array(
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
                    'class' => 'checkbox',
                    )
                ))
                
            // Event Cpt?
            ->add('cpt_event', 'checkbox', array(
                'label'     => 'form.event.cpt_event',
                'attr' => array(
                    'class' => 'checkbox',
                    )
                ))
            
            // Title
            ->add('title', 'text', array(
                'label' => 'Titre',
                'attr' => array(
                    'maxlength'  => '80',
                    'placeholder' => 'form.event.title',
                    ),
             ))                
             
             // Description
             ->add('description', 'ckeditor', array(
                    'config_name' => 'evt_config',          
            )) 
            

             ->add('maxnumattendees', 'integer', array(
                'label' => 'form.event.num_attendees_max',
                'attr' => array(  
                    'min' => 2
               ),

            ))
            // Begin date time
            ->add('begindatetime', 'datetime', array(
                'label' => 'form.event.begin_date_time',
                'date_widget' => 'single_text',
            ))

            // End date time
            ->add('enddatetime', 'datetime', array(
                'label' => 'form.event.end_date_time',
                'date_widget' => 'single_text',
                'required' => false,
            ))
                
                
                
            // ************************
            // LOCATION
            // ************************
            
            // City
                ->add('city_name', 'text', array(
                'label' => 'form.event.city',
                'attr' => array(
                    'class' => 'input_text',
                    'maxlength'  => '80',
                    'placeholder' => 'form.event.city',
                    ),
             ))
             
             // Postal Code
             ->add('city_postal_code', 'text', array(
                'label' => 'form.event.postal_code',
                'required' => false,
                'attr' => array(
                    'class' => 'input_text',
                    'maxlength'  => '80',
                    'placeholder' => 'Code Postal',
                    ),
             )) 
             
             // Street Number
             ->add('street_number', 'text', array(
                'label' => 'form.event.street_number',
                'required' => false,
                'attr' => array(
                    'class' => 'input_text',
                    'maxlength'  => '4',
                    ),
             )) 

             // Street
             ->add('street', 'text', array(
                'label' => 'form.event.street',
                'required' => false,
                'attr' => array(
                    'class' => 'input_text',
                    'maxlength'  => '50',
                    ),
             )) 

             // Additional Address
             ->add('additional_address', 'text', array(
                'label' => 'form.event.additional_address',
                'required' => false,
                'attr' => array(
                    'class' => 'input_text',
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