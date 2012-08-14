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
 *
 *
 */

class MongoDbHelper extends Plugin
{
    protected $_dependencies = array(
        'debug' => 'Debugger',
        'error' => 'ErrorTracker'
    );
    
    protected $_servers;
    protected $_connections = array();
    protected $_current_db = null;

    public function dependencies_map()
    {
        return array(
            'debug' => 'debug',
            'error' => 'error'
        );
    }
    
    public function on_init()
    {
        $this->P('error')->import_message_patterns(array(
            'MongoDbHelper.no_connection' => "No database connection found",
            'MongoDbHelper.connect_fail' => "Can't connect to database '{database}'",
            'MongoDbHelper.error' => "{error} from query = {query}"
        ));
        $this->_servers = $this->read_config('servers', array());
    }

    public function connect($key = 'main', $server = null)
    {
        // if we already have that connection, return it
        if (isset($this->_connections[$key])){
            $this->_current_db = $key;
            return $this->_connections[$key];
        }

        if (!isset($server)){
            if (isset($this->_servers[$key])){
                $server = $this->_servers[$key];
            }
        }

        // still can not get the config, give up
        if (!isset($server)) return false;

        // attemp to connect with this config
        $con = $this->_open_connection($server);
        if (isset($con) && ($con !== false)){ // success
            //store connection & db_config
            $this->_connections[$key] = $con;
            $this->_servers[$key] = $server;
            $this->_current_db = $key;
            return $this->_connections[$key];
        } else { //fail
            $this->P('error')->raise('MongoDbHelper.connect_fail', array('database'=>isset($server['connection']) ? $server['connection'] : $key));
            return false;
        }
    }
    
   /**
    * PRIVATE AREA
    */

    private function _open_connection($server)
    {
        $connection = isset($server['connection']) ? $server['connection'] : 'localhost:27017';
        $options = isset($server['options']) ? $server['options'] : array();

        $this->P('debug')->write("MongoDb: connect to: SERVER = " . $connection . " OPTIONS = " . json_encode($options) . ":", array('mongo','info'));
        
        return new Mongo($connection, $options);
    }
}

?>
