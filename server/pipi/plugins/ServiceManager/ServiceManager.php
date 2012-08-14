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

class ServiceManager extends Plugin
{   
    protected $_dir;
    protected $_loaded_classes = array();
    /**
     * @see Plugin::dependencies_map()
     */
    public function dependencies_map()
    {
        return array(
            'debug' => 'Debugger',
            'error' => 'ErrorTracker',
            'message' => 'MessageStack'
        );
    }
    
    public function on_init()
    {
        $this->_dir = $this->read_config("dir", '');
    }
    
    public function init($class, $path)
    {
        if (!empty($this->_loaded_classes[$class])) return;
        
        if ($path[0] != '/') $path = $this->_dir . $path;
        if (!file_exists($path)) {
            P('message')->push("file not found: " . $path);
            return false;
        }
        include_once $path;
        $this->_loaded_classes[$class] = 1;
        return true;
    }
    
    public function init_test($class)
    {
        return $this->init($class, 'tests/' . $class . '.php');
    }
    
    public function init_service($class)
    {
        return $this->init($class, 'services/' . $class . '.php');
    }
    
    public function init_model($class)
    {
        return $this->init($class, 'models/' . $class . '.php');
    }
    
    
    public function load($name)
    {
              
    }
    
    public function dummy($name)
    {   
    }
    
}

class TestSuite
{
    public $name;
    public $hash;
    
    /*
     * one test suite has a collection of cases, theses are methods that start with test_
     */
    private $_cases;
    private $_instance;
    
    /*
     * Load a test suite from source file
     */
    public function __construct($class_name)
    {
        $this->name = $class_name;
        $this->hash = substr(md5($class_name), 0, 8);
        $methods = get_class_methods($class_name);
        foreach ($methods as $method)
        {
            if (strpos($method, 'test_') === 0)
            {
                $this->_cases[] = $method;
            }
        }
    }
    
    public function test_all_cases()
    {
        $this->setup();
        $result = array();
        foreach ($this->_cases as $case)
        {
            $result[$case] = $this->test_one_case($case);
        }
        $this->teardown();
        return $result;
    }
    
    public function test_one_case($case)
    {
        $this->setup();
        return call_user_func(array($this->_instance, $case));
        $this->teardown();
    }
    
    private function setup()
    {
        unset($this->_instance);
        $this->_instance = new $this->name();
        if (method_exists($this->_instance, "setup")) {
            call_user_func(array($this->_instance, "setup"));
        }
    }
    
    private function teardown()
    {
        if (isset($this->_instance)) {
            if (method_exists($this->_instance, "teardown")) {
                call_user_func(array($this->_instance, "teardown"));
            }
            unset($this->_instance);    
        }
    }
    
}

class BaseService
{
        
}

?>
