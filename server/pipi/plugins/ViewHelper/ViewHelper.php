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
 * @dependencies: P('error'), P('debug')
 */

class ViewHelper extends Plugin
{    
    /**
     * @see Plugin::dependencies_map()
     */
    public function dependencies_map()
    {
        return array(
            'debug' => 'Debugger',
            'error' => 'ErrorTracker',
            'js' => 'JsPacker'
        );
    }
    
    public function on_init()
    {
    }
    
    public function create_object($defs)
    {
        return new ViewObjectInstance($defs);
    }
 
}

class ViewObjectInstance
{
    private $attrs = array();
    private $values = array();
    private $validators = array();
    
    public function __construct($attrs)
    {
        $this->attrs = $attrs;
    }
    
    public function to_array()
    {
        return $this->values;
    }
    
    public function validate()
    {
        return array();
    }
    
    public function is_valid()
    {
        return count($this->validate() == 0);
    }

    public function to_json()
    {
        return json_encode($this->to_array());
    }

    public function __get($att)
    {
        if (isset($this->values[$att])) return $this->values[$att];
        return "";
    }

    public function __set($att, $value)
    {
        $this->values[$att] = $value;
    }

    public function from_array($array)
    {
        foreach ($this->attrs as $name=>$type) {
            if (isset($array[$name])) {
                $this->$name = $array[$name];
            }
        }
    }
    
    public function from_post()
    {
        $this->from_array($_POST);
    }
    
}

?>
