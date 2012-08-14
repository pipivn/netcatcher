<?php

class ArgumentException extends Exception 
{
    public function __construct($message, $code = 1, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

class BussinessException extends Exception
{
    public function __construct($message, $code = 100, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}