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
require dirname(__FILE__) . 'Specification.php';

class DomainDriven extends Plugin
{
    /* Configurations */
    protected $_dir;

    public function dependencies_map()
    {
        return array();
    }
    
    /**
     * Initialize plugin
     */
    public function on_init()
    {        
    }
}

function any() {
    $specs = func_get_args();
    return new LogicalOr($specs);
}

function all() {
    $specs = func_get_args();
    return new LogicalAnd($specs);
}

function one() {
    $specs = func_get_args();
    return new LogicalXor($specs);
}

function not() {
    $specs = func_get_args();
    return new LogicalNot($specs);
}

function lt($field, $value) {
    return new LessThan($field, $value);
}

function gt($field, $value) {
    return new GreaterThan($field, $value);
}

function lte($field, $value) {
    return new LessThanOrEqualTo($field, $value);
}

function gte($field, $value) {
    return new GreaterThanOrEqualTo($field, $value);
}

function eq($field, $value) {
    return new EqualTo($field, $value);
}

function between($field, $min, $max, $inclusive = true) {
    $f = $inclusive ? array('gte', 'lte') : array('gt', 'lt');
    return all($f[0]($field, $min), $f[1]($field, $max));
}
