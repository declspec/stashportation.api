<?php
class Route {
    public $method;
    public $handler;
    public $middleware;
    public $path;
    
    public function __construct($method, $path, $middleware, $handler) {
        if (!is_callable($handler))
            throw new InvalidArgumentException('$handler must be a callable expression');
            
        $this->method = $method !== null ? strtoupper($method) : null;
        $this->path = $path;
        $this->handler = $handler;
        $this->middleware = $middleware !== null ? (array)$middleware : null;
    }
};