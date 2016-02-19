<?php
class Request {
    public $method;
    public $url;
    public $originalUrl;
    public $path;
    public $params;
    public $body;
    public $query;    
    public $cookies;
    public $hostname;
    public $protocol;
    public $ip;
    public $xhr;
    public $json;
    
    public function __construct() {
        $this->method = strtoupper($_SERVER["REQUEST_METHOD"]);
        $this->url = $_SERVER["REQUEST_URI"];
        $this->originalUrl = $_SERVER["REQUEST_URI"];
        $this->hostname = $_SERVER["SERVER_NAME"];
        
        $this->ip = $_SERVER["REMOTE_ADDR"];
        $this->xhr = isset($_SERVER["X-REQUESTED-WITH"]) && $_SERVER["X-REQUESTED-WITH"] === "XMLHttpRequest";
        $this->json = $this->method === "POST" && stripos($_SERVER["CONTENT_TYPE"], "application/json") !== FALSE;
        
        // For some reason the protocol isn't nicely available, try and manufacture it
        $this->protocol = (isset($_SERVER["HTTPS"]) && filter_var($_SERVER["HTTPS"], FILTER_VALIDATE_BOOLEAN)) ||
			(isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] == "https") ? "https" : "http";
        
        $search = strpos($this->url, "?");
        
        $this->path = $search !== false 
            ? substr($this->url, 0, $search) 
            : $this->url;
        
        $this->query = $_GET;
        $this->body = $this->json ? json_decode(file_get_contents("php://input"), true) : $_POST;
        $this->cookies = $_COOKIE;        
    }
};
?>