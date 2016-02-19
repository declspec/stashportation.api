<?php
require(__DIR__ . '/configuration.php');
require(__DIR__ . '/vendor/php-di/dm.php');
require(__DIR__ . '/vendor/php-router/router.php');

class Application extends Router {
    const ANONYMOUS_MODULE = "__main__";
    
    public function __construct($baseUrl=null) {
        parent::__construct($baseUrl);
    }
    
    public static function bootstrap($appName, $env, callable $runFn=null) {
        $config = self::configure($appName, $env);
        $app = new Application($config->baseUrl);
        
        // Create the dependency manager with a basic module resolver
        // to load missing modules.
        $dm = new DependencyManager(function($name, $manager) {
            $fn = require(__DIR__ . "/modules/{$name}/config.php");
            if (!is_callable($fn))
                throw new UnexpectedValueException("configuration for module '{$name}' did not return a callable expression");
            
            call_user_func($fn, $manager);
        });
        
        // If $appName is an array of modules, create an anonymous parent module
        // that references the modules as dependencies so we can still create a 
        // centralized 'run' function. If $appName is a string then it should be the
        // name of an already existing module.
        $module = is_string($appName)
            ? $dm->module($appName)
            : $dm->module(self::ANONYMOUS_MODULE, $appName);

        // Attach the 'runFn' if one is provided.
        if ($runFn !== null) 
            $module->run($runFn);

        $dm->createInjector(array(function($provide) use(&$app, &$config) {
            $provide->constant("app", $app);
            $provide->constant("config", $config);
        }, $module->getName()));
        
        return $app;
    }
    
    private static function configure($appName, $env) {
        $baseDirectory = __DIR__ . '/config';

        $config = self::loadConfig($baseDirectory, $env);
        if (is_string($appName))
            $config = array_replace_recursive($config, self::loadConfig($baseDirectory . '/' . $appName, $env));
        
        if (!isset($config["env"]))
            $config["env"] = $env;

        return new Configuration($config);
    }
    
    private static function loadConfig($directory, $env) {
        if (!is_dir($directory))
            return array(); // no config found
            
        $default = @include($directory . '/default.php');
        if (!is_array($default))
            $default = array();
        
        if ($env) {
            $envSpecific = @include($directory . '/' . strtolower($env) . '.php');
            if (is_array($envSpecific))
                $default = array_replace_recursive($default, $envSpecific);
        }
        
        return $default;
    }
};