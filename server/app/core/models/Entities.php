<?php
define("UNLOADED", -32767);

class HasProperties
{
    public static $PROPERTIES = array();
    
    public static function all_properties()
    {
        return self::$PROPERTIES;
    }
}

class Entity extends HasProperties
{
    public $id;
    
    const AGENT = "Agent";
    
    public static $PROPERTIES = array();
    
    private static $_all_properties;
    
    public static function all_properties()
    {
        if (isset(self::$_all_properties))
        {
            return self::$_all_properties;
        }
        self::$_all_properties = array_merge(
            parent::all_properties(),
            self::$PROPERTIES
        );
        return self::$_all_properties;
    }
    
    public static function parse($data)
    {
        $class = get_called_class();
        $instance = new $class();
        
        foreach ($class::all_properties() as $prop) {
            if (isset($data[$prop])) {
                $instance->$prop = $data[$prop];
            }
        }
        return $instance;
    }
}

class AuditedEntity extends Entity
{
    const CREATED_TIME = 'created_time';
    public $created_time;
    
    const UPDATED_TIME = 'updated_time';
    public $updated_time;
    
    public static $PROPERTIES = array(CREATED_TIME, UPDATED_TIME);
}

class Agent extends AuditedEntity
{
    const NAME = 'name';
    public $name;
    
    const SETTING = 'setting';
    public $setting;
    
    public static $PROPERTIES = array(NAME, SETTING);
}

class AgentScript extends AuditedEntity
{
    const VERSION = 'version';
    public $version;
    
    const CONTENT = 'content';
    public $content;
    
    public static $PROPERTIES = array(VERSION, CONTENT);
}