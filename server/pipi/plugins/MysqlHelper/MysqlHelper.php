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

class MysqlHelper extends Plugin
{
    protected $_dependencies = array(
        'debug' => 'Debugger',
        'error' => 'ErrorTracker'
    );
    
    protected $_db_list;
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
            'MysqlHelper.no_connection' => "No database connection found",
            'MysqlHelper.connect_fail' => "Can't connect to database '{database}'",
            'MysqlHelper.error' => "Mysql error: {error} from query = <pre>{query}</pre>",
            'MysqlHelper.config_not_found' => "Can not find the config for database '{key}'"
        ));
        $this->_db_list = $this->read_config('databases', array());
    }

    public function connect($key = 'master', $db_config = null)
    {
        // if we already have that connection, return it
        if (isset($this->_connections[$key])){
            $this->_current_db = $key;;
            return $this->_connections[$key];
        }

        // check in db_list if $db_config is not passed
        if (!isset($db_config)){
            if (isset($this->_db_list[$key])){
                $db_config = $this->_db_list[$key];
            }
        }

        // still can not get the config, give up
        if (!isset($db_config))
        {
            $this->P('error')->raise('MysqlHelper.config_not_found', array('key' => $key));
            return false;
        }

        // attemp to connect with this config
        $con = $this->_open_connection($db_config);
        if (isset($con) && ($con !== false)){ // success
            //store connection & db_config
            $this->_connections[$key] = $con;
            $this->_db_list[$key] = $db_config;
            $this->_current_db = $key;
            return $this->_connections[$key];
        } else { //fail
            $this->P('error')->raise('MysqlHelper.connect_fail', array('database'=>isset($db_config['database']) ? $db_config['database'] : ''));
            return false;
        }
    }
    

    public function __destruct()
    {
        // close all connections on destruct
        foreach ($this->_connections as $key => $item)
        {
            if (isset($item) && ($item != false)) {
                mysqli_close($item);
            }
            $this->_connections[$key] = false;
        }
    }

    public function select_row($query, $result_type = MYSQLI_ASSOC)
    {
        $current_connection = $this->_get_connection();
        if (empty($current_connection)) {
            $this->P('error')->raise('MysqlHelper.no_connection');
            return false;
        }

        $this->P('debug')->write('SQL: ' . $query, array('sql', 'info'));
        $result = mysqli_query($current_connection, $query);

        if (mysqli_error($this->_get_connection())){
            $this->P('error')->raise('MysqlHelper.error', array('query' => $query,'error'=>mysqli_error($this->_get_connection())));
            return false;
        }
        $row = mysqli_fetch_array($result, $result_type);
        return $row;
    }

    public function select_all($query, $result_type = MYSQLI_ASSOC)
    {
        $current_connection = $this->_get_connection();
        if (empty($current_connection)) {
            $this->P('error')->raise('MysqlHelper.no_connection');
            return false;
        }

        $this->P('debug')->write('SQL: ' . $query, array('sql', 'info'));
        $sql_result = mysqli_query($current_connection, $query);

        if ($sql_result !== false){
            $result = array();
            while ($row = mysqli_fetch_array($sql_result, $result_type)) {
                array_push($result, $row);
            }
        }

        if (mysqli_error($this->_get_connection())){
            $this->P('error')->raise('MysqlHelper.error', array('query' => $query,'error'=>mysqli_error($this->_get_connection())));
            return false;
        }
        return $result;
    }

   public function last_insert_id()
   {
      $current_connection = $this->_get_connection();
        if (empty($current_connection)) {
            $this->P('error')->raise('MysqlHelper.no_connection');
            return false;
        }
      return mysqli_insert_id($current_connection);
   }

    public function count_all($query)
    {
        $current_connection = $this->_get_connection();
        if (empty($current_connection)) {
            $this->P('error')->raise('MysqlHelper.no_connection');
            return false;
        }

        $this->P('debug')->write('SQL: ' . $query, array('sql', 'info'));
        $result = mysqli_query($current_connection, $query);

        $row = mysqli_fetch_array($result, MYSQLI_NUM);
        if (!$row[0]) return 0;
        return $row[0];
    }

    public function execute($query, $result_type = MYSQLI_ASSOC)
    {
        $this->P('debug')->write('SQL: ' . $query, array('sql', 'info'));
        $sql_result = mysqli_query($this->_get_connection(), $query);
        if (isset($result_type)){
            if (gettype($sql_result) !== 'boolean'){
                $result = array();
                while ($row = mysqli_fetch_array($sql_result, $result_type)) {
                    array_push($result, $row);
                }
                return $result;
            }
        }
        if (mysqli_error($this->_get_connection())){
            $this->P('error')->raise('MysqlHelper.error', array('query' => $query, 'error'=>mysqli_error($this->_get_connection())));
            return false;
        }
        return '';
    }

    public function truncate_all()
    {
        /* query all tables */
        $found_tables = $this->select_all('SHOW TABLES', MYSQLI_NUM);
        
        foreach($found_tables as $row){
            $this->execute('TRUNCATE TABLE `' . $row[0] .'`');
        }
    }

    public function drop_all()
    {
        /* query all tables */
        $found_tables = $this->select_all('SHOW TABLES', MYSQLI_NUM);

        foreach($found_tables as $row){
            $this->execute('DROP TABLE `' . $row[0] .'`');
        }
    }

    public function execute_file($filename)
    {
        $sql_content = file_get_contents($filename);
        $sql_array = explode(';', $sql_content);
        foreach ($sql_array as $sql_command) {
            $sql_command = trim($sql_command);
            if (!empty($sql_command)) $this->execute($sql_command);
        }
    }

   /**
    * PRIVATE AREA
    */

    private function _open_connection($db_config)
    {
        $host = isset($db_config['host']) ? $db_config['host'] : 'localhost';
        $port = isset($db_config['port']) ? $db_config['port'] : '';
        $user = isset($db_config['user']) ? $db_config['user'] : 'root';
        $password = isset($db_config['password']) ? $db_config['password'] : '';
        $database = isset($db_config['database']) ? $db_config['database'] : '';

        $this->P('debug')->write("SQL: connect to: HOST = " . $host . " DB = " . $database . ":", array('sql','info'));
        $g_link = mysqli_connect(
            $this->_get_full_host_name($host, $port),
            $user,
            $password
        );
        if ($g_link === false) return false;
        if (mysqli_select_db($g_link, $database)){ // success
            $this->P('debug')->write("SQL: connect success", array('sql','info'));
        } else { // fail
            $this->P('debug')->write("SQL: connect fail", array('sql','info'));
            $g_link = false;
        }
        $msg = mysqli_connect_error($g_link);
        if ($msg != ''){
            $this->P('error')->raise('MysqlHelper.error', array('error'=>$msg));
        }
        return $g_link;
    }

    private function _get_connection()
    {
        $key = $this->_current_db;
        if (!isset($key)) return false;
        if (!isset($this->_connections[$key])) return false;
        return $this->_connections[$key];
    }

    private function _get_full_host_name($host, $port)
    {
        if (is_null($port) || ($port=='')) return $host;
        return $host . ":" . $port;
    }
}

?>
