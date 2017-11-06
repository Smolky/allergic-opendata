<?php

/**
 * NotFound404
 *
 * @package UCC
 */
class NotFound404 extends BaseController {

    /**
     * handleRequest
     *
     * @package UCC
     */
    public function handleRequest () {
        $this->_response->setStatus ('404 Not Found');
        $this->_response->setContent (array ('ok' => false));
    }
}
