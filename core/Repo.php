<?php

/**
 * Repo
 *
 * This class represents the base repository to extract information
 * for all the custom classes
 *
 * It's need to define a _class and a _table
 *
 * Supports methods to:
 * - Retrieve a single instance by ID
 * - Retrieve all the instances (paged)
 *
 * @package CorpusClassifier
 */

abstract class Repo {
    
    /** $_class String */
    protected $_class;
    
    
    /** $_table String */
    protected $_table;
    
    
    /** $_connection Database */
    protected $_connection;
    
    
    /** 
     * __construct
     *
     * @package CorpusClassifier
     */
    
    public function __construct () {
        global $container;
        $this->_connection = $container['connection'];
    }
    
    
    /**
     * getById
     *
     * @param $id int
     *
     * @package CorpusClassifier
     */
    public function getById ($id) {
        return new $this->_class ($id);
    }


    /**
     * getAll
     *
     * @param $page int The first param of the limit
     * @param $offset int The number of items to retrieve
     * @param $order_field String Field to order
     * @param $order_direction String Direction
     * @param $filter String|null
     * @param $filter_params Array|null
     *
     * @package CorpusClassifier
     */
    
    public function getAll (
        $page=1,
        $offset=7,
        $order_field='id',
        $order_direction='DESC',
        $filter='',
        $filter_params=array ()
    ) {
        
        // Prepare filter
        if ($filter) {
            $filter = ' WHERE ' . $filter;
        }

   
        // Prepare SQL statement     
        $sql = "
            SELECT
                " . $this->_table . ".*
            
            FROM
                " . $this->_table . "
            
            " . $filter . " 
            
            ORDER BY
                " . $order_field . ' ' . $order_direction . "
                
            LIMIT
                :limit, :offset
        ";
        
        
        // Prepare params
        $params = array (
            ':limit' => ($page - 1) * $offset,
            ':offset' => $offset
        );
        
        
        // Run connection
        $this->_connection->prepare ($sql, $params);
        $results = $this->_connection->execute ();
        
        
        // Bind data
        $result = array ();
        foreach ($results as $row) {
            $result[] = new $this->_class ($row);
        }
        
        return $result;
        
    }
    
    
    /**
     * getTotal
     *
     * Returns the total of results inside in a table
     *
     * @param $filter String|null
     * @param $filter_params Array|null
     *
     * @package UCC
     */
    
    public function getTotal ($filter='', $filter_params=array ()) {
        
        // Prepare filter
        $_new_filter = '';
        if ($filter) {
            $_new_filter = ' WHERE ' . $filter;
        }
        
   
        // Prepare SQL statement
        $sql = "
            SELECT
                COUNT(" . $this->_table . ".id)
            
            FROM
                " . $this->_table . "
                " . $_new_filter . " 
        ";
        
        
        $this->_connection->prepare ($sql);
        return $this->_connection->getTotal ();
        
    }
}