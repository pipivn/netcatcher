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


class IoCManager extends Plugin
{
    /* Configurations */
    protected $_dir;

    private $_defs;
    private $_coms;

    public function dependencies_map()
    {
        return array(
            'debug' => 'debug',
            'error' => 'error'
        );
    }
    
    /**
     * Initialize plugin
     */
    public function on_init()
    {
        $this->P('error')->import_message_patterns(array(
            "IoCManager.source_is_missing" => "Component {com} does not have 'source' parameter, please check your mapping.",
            "IoCManager.source_not_found" => "Can not find 'source:{source}' of component {com}"
        ));
        
        $this->_service_dir = $this->read_config("service_dir", '');
        $this->_coms = array();               
    }

    /**
     * Mapping all components 
     */
    public function map($map)
    {
        foreach ($map as $key=>$def) 
        {
            if (gettype($def) == 'string') 
            {
                $def = array(
                    'source' => $def,
                    'depend' => array()
                );
            }
            if (empty($def['depend'])) $def['depend'] = array();
            $this->_defs[$key] = $def;
        }
    }
    
    /**
     * Load a component
     */
    public function load($name)
    {
        if (!isset($this->_defs[$name]))
            throw new Exception("Can not find component $name");

        if (!isset($this->_coms[$name]))
        {
            $this->_coms[$name] = $this->build_component($name, $this->_defs[$name]);
        }

        return $this->_coms[$name];
    }
    
    private function build_component($com_name, $def)
    {
        if (empty($def['source'])) {
            $this->P('error')->raise('IoCManager.source_is_missing', array('com'=>$com_name));
            return new DummyComponent();
        }
        
        $items = explode("::", $def['source']);
        
        $filePath = count($items) > 0 ? $items[0] : '';
        $className = count($items) > 1 ? $items[1] : '';

        if (file_exists($this->_service_dir . $filePath)) {
            $filePath = $this->_service_dir . $filePath;
        }
                
        if (!file_exists($filePath)){
            $this->P('error')->raise('IoCManager.source_not_found', array('source'=>$filePath, 'com'=>$com_name));
            return new DummyComponent();
        }
        
        include_once $filePath;
        if ($className == '') $className = basename($filePath, ".php");        

        $reflection = new ReflectionClass($className);
        $com = $reflection->newInstanceArgs();
        
        foreach ($def['depend'] as $property=>$name)
        {
            $com->$property = $this->load($name);
        }
        
        return $com;
    }
}

class DummyComponent
{
    function __call($name, $arguments) {
        return;
    }
}