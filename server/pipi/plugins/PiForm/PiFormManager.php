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

require dirname(__FILE__) . '/PiForm.php';

class PiFormManager extends Plugin
{
    /* Configurations */
    private $_template;
    
    /* States */
    
    public function dependencies_map()
    {
        return array(
            'debug' => 'debug',
            'error' => 'error',
            'template' => 'tpl'
        );
    }
    
    public function on_init()
    {
        $this->_template = $this->read_config('template', "");
        $this->P('error')->import_message_patterns(PiForm::$ERROR_TABLE);
        PiForm::$manager = $this;
    }
    
    public function raise_error($code, $args)
    {
        $this->P('error')->raise($code, $args);
    }
    
    public function html($args)
    {
        $this->P('template')->html($this->_template, $args);
    }
    
    public function before_run() {
        parent::before_run();
    }
}