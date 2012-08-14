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


class IdGeneratorByMysql extends IdGenerator
{
    protected $_database;
    protected $_table;
    protected $_column;
    
    public function dependencies_map()
    {
        return array(
            'debug' => 'debug',
        	'mysql' => 'mysql'
        );
    }
    
    /**
     * Initialize plugin
     */
    public function on_init()
    {
        parent::on_init();
        $this->_database = $this->read_config("database", "master");
        $this->_table = $this->read_config("table", "hilow");
        $this->_column = $this->read_config("column", "max");
    }
	
    protected function get_and_update_max()
    {
        $this->P('mysql')->connect($this->_database);
        
        $this->P('mysql')->execute('START TRANSACTION');
        $this->P('mysql')->execute('BEGIN');
        $rows = $this->P('mysql')->execute('SELECT `max` FROM `hilow`');
        if (count($rows)>0)
        {
            $this->P('mysql')->execute("UPDATE `$this->_table` SET `max` = `max` + $this->_range");
            $this->P('mysql')->execute('COMMIT');
            return $rows[0]['max'];
        }
        else
        {
            $this->P('mysql')->execute("INSERT INTO `$this->_table` (`max`) VALUES ($this->_range)");
            $this->P('mysql')->execute('COMMIT');
            return 0;
        }
   }
}
