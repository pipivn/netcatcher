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
 * @link         https://github.com/lamtq/base
 * @author       thanquoclam@gmail.com
 */

/**
 * Global Consts
 */

define('PIPI_DIR', dirname(__FILE__) . '/');

abstract class Plugin
{
    protected $_config = array();

    protected $_dependencies;

    abstract public function dependencies_map();
    
    abstract public function on_init();

    public function before_run(){}

    public function after_run(){}

    protected function debug($message, $tags = array(), $data = null)
    {
        if (gettype($tags) == 'string') $tags = array($tags);
        if (empty($tags)) $tags = array('info');
        $tags[] = get_class($this);
        $this->P('debug')->write('[' . get_class($this) . '] ' . $message, $tags, $data);
    }
    
    public function setup($config)
    {
        $this->_config = $config;
        $this->_dependencies = $this->dependencies_map();
        if (!empty($config['dependencies'])) {
            $this->_dependencies = array_merge($this->_dependencies, $config['dependencies']);
        }        
        $this->on_init();
    }

    public function P($key)
    {
        if (isset($this->_dependencies[$key])) {
            return P($this->_dependencies[$key]);
        } else {
            echo "[pipi error]: \"" . get_class($this) . "\" call plugin \"$key\" that did not register on the plugin's dependencies map.";
        }
    }

    public function read_config($key, $default)
    {
        if (isset($this->_config[$key])) {
            return $this->_config[$key];
        } else {
            return $default;
        }
    }

    public static function create($key, $def)
    {
        if (isset($def['disable']) && ($def['disable']===true)){
            return new DummyPlugin();
        }

        if (!isset($def['class_name'])) {
            echo "[pipi error]: can't not create plugin \"$key\". \"class name\" is missing in plugin definition";
            return new DummyPlugin();
        }

        $class_name = $def['class_name'];
        $location = self::_get_plugin_location($def);
        $class_file_path =  $location . $def['class_name'] . ".php";
        if (file_exists($class_file_path)) {
            // create new plugin instance
            include $class_file_path;

            $plugin = new $class_name;

            //import error code
            $error_code_file_path =  $location . "error_code.inc.php";
            if (file_exists($error_code_file_path)){
                include $error_code_file_path;
            }

            //read its config section in configuration file
            if (isset($def['config'])) {
                $plugin->setup($def['config']);
            } else {
                $plugin->setup(array());
            }
            return $plugin;
        } else {
            //Can't find plugin file
            echo "[pipi error]: can't not create plugin \"$key\". File $class_file_path is missing or invalid";
            return new DummyPlugin();
        }
    }

   /*
    * PRIVATE AREA
    */

    private static function _get_plugin_location($def)
    {
        if (isset($def['location'])){
            $location = $def['location'];
        } else {
            $location = "plugins/" . $def['class_name']. "/";
        }
        //Convert relative path to absolute path
        if ($location[0] != "/"){
            $location = dirname(__FILE__) . "/" . $location;
        }
        return $location;
    }
}


class DummyPlugin extends Plugin
{
    function on_init(){}
    
    function dependencies_map(){ return array(); }

    function __call($name, $arguments) {
        return;
    }
}

abstract class Action
{
    public function run(){}

    public function redirect($location)
    {
        header('Location: ' . $location);
    }

    public function forward_404()
    {
        header("Status: 404 Not Found");
    }
}

class Executer
{
    protected static $_instances = array();
    protected static $_plugins = array();
    protected static $_current_action;

    public static function get_plugin($key)
    {
        if (!isset(self::$_plugins[$key])){
            // Start that plugin!
            if (isset($GLOBALS['PLUGINS'][$key])){
                $plugin = Plugin::create($key, $GLOBALS['PLUGINS'][$key]);
                if ($plugin === false){
                    echo "[pipi error]: plugin \"$key\" start fail";
                }
                self::$_plugins[$key] = $plugin;
            } else {
                self::$_plugins[$key] = new DummyPlugin();
                echo "[pipi error]: plugin \"$key\" has not defined in plugin.config.php";
            };
        }
        return self::$_plugins[$key];
    }

    public static function get_current_action()
    {
        return self::$_current_action;
    }

    public static function run($action_name)
    {
        self::$_current_action = $action_name;

        if (!isset(self::$_instances[$action_name])){
            $instance = new $action_name();
            self::$_instances[$action_name] = $instance;
        }
        $instance = self::$_instances[$action_name];

        foreach ($GLOBALS['PLUGINS'] as $key=>$setting) {
            if (!empty($setting['before_run']) && $setting['before_run']) {
                P($key)->before_run();
            }
        }

        $result = $instance->run();

        foreach (array_reverse($GLOBALS['PLUGINS']) as $key=>$setting) {
            if (!empty($setting['after_run']) && $setting['after_run']) {
                P($key)->after_run();
            }
        }
    }
}

function run($action_name)
{
    return Executer::run($action_name);
}

function P($global_instance)
{
    return Executer::get_plugin($global_instance);
}
?>