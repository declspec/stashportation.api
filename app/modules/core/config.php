<?php
require(__DIR__ . '/services/database.php');

return function($dm) {
    $module = $dm->module("core", array());
    
    $module->config(function($config, $DatabaseProvider) {
        $dbConfig = $config->get("db");
        
        if ($dbConfig !== null) {
            $DatabaseProvider->setConnectionString($dbConfig["connectionString"]);
            $DatabaseProvider->setCredentials($dbConfig["username"], $dbConfig["password"]);
            
            if (isset($dbConfig["options"]))
                $DatabaseProvider->setOptions($dbConfig["options"]);
        }
    });
   
    // Database provider
    $module->provider("Database", new DatabaseProvider());
};