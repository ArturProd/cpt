<?php

namespace Cpt\MainBundle\Twig;

/**
 * Description of UserAccountLinkExtension
 *
 * @author cyril
 */
class UserAccountLinkExtension extends \Twig_Extension {
    
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('account_link', array($this, 'generateAccountlink'), array('is_safe' => array('all')))
        ); 
    }
    
    public function getName()
    {
        return 'cpt_account_link';
    }

    public function generateAccountlink($linktext, $userid, $class = null)
    {
        $classstring = $class ? "class='$class'" : "";
        $link = "<a href='#' onclick='ShowPage(PAGE_PROFILE_VIEW,$userid);' $classstring>$linktext</a>";
        
        return $link;
    }
    
    
}
