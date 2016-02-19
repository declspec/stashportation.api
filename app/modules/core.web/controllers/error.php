<?php
class ErrorController {
    // TODO: Replace this for your own custom errors
    
    public function notFound($req, $res) {
        $res->status(404)->send(self::wrapBasicHtml('Not Found', '<h1>Page not found</h1><p>The page you requested does not exist on the server</p>'));
    }  
    
    public function serverError($err, $req, $res) {
        $html = '<h1>Internal server error</h1>' .
        '<strong>' . self::escape($err->getMessage()) . '</strong>' .
        '<pre><code>' . self::escape($err->getTraceAsString()) . '</code></pre>';
        
        $res->status(500)->send(self::wrapBasicHtml('Server Error', $html)); 
    }
    
    private static function wrapBasicHtml($title, $html) {
        return '<!DOCTYPE html>' .
        '<html lang="en">' .
        '<head>' .
        '<meta charset="utf-8" />' .
        '<title>' . self::escape($title) . '</title>' .
        '</head>' .
        '<body>' . $html . '</body>' .
        '</html>';
    }
    
    private static function escape($html) {
        return htmlentities($html, ENT_QUOTES, "UTF-8");   
    }
};