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

require dirname(__FILE__) . "/IdGenerator.php";

class IdGeneratorByMongo extends IdGenerator
{
    protected $_server;
    protected $_database;
    protected $_collection;
    protected $_document;
    
    public function dependencies_map()
    {
        return array(
            'debug' => 'debug',
        	'mongo' => 'mongo'
        );
    }
    
    /**
     * Initialize plugin
     */
    public function on_init()
    {
        parent::on_init();
        $this->_server = $this->read_config("server", "hilo-server");
        $this->_database = $this->read_config("database", "hilo");
        $this->_collection = $this->read_config("collection", "NormHiLoKey");
    }
	
    protected function get_and_update_max()
    {
        $m = $this->P('mongo')->connect($this->_server);
        $db = $this->_database;
        $key = array( '_id' => '1' );
        $data = array( '$inc' => array( 'ServerHi' => $this->_range ) );
        
        $result = $m->$db->command(array(
            'findAndModify' => $this->_collection,
            'query' => $key,
            'update' => $data,
            'new' => false, // To get the value before incrementing
            'upsert' => true,
            'fields' => array( 'ServerHi' => 1 ) //Only return "ServerHi" field
        ));
        if (empty($result['value'])) return 0;        
        return $result['value']['ServerHi'];
    }
}
