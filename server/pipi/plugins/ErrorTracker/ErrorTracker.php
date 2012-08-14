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
 * @author           thanquoclam@gmail.com
 */

 
class ErrorTracker extends Plugin
{
    protected $_error_flag = false;
    protected $_error_stack = array();
    protected $_message_patterns;
    
    protected $_display_error;
    
    public function dependencies_map()
    {
        return array();
    }
    
    public function on_init()
    {       
        $this->_message_patterns = array('success' => 'Success');
        $this->_display_error = $this->read_config('display', true);
    }
    
    /**
     * Import new message patterns to message pattern table
     * @param <type> $message_patterns 
     */
    public function import_message_patterns($message_patterns)
    {
        $this->_message_patterns = array_merge($this->_message_patterns, $message_patterns);
    }

    /**
     * Raise an error, push to stack
     */ 
    public function raise($error_code, $error_args = array())
    {
        $this->_error_flag = true;
        array_push($this->_error_stack, array("code"=> $error_code, "args" => $error_args));
    }
    
    /**
     * Check and return the current status of error flag
     */
    public function has_error()
    {
        return $this->_error_flag;
    }
    
    /**
     * Lastest error in error stack
     */  
    public function lastest()
    {
        return end($this->_error_stack);
    }
    
    /**
     * Get error stack
     */  
    public function stack()
    {
        return $this->_error_stack;
    }
    
    /**
     * Generage an error message
     * Use:
     * error_message($error_code, array("key" => "value"));
     *
     * E.g.
     * $error_code_table = array(
     *      'error_1' => '{something} is wrong'
     * )     *
     * error_message('error_1', array('something'=>'Component 1'))
     *
     * return:     
     * 'Component 1 is wrong'
     *
     */
    public function build_message()
    {
        if (func_num_args()==1){
            $args = func_get_args();
            if (gettype($args[0]) == 'array'){
                $error_code = $args[0]['code'];
                $error_args = $args[0]['args'];
            } else if (gettype($args[0]) == 'string'){
                $error_code = $args[0];
                $error_args = array();
            }
        } else if (func_num_args()==2){
            $args = func_get_args();
            $error_code = $args[0];
            $error_args = $args[1];
        }        
        if (isset($this->_message_patterns[$error_code])){
            $error_format = $this->_message_patterns[$error_code];
        } else {
            $error_format = 'Unknown this error code.';
        }
        $message = $error_format;
        foreach ($error_args as $key => $value){
            $pattern = "/\{" . addslashes($key) . "\}/";
            $message = preg_replace($pattern, $value, $message);
        }
        return $message;
    }
    
    /*
     * Get full error messages string
     */
    public function error_string()
    {
        $full_message = '';
        foreach ($this->$_error_stack as $error) {
            $full_message .= $this->build_message($error['code'], $error['args']) . '\n';
        }
    }
}

/**
 * HISTORY
 *
 * Mar, 20 2010 by lamtq (thanquoclam@gmail.com)
 * created file
 *
 * Jan 20, 2011 by lamtq (thanquoclam@gmail.com)
 * modified
 */