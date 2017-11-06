<?php
/**
 * JSONResponse
 *
 * @package CorpusClassifier
 */

class JSONResponse extends Response {

    /**
     * __construct
     *
     * @package CorpusClassifier
     */
    public function __construct ($content="") {
    
        // Force the content type
        $this->setContentType ('application/json; charset=utf-8');
        
        
        // Delegate
        parent::__construct ($content);
        
    }

}