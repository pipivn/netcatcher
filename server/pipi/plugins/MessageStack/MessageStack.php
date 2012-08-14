<?php
/**
 * PHP versions 4 and 5
 *
 * pipi 1.0 : A tiny PHP web framework
 * Copyright 2010-2011, lamtq (thanquoclam@gmail.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @link          https://github.com/lamtq/base
 * @author        thanquoclam@gmail.com
 */

class MessageStack extends Plugin
{
    
    /* Configurations */
    
    /* States */
    protected $_stack = array();
    protected $_error_count = 0;
    
    public function dependencies_map()
    {
        return array(
            'debug' => 'debug'
        );
    }    
    
    public function on_init()
    {        
    }

    public function clear()
    {
        reset($this->_stack);
        $this->_error_count = 0;
    }

    public function stack()
    {
        return $this->_stack;
    }

    public function no_error()
    {
        return $this->_error_count == 0;
    }
    
    /**
     * Push a message to stack
     * @param string $message
     * @param char $type. Type of message ('e' - error, 'w' - warning, 'i' - info)
     */
    public function push($message, $type = 'e')
    {
        if ($type == 'error') $this->_error_count++;
        $this->_stack[] = array(
            'type' => $type,
            'content' => $message
        );
    }
    
    /**
     * Return lastest message
     */
    public function pop()
    {
        $item = array_pop($this->_stack);
        if (!empty($item))
        {
            return $item;
        }
    }
    
    /**
     * Return lastest error
     */
    public function pop_error()
    {
        for($i=count($this->_stack)-1; $i>=0; $i--)
        {
            if ($this->_stack[$i]['type'] == 'e')
            {
                $item = $this->_stack[$i];
                unset($this->_stack[$i]);
                $this->_error_count--;                 
                return $item; 
            }
        }
        return null;
    }
}
