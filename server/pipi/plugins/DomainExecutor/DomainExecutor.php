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

class DomainExecutor extends Plugin
{
    /* Configurations */
    protected $_domain_dir;
    
    /* States */
    protected $_loaded_services = array();
    protected $_loaded_entities = array();
    protected $_loaded_value_types = array();
    
    /**
     * Initialize plugin
     */
    public function on_init()
    {
        $this->P('error')->import_message_patterns(array(
            'DomainExecutor.file_not_found' => "Service file is not found '{path}'!",
            'DomainExecutor.service_not_found' => "Service '{service}' is not in mapping file!",
            'DomainExecutor.value_is_not_set' => "The value of {entity}->{attribute} hasn't set!",
            'DomainExecutor.attribute_is_not_defined' => "The attribute '{attribute}' of '{entity}' hasn't defined'!",
            'DomainExecutor.attribute_must_defined_by_an_array' => "The attribute '{attribute}' of '{entity}' must be defined by an array'!",
            'DomainExecutor.can_not_set_multiple' => "Can not set value for multiple entity attribute: '{attribute}' of '{entity}'!"
        ));
        
        $this->_domain_dir = $this->read_config("domain_dir", './domain/');
    }

    public function start()
    {
        $file_path = $this->_domain_dir . 'mapping.php';
        require $file_path;
    }

    public function set_dir($domain_dir)
    {
        $this->_domain_dir = $domain_dir;
    }

    public function require_value($value_type)
    {
        if (!empty($this->_loaded_value_types[$value_type])) {
            return;
        } else {
            $value_type_path = $this->_domain_dir . 'value_objects/' . $value_type . '.php';
            
            if (!file_exists($value_type_path)) {
                $this->P('error')->raise('DomainExecutor.file_not_found', array('path'=>$value_type_path));
            } else {
                require $value_type_path;
                $this->log("require $value_type_path");
            }
            $this->_loaded_value_types[$value_type] = 1;
        }
    }
    
    public function require_entity($entity_name)
    {
        if (!empty($this->_loaded_entities[$entity_name])) {
            return;
        } else {
            $entity_path = $this->_domain_dir . 'entities/' . $entity_name . '.php';

            if (!file_exists($entity_path)) {
                $this->P('error')->raise('DomainExecutor.file_not_found', array('path'=>$entity_path));
            } else {
                require $entity_path;
                $this->log("require $entity_path");
            }
            $this->_loaded_entities[$entity_name] = 1;
        }
    }

    public function load($service)
    {
        if (!empty($this->_loaded_services[$service])) {
            return $this->_loaded_services[$service];
        } else {
            if (!empty(DomainMapping::$Services[$service])) {
                $class = DomainMapping::$Services[$service];
            } else {
                $this->P('error')->raise('DomainExecutor.service_not_found', array('service'=>$service));
                return new DummyDomainService();
            }
            $this->log("Load service [" . $service . "]");
            $file_path = $this->_domain_dir . 'services/' . $class . '.php';

            if (!file_exists($file_path)) {
                $this->P('error')->raise('DomainExecutor.file_not_found', array('path'=>$file_path));
                $instance = new DummyDomainService();
            } else {
                require $file_path;
                $instance = new $class();
                $instance->on_init();
            }
            $this->_loaded_services[$service] = $instance;
            return $instance;
        }
    }
}

class DomainValueObject
{

}

class DomainEntity
{
    public $id;

    public $attributes = array();
    public $references = array();
    public $values = array();
    public $validators = array(); //TODO: implement this

    public function is_new()
    {
        return empty($this->id);
    }

    public function to_array($recursive = false)
    {
        $array = array();
        if (!empty($this->id)) $array['id'] = $this->id;
        foreach ($this->attributes as $att=>$def) {
            if (isset($this->values[$att])) {
                switch ($def['type']) {
                    case 'string':
                    case 'integer':
                    case 'float':
                        $array[$att] = $this->values[$att];
                        break;
                    case 'ValueObject':
                        $array[$att] = $this->values[$att]->to_array();
                        break;
                    case 'Entity':
                        if ($recursive) $array[$att] = $this->values[$att]->to_array($recursive);
                        break;
                }
            }
        }
        return $array;
    }

    public function to_json($recursive = false)
    {
        return json_encode($this->to_array($recursive));
    }

