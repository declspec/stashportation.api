<?php
class ApiService {

    public function wrapSuccess($data, $status=200) {
        return self::createResponse(true, $data, null, $status);   
    }
    
    public function wrapError($error, $status=500) {
        return self::createResponse(false, null, (array)self::normalizeError($error), $status); 
    }
    
    public function sendSuccess($res, $data=null, $status=200) {
        return $res->status($status)->send($this->wrapSuccess($data, $status));
    }
    
    public function sendFailure($res, $error=null, $status=500) {
        return $res->status($status)->send($this->wrapError($error, $status));
    }
    
    // Utility functions for common errors/success
    public function sendCreated($res, $data=null) {
        return $this->sendSuccess($res, $data, 201);
    }
    
    public function sendBadRequest($res, $message="Bad Request") {
        return $this->sendFailure($res, $message, 400);   
    }
    
    public function sendFailedValidation($res, $errors) {
        return $this->sendFailure($res, $errors, 422);   
    }
    
    public function sendUnauthorized($res, $message="Unauthorized") {
        return $this->sendFailure($res, $message, 401);   
    }
    
    public function sendNotFound($res, $message="Not Found") {
        return $this->sendFailure($res, $message, 404);   
    }
    
    public function sendForbidden($res, $message="Forbidden") {
        return $this->sendFailure($res, $message, 403);   
    }
    
    public function sendServerError($res, $message="Internal Server Error") {
        return $this->sendFailure($res, $message, 500);   
    }

    private static function createResponse($success, $data, $errors, $status) {
        return array(
            "success" => $success,
            "status"  => $status,
            "errors"  => $errors,
            "data"    => $data
        );   
    }

    private static function normalizeError($err) {
        if ($err === null || $err === false)
            return null;
        else if ($err instanceof Exception)
            return $err->getMessage();
        else if (is_array($err))
            return array_map('self::normalizeError', $err);
        else if (is_object($err))
            return strval($err);
        
        return $err;
    }    
};