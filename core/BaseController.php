<?php

/**
 * BaseController
 *
 * @package UCC
 */
abstract class BaseController {


    /** @var $_template */
    protected $_template;
    
    
    /** @var $_container */
    protected $_container;
    
    
    /** @var response */
    protected $_response;
    
    
    /**
     * handleRequest
     *
     * This method has to be implemented by the controllers
     *
     * @package UCC
     */
    
    public abstract function handleRequest () ;
    
    
    /**
     * handles
     *
     * @package UCC
     */
    
    public function handle () {
        $this->handleRequest ();
        return $this->_response;
    }
    
    
    /**
     * __construct
     *
     * @package UCC
     */
    public function __construct () {
        
        // Reference container
        global $container;
        
        
        // Store
        $this->_container = $container;
        
       
        // Get class info for the current controller
        $class_info = new ReflectionClass ($this);
        $class_path = dirname ($class_info->getFileName()); 
        $class_path = str_replace (getcwd (), '', $class_path);
        $class_path = trim ($class_path, '/');
        
        
        // Fetch template system
        $twig = $container['templates'];
        $loader = $container['loader'];
        if (is_dir ($class_path . '/templates/')) {
            $loader->addPath($class_path . '/templates/');
        }
        
        
        // Store
        $this->_template = $twig;
        
        
        // Create response
        $this->_response = new JSONResponse ();

    }
}
