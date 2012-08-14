<?php

class Validator extends Plugin
{
    protected static $_rules = array();
    protected static $_errors = array();

    public function  __construct($rules)
    {
        foreach ($rules as $rule)
        {
            $this->setRule($rule[0], $rule[1]);
        }
    }

    public function setRule($name, $validator)
    {
        if (!isset(self::$_rules[$name]))
        {
            self::$_rules[$name] = array();
        }
        self::$_rules[$name][] = $validator;
    }

    public function validate($name, $value)
    {
        if (isset(self::$_rules[$name]))
        {
            foreach (self::$_rules[$name] as $validator)
            {
                $error = $validator->validate($value);
                if (!empty($error))
                {
                    self::$_errors[$name] = $error;
                }
            }
        }
    }

    public function getErrors()
    {
        return self::$_errors;
    }
}

abstract class Vega_Validator_Rule
{
    public abstract function validate($value);
}

class Vega_Validator_Rule_NotEmpty extends Vega_Validator_Rule
{
    protected $_message;

    public function  __construct($message)
    {
        $this->_message = $message;
    }

    public function validate($value)
    {
        if (empty($value)) return $this->_message;
        return '';
    }
}

