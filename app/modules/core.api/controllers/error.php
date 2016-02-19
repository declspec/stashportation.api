<?php
class ErrorController {
    private $_apiService;
    
    public function __construct($ApiService) {
        $this->_apiService = $ApiService;
    }
    
    public function notFound($req, $res) {
        $this->_apiService->sendNotFound($res);
    }
    
    public function serverError($err, $req, $res) {
        $this->_apiService->sendServerError($res, $err);
    }
};