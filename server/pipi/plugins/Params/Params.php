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
 * @author 	      thanquoclam@gmail.com
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
 * - P('params')->read("name") // default is get value "name" from url parameter(method GET) as a string
 * - P('params')->read(array('key' => 'name', 'method' =>'post')) // single param
 * - P('params')->read(array(
 *      array('key'=>'name', 'method'=>'post', 'reduired'=>true),
 *      array('key'=>'age", 'default'=>18, 'method'=>'post'),
 *      array('key'=>'remember", 'default'=>0, 'method'=>'cookie')
 *   ))  // multiple params
 *
 * @dependencies: P('error'), P('debug')
 */
// The reason for using const here is it's faster than string when it's used for array key

class Params extends Plugin
{

    public function on_init()
    {
        $this->P('error')->import_message_patterns(array(
            'Params.invalid_type' => "'{param}' must be a(n) {type}",
            'Params.required' => "'{param}' is required",
            'Params.wrong_definition' => "wrong params definition, must be a string or an array",
        ));
    }

    public function read()
    {
        //#. Analyse & normalize arguments
        $args = func_get_args();
        $defs = array();
        foreach ($args as $arg){
            switch (gettype($arg)){
                case 'string':
                    array_push($defs, array('key' => $arg));
                    break;
                case 'array':
                    if (isset($arg['key']) && (gettype($arg['key']) == 'string')){
                        array_push($defs, $arg);
                    } else {
                        foreach ($arg as $key => $item){
                            switch (gettype($item)){
                                case 'array':
                                    if (isset($item['key'])){
                                        array_push($defs, $item);
                                    } else {
                                        $item['key'] = $key;
                                        array_push($defs, $item);
                                    }
                                    break;
                                case 'string':
                                    array_push($defs, array('key' => $item));
                                    break;
                            }
                        }
                    }
                    break;
                default:
                    $this->P("error")->raise('Params.wrong_definition');
                    break;
            }
        }

        //#. Inspect and validate those action params
        $params = array();
        foreach ($defs as $def){
            $key = $this->_readd($def['key'], null);
            if (!isset($key)) continue;
            $type = $this->_readd($def['type'], 'string');
            $methods = explode('|', $this->_readd($def['method'], 'get'));
            $value = null;
            foreach ($methods as $method){
                if (isset($params[$key])) break;
                switch ($method){
                    case 'cookie':
                        if (isset($_COOKIE[$key])) $value = $_COOKIE[$key];
                        break;
                    case 'post':
                        if (isset($_POST[$key])) $value = $_POST[$key];
                        break;
                    case 'get':
                        if (isset($_GET[$key])) $value = $_GET[$key];
                        break;
                }
            }
            if (is_null($value) && isset($def['default'])){
                $value = $def['default'];
            }
            $params[$key] = $this->_inspect_param($value, $type);
            // Check required
            $required = $this->_readd($def[], false);
            if (isset($key) && $required){
                if (!isset($params[$key]) || ($params[$key]=='')){
                    $this->P("error")->raise('Params.required', array('param' => $key));
                }
            }
        }
        $this->P("debug")->write('<b>Url params: </b>' . json_encode($params). "<br/>");
        return $params;
    }

    /**
     * PRIVATE AREA
     */

    /**
     * Read from $val, if it's null return $default
     */
    private function _readd(&$val, $default)
    {
        if (isset($val)){
            return $val;
        } else {
            return $default;
        }
    }

    /**
     * Inspect type & security from input parameters
     */
    private function _inspect_param($param, $type = 'string')
    {
        $result = null;
        if (is_null($param)) return null;
        switch ($type){
            case 'string':
                //$result = addslashes($param);
                $result = $param;
                break;
            case 'number':
                if (!is_float($param)){
                    // When param is equal '0'
                    $result = floatval($param);
                    if (($param != '0')&&($result==0)) $result = null;
                } else {
                    $this->P("error")->raise('Params.invalid_type', array('param' => $name, 'type' => 'number'));
                }
                break;
        }
        return $result;
    }
}

/**
 * HISTORY (major change only)
 *
 * Feb 6, 2011 by lamtq (thanquoclam@gmail.com)
 * created file
 *
 */
?>
