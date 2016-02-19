<?php
class DatabaseProvider {
    private $_connectionString;
    private $_username;
    private $_password;
    private $_options;
    
    public function setConnectionString($connectionString) {
        $this->_connectionString = $connectionString;
    }
    
    public function setCredentials($username, $password) {
        $this->_username = $username;
        $this->_password = $password;   
    }
    
    public function setOptions(array $options) {
        $this->_options = $options;   
    }
    
    public function _get() {
        if (empty($this->_connectionString))
            throw new UnexpectedValueException("No connection string was set for the database component");
        return new DatabaseService($this->_connectionString, $this->_username, $this->_password, $this->_options);   
    }
};

class DatabaseService {
    private static $DefaultOptions = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    );
    
    private $_connectionString;
    private $_username;
    private $_password;
    private $_options;
    
    private $_connection;
    private $_statementCache;
    
    public function __construct($connectionString, $username, $password, array $options=null) {
        $this->_connectionString = $connectionString;   
        $this->_username = $username;
        $this->_password = $password;   
        $this->_options = $options === null
            ? self::$DefaultOptions
            : $options + self::$DefaultOptions;  
        
        $this->_connection = null;
    }
    
    /*
    public function __call($name, array $args) {
        if ($this->_connection !== null && method_exists($this->_connection, $name))
            return call_user_func_array(array($this->_connection, $name), $args);
        throw new BadMethodCallException("Unknown method '$name'");
    }*/
    
    public function query($sql, array $params=null, $emulatePrepare=true) {
        $statement = $this->createStatement($sql, $emulatePrepare);
        $this->bindStatement($statement, $params);
        
        $statement->execute();
        $resultset = $statement->fetchAll();
        $statement->closeCursor();
        
        return $resultset;
    }
    
    public function execute($sql, array $params=null, $emulatePrepare=true) {
        $statement = $this->createStatement($sql, $emulatePrepare);
        $this->bindStatement($statement, $params);
        
        $statement->execute();
        $nrows = $statement->rowCount;
        $statement->closeCursor();
        
        return $nrows;
    }
    
    public function querySingle($sql, array $params=null, $emulatePrepare=true) {
        $resultset = $this->query($sql, $params, $emulatePrepare);
        return count($resultset) > 0
            ? $resultset[0]
            : null;
    }
    
    public function release() {
        // Garbage collection will release the connection.
        $this->_connection = null;
    }
    
    private function acquire() {
        if ($this->_connection === null)
            $this->_connection = new PDO($this->_connectionString, $this->_username, $this->_password, $this->_options);
        return $this->_connection;
    }
    
    private function createStatement($sql, $emulatePrepare) {
        $con = $this->acquire();
        $con->setAttribute(PDO::ATTR_EMULATE_PREPARES, $emulatePrepare);
        
        // Build the statement
        $statement = null;
        
        if ($emulatePrepare)
            $statement = $con->prepare($sql);
        else {
            // Use the statement cache to re-use statements matching the current one.
            // when using a database-level prepared statement (little-to-no point in caching an emulated statement)
            $lookup = hash("md5", $sql);
            $statement = isset($this->_statementCache[$lookup])
                ? $this->_statementCache[$lookup]
                : ($this->_statementCache[$lookup] = $con->prepare($sql));
        }  

        return $statement;
    }
    
    private function bindStatement(&$statement, array $bindValues = null) {
        if ($bindValues === null)
            return;
        
        foreach($bindValues as $key=>$value) {
            $statement->bindValue(is_numeric($key) ? ($key+1) : $key, $value);   
        }
    }
};
