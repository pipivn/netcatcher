<?php
/**
 * PHP versions 4 and 5
 *
 * pipi : A tiny PHP web framework
 * Copyright 2010-2011, lamtq (thanquoclam@gmail.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @link          https://github.com/lamtq/base
 * @author        thanquoclam@gmail.com
 */

/**
 * DESCRIPTION
 * A plugin of pipi framework
 *
 * Get the parameters from request array, verify and return the params list
 * Default definition is (type=string, method=get, required=false)
 * @return params after validation
 *
 * E.g
 *
 * - P('params')->read("name") // get value "name" from url parameter as a string
 * - P('params')->read("name", "guest") // get value "name" from request in order, $_POST, if not, get from $_GET. If it's null, set to "guest" 
 * - P('params')->read("name", null, "get") // get value "name" from $_GET.
 * - P('params')->read("name", "guest", array("get", "post") // get value "name" from $_GET, if not, get from $_POST, if it's still null, set to "guest". 
 * 
 * @dependencies: P('error'), P('debug')
 */

class ParamsAnalyzer extends Plugin
{    
    /**
     * @see Plugin::dependencies_map()
     */
    public function dependencies_map()
    {
        return array(
            'debug' => 'Debugger',
            'error' => 'ErrorTracker'
        );
    }
    
    public function on_init()
    {
    }
    
    public function read($name, $default = null,  $methods = array('post', 'get'))
    {
        if (gettype($methods)=='string') $methods = array($methods);
        $value = null;
        foreach ($methods as $method) {
            if (($method == 'post') && isset($_POST[$name])) {
                return $_POST[$name];
            } else if (($method == 'get') && isset($_GET[$name])) {
                return $_GET[$name];
            } else if (($method == 'cookie') && isset($_COOKIE[$name])) {
                return $_COOKIE[$name];
            }
        }
        if ($value ==null && $default!= null) return $default;
        return $value;
    }
}
?>
