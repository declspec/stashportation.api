<?php
// Really basic wrapper around the config array
class Configuration {
    private $_config;  
    
    public function __construct(array $config) {
        $this->_config = $config;   
    }
    
    public function get($key, $defaultValue=null) {
        return array_key_exists($key, $this->_config)
            ? $this->_config[$key]
            : $defaultValue;   
    }
    
    public function __get($name) {
        return $this->get($name);
    }
};