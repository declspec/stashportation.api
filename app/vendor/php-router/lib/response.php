<?php
class Response {
    // HTTP Status Codes
    const
		HTTP_100 = 'Continue',
		HTTP_101 = 'Switching Protocols',
		HTTP_200 = 'OK',
		HTTP_201 = 'Created',
		HTTP_202 = 'Accepted',
		HTTP_203 = 'Non-Authorative Information',
		HTTP_204 = 'No Content',
		HTTP_205 = 'Reset Content',
		HTTP_206 = 'Partial Content',
		HTTP_300 = 'Multiple Choices',
		HTTP_301 = 'Moved Permanently',
		HTTP_302 = 'Found',
		HTTP_303 = 'See Other',
		HTTP_304 = 'Not Modified',
		HTTP_305 = 'Use Proxy',
		HTTP_307 = 'Temporary Redirect',
		HTTP_400 = 'Bad Request',
		HTTP_401 = 'Unauthorized',
		HTTP_402 = 'Payment Required',
		HTTP_403 = 'Forbidden',
		HTTP_404 = 'Not Found',
		HTTP_405 = 'Method Not Allowed',
		HTTP_406 = 'Not Acceptable',
		HTTP_407 = 'Proxy Authentication Required',
		HTTP_408 = 'Request Timeout',
		HTTP_409 = 'Conflict',
		HTTP_410 = 'Gone',
		HTTP_411 = 'Length Required',
		HTTP_412 = 'Precondition Failed',
		HTTP_413 = 'Request Entity Too Large',
		HTTP_414 = 'Request-URI Too Long',
		HTTP_415 = 'Unsupported Media Type',
		HTTP_416 = 'Requested Range Not Satisfiable',
		HTTP_417 = 'Expectation Failed',
		HTTP_500 = 'Internal Server Error',
		HTTP_501 = 'Not Implemented',
		HTTP_502 = 'Bad Gateway',
		HTTP_503 = 'Service Unavailable',
		HTTP_504 = 'Gateway Timeout',
		HTTP_505 = 'HTTP Version Not Supported';
    
    private $_headers;
    private $_sent = false;
    
    public function __construct() {
        $this->_headers = self::parseHeaders(headers_list());
        if (function_exists('apache_response_headers')) {
            foreach(apache_response_headers() as $header=>$value)
                $this->_headers[strtolower($header)] = $value;
        }
    }
    
    public function status($statusCode) {
        if ($this->_sent)
            throw new RuntimeException("Cannot set headers after response body has already been sent");
 
        $message = "{$_SERVER['SERVER_PROTOCOL']} $statusCode " . constant("Response::HTTP_{$statusCode}");
        header($message, true, $statusCode);
        return $this;
    }
    
    public function send($body) {
        if ($body === null)
            $body = "";
        else if (is_string($body)) {
            // Specify content-type if it hasn't manually been assigned
            if (!$this->get("Content-Type"))   
                $this->set("Content-Type", "text/html; charset=utf-8");
        }
        else {
            if (!$this->get("Content-Type"))
                $this->set("Content-Type", "application/json; charset=utf-8");;
            $body = json_encode($body);
        }
        
        $this->_sent = true;
        echo $body;
        
        return $this;
    }
    
    public function get($header) {
        $header = strtolower($header);
        return isset($this->_headers[$header])
            ? $this->_headers[$header]
            : null;
    }
    
    public function set($header, $value=null) {
        if ($this->_sent)
            throw new RuntimeException("Cannot set headers after response body has already been sent");
        
        if (!is_array($header)) {
            $this->_headers[strtolower($header)] = $value;
            
            if (!is_array($value))
                header("{$header}: {$value}");
            else {
                foreach($value as $val)
                    header("{$header}: {$val}");   
            }
        }
        else {
            foreach($header as $key=>$value)
                $this->set($key, $value); 
        }
        
        return $this;
    }
   
    private static function parseHeaders($rawHeaders) {
        $parsed = array();
        foreach($rawHeaders as $header) {
            $parts = explode(':', $header, 2);
            $parsed[strtolower(trim($parts[0]))] = isset($parts[1]) ? trim($parts[1]) : "";
        }
        
        return $parsed;
    } 
}