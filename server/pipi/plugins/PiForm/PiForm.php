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

class PiForm
{
    public static $SINGLELINE = 'singeline';
    public static $SINGLECHOICE = 'singechoice';
    public static $MULTICHOICE = 'multichoice';
    
    public static $ERROR_TABLE = array(
        'PiFormManager.field_already_existed' => "Field {name} has already existed.",
    );
    
    public static $manager;
    
    public $name;
    
    public function __construct($manager, $name) 
    {
        $this->manager = $manager;
        $this->name = $name;
    }
        
    private function raise_error($code, $args)
    {
        $this->manager->raise_error($code, $args);
    }
    
    /* States */
    protected $fields = array();
    protected $values = array();
    protected $errors = array();
    protected $is_dirty = true;
    
    public function add($name, $type, $options = array())
    {
        $options['type'] = $type;
        if (!empty($this->fields[$name]))
        {
            $this->raise_error('PiFormManager.field_already_existed', array('name' => $name));
        }
    }
            
    public function render()
    {
        return $this->manager->render(
            $this->fields, 
            $this->values, 
            $this->errors
        );
    }
    
    public function get_errors()
    {
        if ($this->is_dirty) 
        {
            $this->_validate();
            $this->is_dirty = false;
        }
        return $this->errors;
    }
    
    public function ok()
    {
        return count($this->get_errors()) == 0;
    }
    
    private function _validate()
    {
        
    }
    
    public function update($data)
    {
        $this->is_dirty = true;
        if (gettype($date) == 'array')
        {
            
        }
    }
    
    public function values()
    {
        return $this->values;
    }
}

