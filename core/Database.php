<?php

/**
 * Database
 *
 * @package CorpusClassifier
 */

class Database {

    /** @var String $dsn */
    protected $_dsn;
    
    
    /** @var String $user */
    protected $_user;
    
    
    /** @var String $password */
    protected $_password;
    
    
    /** @var PDO $pdo*/
    protected $_pdo;
    
    
    /** @var String query */
    protected $_query;
    
    
    /** @var Array params */
    protected $_params;
    
    
    /**
     * __construct
     *
     * @param $dsn
     * @param $user
     * @param $password
     *
     * @package CorpusClassifier
     */
    
    function __construct ($dsn, $user, $password) {
        $this->_dsn = $dsn;
        $this->_user = $user;
        $this->_password = $password;
    }

    
    /**
     * connect
     *
     * @return PDO
     *
     * @package CorpusClassifier
     */
    public function connect () {
    
        try {
            
            // Create PDO connection
            $pdo = new PDO ($this->_dsn, $this->_user, $this->_password);
            
            
            // Configure
            $pdo->setAttribute (PDO::ATTR_EMULATE_PREPARES, false);
            $pdo->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec ("set names utf8");
            
            
            // Attach to object
            $this->_pdo = $pdo;
            
            
            // Return
            return $this->_pdo;
            
        } catch (PDOException $e) {
            die ('Can\'t connect to database');
        }
            
    }
    
    
    /**
     * prepare
     *
     * @param $sql String
     * @param $params array|null
     *
     * @package CorpusClassifier
     */
    public function prepare ($sql, $params = array ()) {
    
        // Get PDO
        $pdo = $this->_pdo;
    
        
        // Create query
        $this->_query = $pdo->prepare ($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
        
        
        // Store parameters
        $this->_params = $params;
    
    }
    
    
    /**
     * execute
     *
     * @return Array
     *
     * @package CorpusClassifier
     */
    public function execute () {
    
        // Run query
        $this->_query->execute ($this->_params);
        
        
        // Return results
        try {
            return $this->_query->fetchAll (PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            return [];
        }
        
    }
    
    
    /**
     * getTotal
     *
     * @return int
     *
     * @package CorpusClassifier
     */
    public function getTotal () {
    
        // Run query
        $this->_query->execute ($this->_params);
        
        
        // Return results
        return $this->_query->fetchColumn(); 
        
    }    
    
    
    /**
     * insert
     *
     * @param $table String
     * @param $data array
     *
     * @return int
     *
     * @package CorpusClassifier
     */
    
    public function insert ($table, $data) {
    
        // Prepare SQL
        $sql = "INSERT INTO " . $table;
        $sql .= " (" . implode (',', array_keys ($data)) . ")";
        $sql .= " VALUES (\"" . implode ('", "', array_values ($data)) . "\")";
        
        
        // Run
        $this->prepare ($sql, array ());
        $this->execute ();
        
        
        return $this->_pdo->lastInsertId ();
    
    }
    
    
    /**
     * update
     *
     * @param $table String
     * @param $data array
     * @param $condition String
     *
     * @package CorpusClassifier
     */
    
    public function update ($table, $data, $condition='') {
    
        // Set the update condition
        if ( ! $condition) {
            $condition = ' ' . $table . '.id = ' . $data['id'];
        }
        
        
        // Prepare SQL
        $sql = "UPDATE " . $table . " SET " ;
        $statements = array ();
        foreach ($data as $key => $value) {

            // Exclude fields we don't want to update such as
            // the primary create or the created_at info
            if (in_array ($key, ['created_at', 'id'])) {
                continue;
            }
            
            $statements[] = $table . '.' . $key . '="' . $value . '"';
        
        }
        
        $sql .= implode (', ', $statements);
        $sql .= " WHERE " . $condition;
        
        
        // Run the query
        $this->prepare ($sql, array ());
        $this->execute ();
    
    }
    
    
    /**
     * remove
     *
     * Allows to delete records 
     *
     * @param $table String
     * @param $condition int|String
     *
     * @package CorpusClassifier
     */
    
    public function remove ($table, $condition='') {
    
        // Set the update condition
        if (is_int ($condition)) {
            $condition = ' ' . $table . '.id = ' . $condition;
        }
        
        
        // Prepare SQL
        $sql = "DELETE FROM " . $table . " WHERE " . $condition;
        
        
        
        // Run the query
        $this->prepare ($sql, array ());
        $this->execute ();
    
    }    
    
    
    
    /**
     * disconnect
     *
     * @package CorpusClassifier
     */    
    public function disconnect () {
        $this->_pdo = null;
    }

}