    public function get_default($att)
    {
        if (empty($this->attributes[$att])) {
            P('error')->raise('DomainExecutor.attribute_is_not_defined', array('attribute'=>$att, 'entity' => get_class($this)));
            return '';
        }

        if (gettype($this->attributes[$att]) != 'array') {
            P('error')->raise('DomainExecutor.attribute_must_defined_by_an_array', array('attribute'=>$att, 'entity' => get_class($this)));
            return '';
        }
        
        //multiple value
        if (!empty($this->attributes[$att]['multiple']) && $this->attributes[$att]['multiple']) return array();

        //single value
        switch ($this->attributes[$att]['type']) {
            case 'string':
                return '';
            case 'integer':
                return 0;
            case 'float':
                return 0;
            case 'ValueObject':
                $type = $this->attributes[$att]['class'];
                P('service')->require_value($type);
                return new $type();
            case 'Entity':
                return new DummyDomainEntity();
        }
        return '';
    }
    
    public function __get($att)
    {
        if (isset($this->values[$att])) return $this->values[$att];
        $this->values[$att] = $this->get_default($att);
        if (isset($this->values[$att])) return $this->values[$att];
        return null;
    }

    public function __set($att, $value)
    {
        if (empty($this->attributes[$att])) {
            P('error')->raise('DomainExecutor.attribute_is_not_defined', array('attribute'=>$att, 'entity' => get_class($this)));
            return '';
        }

        //validate
        if (!empty($this->validators[$att])) {
            if (is_a($this->validators[$att], 'DomainValueValidator')) {
                if (!$this->validators[$att]->execute($value)) return '';
            }
        }

        //can not set multiple value
        if (!empty($this->attributes[$att]['multiple']) && $this->attributes[$att]['multiple']) {
            P('error')->raise('DomainExecutor.can_not_set_multiple', array('attribute'=>$att, 'entity' => get_class($this)));
        } else {
            $this->values[$att] = $this->parse($this->attributes[$att]['type'], $value);
        }
    }

    private function parse($type, $value)
    {
        //single value
        switch ($type) {
            case 'string':
                return $value;
            case 'interger':
                return intval($value);
            case 'float':
                return floatval($value);
            case 'ValueObject':
                if (is_a($value, 'DomainValueObject')) return $value;
                break;
            case 'Entity':
                if (is_a($value, 'DummyDomainEntity')) return $value;
                break;
        }
        return null;
    }

    /* Factory Methods */

    public static function create_new()
    {
        return self::from_array(array());
    }

    public static function from_array($array)
    {
        $class = get_called_class();
        $instance = new $class();
        if (!empty($array['id'])) {
            $instance->id = intval($array['id']);
        }
        foreach ($instance->attributes as $name=>$type) {
            if (isset($array[$name])) {
                $instance->$name = $array[$name];
            }
        }
        return $instance;
    }
}

abstract class DomainService
{
    abstract public function on_init();
}

class DummyDomainService extends DomainService
{
    public function on_init(){}
    public function __call($name, $arguments) {
        return;
    }
}

class DummyDomainEntity extends DomainEntity
{
    public function __get($att_name)
    {
        return '';
    }
    public function __set($att_name, $att_value)
    {
    }
}

class UnloadedEntity extends DomainEntity
{
    public $class;
    
    public function __construct($id, $class)
    {
        $this->id = $id;
        $this->class = $class;
    }
}

abstract class DomainValueValidator
{
    public abstract function check($value);
    public $callback;
    public $message_format = "Value is invalid. Can't set {name} to {value} (check by {validator_class})";

    public function  __construct($callback = null) {
        if (!empty($callback)) {
            $this->callback = $callback;
        } else {
            $this->callback = array($this, 'default_callback');
        }
        if (!empty($message_format)) {
            P('error')->import_message_patterns(array(
                'DomainValueValidator.invalid' => array($message_format)
            ));
        }
    }

    private function default_callback($name, $value)
    {
        P('error')->raise('DomainValueValidator.invalid', array('name'=>$name, 'value' => $value, 'validator_class' => get_class($this)));
    }

    public function execute($name, $value)
    {
        if (!$this->check($value)) {
            call_user_func($this->callback, $name, $value);
            return false;
        }
        return true;
    }
}
