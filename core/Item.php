<?php

/**
 * Item
 *
 * @package CorpusClassifier
 */
class Item {
    
    /** @var $_data */
    protected $_data = array ();
    
    
    /** @var $_table */
    protected $_table;
    
    
    /** @var $_connection Database */
    protected $_connection;
    
    
    /**
     * __construct
     *
     * @param $id int|array
     * @param $table
     *
     * @package CorpusClassifier
     */
    public function __construct ($id = array (), $table) {
    
        // Get database connection
        global $container;
        $this->_connection = $container['connection'];
        
        
        // Assign table
        $this->_table = $table;
        
        
        // An array?
        if (is_array ($id)) {
            $this->_data = $id;
        
        } else {
            
            // Prepare SQL statement
            $sql = "SELECT * FROM " . $table . " WHERE id = :id";
            
            
            // Run the query
            $this->_connection->prepare ($sql, array(':id' => $id));
            $data = $this->_connection->execute ();
            
            
            // Bind data
            $this->_data = reset ($data);
            
        }
        
    }
    
    
    /**
     * getTable
     *
     * @package CorpusClassifier
     */
    public function getTable () {
        return $this->_table;
    }
    
    
    /**
     * get
     *
     * @package CorpusClassifier
     */
    public function get ($value) {
        return isset ($this->_data[$value]) ? $this->_data[$value] : null;
    }    
    
    
    /**
     * __get
     *
     * @package CorpusClassifier
     */
    public function __get ($value) {
        return $this->get ($value);
    }
    
    
    /**
     * __set
     *
     * @package CorpusClassifier
     */
     
    public function __set ($name, $value) {
        $this->_data[$name] = $value;
    }
    
    
    /**
     * set
     *
     * @package CorpusClassifier
     */
     
    public function set ($name, $value) {
        $this->_data[$name] = $value;
    }
    
    
    /**
     * store
     *
     * @package CorpusClassifier
     */    
    public function store () {
        
        // Update
        if (isset ($this->_data['created_at'])) {
            $this->_connection->update ($this->getTable (), $this->_data);
        
        // Insert
        } else {
            $id = $this->_connection->insert ($this->getTable (), $this->_data);
            if ($id) {
                $this->_data['id'] = $id;
                $this->_data['created_at'] = date ('Y-m-d H:i:s');
            }
        }
    }
    
    
    /**
     * remove
     *
     * @package CorpusClassifier
     */    
    public function remove () {
        $this->_connection->remove ($this->getTable (), $this->getId ());
    }    
    
    
    /**
     * getArray
     *
     * @param $extra Array
     *
     * @package CorpusClassifier
     */
    public function getArray ($extra=array ()) {
        return array_merge ($this->_data, $extra);
    }
    
    
    /**
     * toJSON
     *
     * @package CorpusClassifier
     */
    public function toJSON () {
        return json_encode ($this->_data);
    }
}