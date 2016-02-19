<?php
class UrlMatcher {
    const TOKEN_REGEXP = '/(\*|:([a-zA-Z_0-9]+)(\*|\+|\?|\()?)/';
    const TRIM_CHARS   = "/ \t\n\r\0\x0B";
    
    private $_regexp;
    private $_params;
    
    protected function __construct($regexp, array $params) {
        $this->_regexp = $regexp;
        $this->_params = $params;   
    }
    
    public function match($path, &$params) {
        if (preg_match($this->_regexp, $path, $matches) !== 1)
            return false;
        
        $params = array();   
        for($i = 0, $j = count($this->_params); $i < $j; ++$i)
            $params[$this->_params[$i]] = isset($matches[$i+1]) ? $matches[$i+1] : null;
        
        return true;
    }
    
    public static function create($path) {
        $path = trim($path, self::TRIM_CHARS);
        $length = strlen($path);
        $params = array();
        
        $regexp = "";
        $idx = 0;
        
        while(preg_match(self::TOKEN_REGEXP, $path, $matches, PREG_OFFSET_CAPTURE, $idx) === 1) {
            $baseOffset = $matches[0][1];
            // Append all of the non-matching content verbatim to the regexp.
            if ($baseOffset !== $idx)
                $regexp .= str_replace('/', '\/', substr($path, $idx, $baseOffset - $idx));
            
            // Calculate the new offset
            $idx = $baseOffset + strlen($matches[0][0]);
            $pattern = null;
            
            if (isset($matches[3]) && $matches[3][0] === "(") {
                // special 'pattern' token, which we need to pull out manually (recursive regex too hard)
                $depth = 1; // maintain the current parenthesis depth
                $start = $idx - 1;
                
                while ($depth > 0 && $idx < $length) {
                    if ($path[$idx] === ')' && $path[$idx-1] !== '\\')
                        --$depth;
                    else if ($path[$idx] === '(' && $path[$idx-1] !== '\\')
                        ++$depth;
                    ++$idx;   
                }   
                
                if ($depth > 0) // end of string without finding a closing parenthesus
                    throw new InvalidArgumentException('Unclosed group in regex provided for parameter "' . $matches[1][0] . '"');
                
                $pattern = str_replace('/', '\/', substr($path, $start, $idx - $start));
            }
            
            // Create the pattern
            if ($matches[0][0] === '*') {
                $pattern = '.*'.($idx !== $length ? '?' : '');
                if (self::isLeadingSlash($path, $baseOffset) && self::isTrailingSlash($path, $baseOffset, $length)) {
                    // "/*/" patterns can match any number of path segments (or none at all)
                    // including the literal '/' on both sides means that it can't match 0 segments 
                    // so manually patch this.
                    $pattern .= '\/?';
                    ++$idx;
                }                
            }
            else if ($pattern === null) {
                $multiplier = isset($matches[3]) && $matches[3][0] === '?' ? '*' : '+';
                $pattern = $idx === $length || $path[$idx] === "/"
                    ? "([^\\/]{$multiplier})"
                    : "(.{$multiplier}?)";  
            }
            
            // Push the param into the list if needed
            if (isset($matches[2]))
                $params[] = $matches[2][0];
            
            $regexp .= $pattern;
        }
        
        // Append any leftover literals to the regexp
        if ($idx < $length)
            $regexp .= str_replace('/', '\/', substr($path, $idx));
            
        return new UrlMatcher("/^\\/{$regexp}\\/?$/i", $params);
    }   
    
    private static function isLeadingSlash($path, $idx) {
        return $idx === 0 || $path[$idx-1] === '/';   
    }
    
    private static function isTrailingSlash($path, $idx, $length) {
        return $idx < ($length-1) && $path[$idx+1] === '/';   
    }
}