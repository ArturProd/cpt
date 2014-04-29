<?php

namespace Cpt\MainBundle\Entity;


/**
 * Description of Newsletter
 *
 * @author cyril
 */
class Newsletter {
    
    protected $id;
    protected $content = "";
    
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

        
    public function getContent() {
        return $this->content;
    }

    public function setContent($content) {
        $this->content = $content;
    }
    
}
