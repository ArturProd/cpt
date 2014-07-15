<?php

namespace Application\Sonata\UserBundle\Entity;

/**
 * Description of UserOption
 *
 * @author cyril
 */
class UserOption {

    protected $id;
    
    protected $option_newsletter = true;
    
    protected $option_mailoncomment = false;
    
    // Affichage du numéro de téléphone uniquement aux organisateurs/animateurs d'un évènement
    protected $option_allow_phonedisplay = false;
     
    // Email me for each event created in my city
    protected $option_emailme_eachevent = false;

    protected $option_pro_includemenewsletter = true;
    
    protected $option_pro_displaymyresume = true;
    
    protected $option_pro_displayinprodirectory = true;
    
    // Display phone number on profile page
    protected $option_pro_phonedisplay = true;
        
    protected $pro_subscribed = false;
        
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

        
    public function getOptionAllowPhonedisplay() {
        return $this->option_allow_phonedisplay;
    }
    
    public function setOptionAllowPhonedisplay($option_allow_phonedisplay) {
        $this->option_allow_phonedisplay = $option_allow_phonedisplay;
    }
    
    
    // <editor-fold defaultstate="collapsed" desc="Getters & Setters - Options">

    public function getOptionProPhoneDisplay() {
        return $this->option_pro_phonedisplay;
    }
    
    public function setOptionProPhoneDisplay($option_pro_phonedisplay) {
        $this->option_pro_phonedisplay = $option_pro_phonedisplay;
    }
    
    public function getProSubscribed() {
        return $this->pro_subscribed;
    }
    
    public function setProSubscribed($prosubscribed) {
        $this->pro_subscribed = $prosubscribed;
    }
    
    public function getOptionProIncludemenewsletter() {
        return $this->option_pro_includemenewsletter;
    }
    
    
    public function setOptionProIncludemenewsletter($option_pro_includemenewsletter) {
        $this->option_pro_includemenewsletter = $option_pro_includemenewsletter;
    }
    
    public function getOptionProDisplaymyresume() {
        return $this->option_pro_displaymyresume;
    }
    
    
    public function setOptionProDisplaymyresume($option_pro_displaymyresume) {
        $this->option_pro_displaymyresume = $option_pro_displaymyresume;
    }
    
    public function getOptionProDisplayinprodirectory() {
        return $this->option_pro_displayinprodirectory;
    }
    
    
    public function setOptionProDisplayinprodirectory($option_pro_displayinprodirectory) {
        $this->option_pro_displayinprodirectory = $option_pro_displayinprodirectory;
    }
    
    public function getOptionEmailmeEachevent() {
        return $this->option_emailme_eachevent;
    }
    
    
    public function setOptionEmailmeEachevent($option_emailme_eachevent) {
        $this->option_emailme_eachevent = $option_emailme_eachevent;
    }

    public function getOptionNewsletter() {
        return $this->option_newsletter;
    }
    
    public function setOptionNewsletter($option_newsletter) {
        $this->option_newsletter = $option_newsletter;
    }
    
    public function getOptionMailoncomment() {
        return $this->option_mailoncomment;
    }
    
    public function setOptionMailoncomment($option_mailoncomment) {
        $this->option_mailoncomment = $option_mailoncomment;
    }
     // </editor-fold>

    
}
