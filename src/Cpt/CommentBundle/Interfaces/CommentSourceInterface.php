<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author cyril
 */
interface CommentSourceInterface {
    public function getCommentSourceName();
    
    public function getId();
    
    public function getAuthor();
}

?>
