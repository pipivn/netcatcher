<?php

class MysqlHelperWithMemcache extends CBasePlugin
{
    protected $_db_list;
    protected $_cache;
    protected $_connections = array();
    protected $_current_db = null;
    
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
    
    public function on_init()
    {
        $this->_db_list = $this->read_config('databases', array());
        $this->_cache = $this->read_config('cache', false);
    }
    
    public function connect($key, $db_config = null)
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
        if (!isset($db_config)) return false;
        // attemp to connect with this config
        $con = $this->_open_connection($db_config);
        if (isset($con) && ($con !== false)){ // success
            //store connection & db_config
            $this->_connections[$key] = $con;
            $this->_db_list[$key] = $db_config;
            $this->_current_db = $key;
            return $this->_connections[$key];
        } else { //fail
            P('error')->raise('mysql.connect_fail');
            return false;
        }
    }
    
    public function select_row($args, $result_type = MYSQLI_ASSOC, $need_cache = false)
    {
        if ($need_cache && $this->_cache){
            $args = func_get_args();
            $key = $this->_gen_db_cache_key() . "::select_row(" . json_encode($args) . ')';
            return P('memcache')->cache(array($this, '_do_select_row'), $args, $key);
        } else {
            return $this->_do_select_row($args, $result_type);
        }
    }
    
    public function select_all($args, $result_type = MYSQLI_ASSOC, $need_cache = false)
    {   
        if ($need_cache && $this->_cache){
            $args = func_get_args();
            $key = $this->_gen_db_cache_key() . "::select_all(" . json_encode($args) . ')';
            // return P('memcache')->cache(array($this, '_do_select_all'), $args, $key);
        } else {
            return $this->_do_select_all($args, $result_type);
        }        
    }
    
    public function count_all($args, $need_cache = false)
    {   
         if ($need_cache && $this->_cache){
             $args = func_get_args();
             $key = $this->_gen_db_cache_key() . "::count_all(" . json_encode($args) . ')';
             return P('memcache')->cache(array($this, '_do_count_all'), $args, $key);
         } else {
             return $this->_do_count_all($args);
         }        
    }
    
    public function execute($query, $result_type = null)
    {
        P('debug')->write("<b>SQL</b>: " . $query."<br/>");

        $sql_result = mysqli_query($this->_get_connection(), $query);        
        
        if (isset($result_type)){
            if ($sql_result !== false){
                $result = array();
                while ($row = mysqli_fetch_array($sql_result, $result_type)) {
                    array_push($result, $row);
                }
            }
            return $result;
        }
        return true;        
    }
    
    public function update($args)
    {
        if (!key_exists('update', $args)) return null;    
        $query = "UPDATE " . $args['update'];        
        if (!key_exists('set', $args)) return null;        
        $query .= " SET " . $args['set'] . " ";        
        if (key_exists('where', $args) && ($args['where'] != ""))
        {
            $query .= ' WHERE ' . $args['where'];
        }        
        P('debug')->write("<b>SQL</b>: " . $query."<br/>");
        $result = mysqli_query($this->_get_connection(), $query);        
        if (mysqli_error($this->_get_connection())){
            P('debug')->write("<b>SQL error</b>: " . mysqli_error($this->_get_connection()) . "<br/>");
            return false;
        }
        return true;
    }
    
    public function insert($args)
    {   
        if (!key_exists('insert_into', $args)) return null;    
        $query = "INSERT INTO " . $args['insert_into'];
        
        if (!key_exists('columns', $args)) return null;        
        $query .= " ( " . $args['columns'] . " ) ";
        
        if (key_exists('values', $args) && ($args['values'] != ""))
        {
            $query .= ' VALUES (' . $args['values'] . ') ';
        }
        
        P('debug')->write("<b>SQL</b>: " . $query."<br/>");
        $result = mysqli_query($this->_get_connection(), $query);
        
        if (mysqli_error($this->_get_connection()) != ''){
            P('debug')->write("<b>SQL error</b>: " . mysqli_error($this->_get_connection()) . "<br/>");
            return false;
        }
        return true;
    }
    
    public function delete($args)
    {   
        if (!key_exists('delete_from', $args)) return null;    
        $query = "DELETE FROM " . $args['delete_from'];
        
        if (key_exists('where', $args) && ($args['where'] !=""))
        {
            $query .= ' WHERE ' . $args['where'];
        }
       
        P('debug')->write("<b>SQL</b>: " . $query."<br/>");
        $result = mysqli_query($this->_get_connection(), $query);
        
        if (mysqli_error($this->_get_connection())){
            P('debug')->write("<b>SQL error</b>: " . mysqli_error($this->_get_connection()) . "<br/>");
            return false;
        }
        return true;
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
        
        P('debug')->write("<b>SQL connect to</b>: HOST = " . $host . " DB = " . $database . ":");
        $g_link = mysqli_connect(
            $this->_get_full_host_name($host, $port),
            $user,
            $password
        );
        if ($g_link === false) return false;
        if (mysqli_select_db($g_link, $database)){ // success
            P('debug')->write(" success<br/>");
        } else { // fail
            P('debug')->write(" fail<br/>");
            $g_link = false;
        }
        $msg = mysqli_connect_error($g_link);
        if ($msg != ''){
            P('debug')->write("<b>SQL connect error</b>: " . $msg . "<br/>");            
        }
        return $g_link;
    }
    
    private function _do_select_row($args, $result_type = MYSQLI_ASSOC)
    {
        $query = "SELECT ";
        if (!key_exists('select', $args)) return null;
        
        $query .= $args['select'] . " FROM ";
        
        if (!key_exists('from', $args)) return null;
        
        $query .= $args['from'] . " ";
        
        if (key_exists('where', $args) && ($args['where'] != ""))
        {
            $query .= ' WHERE ' . $args['where'];
        }
        
        P('debug')->write("<b>SQL</b>: " . $query."<br/>");
        $result = mysqli_query($this->_get_connection(), $query);
        
        if ($result === false){
            P('debug')->write("<b>SQL error</b>: " . mysqli_error($this->_get_connection()) . "<br/>");
            return false;
        }
        
        $row = mysqli_fetch_array($result, $result_type);
        
        return $row;
    }
    
    private function _build_select_query($args)
    {        
        $query = "SELECT ";
        
        if (!isset($args['select'])) return null;
        $query .= $args['select'] . " FROM ";        
        if (!key_exists('from', $args)) return null;        
        $query .= $args['from'] . " ";
        
        
        if (key_exists('where', $args) && ($args['where'] != ""))        
        {
            $query .= ' WHERE ' . $args['where'];        
        }
        if (key_exists('group_by', $args) && $args['group_by']  != "")
        {
            $query .= ' GROUP BY ' . $args['group_by'];
        }
        if (key_exists('order_by', $args) && trim($args['order_by'])  != "")
        {
            $query .= ' ORDER BY ' . $args['order_by'];
        }
        if (key_exists('limit', $args) && $args['limit']  != "")
        {
            $query .= ' LIMIT ' . $args['limit'];
        }
        return $query;
    }
    
    private function _do_select_all($args, $result_type = MYSQLI_ASSOC)
    {        
        $query = $this->_build_select_query($args);
        P('debug')->write("<b>SQL</b>: " . $query."<br/>");
        $sql_result = mysqli_query($this->_get_connection(), $query);        
        if ($sql_result !== false){
            $result = array();
            while ($row = mysqli_fetch_array($sql_result, $result_type)) {
                array_push($result, $row);
            }
        }
        if (mysqli_error($this->_get_connection())){
            P('debug')->write("<b>SQL error</b>: " . mysqli_error($this->_get_connection()) . "<br/>");
            return false;
        }
        return $result;
    }
    
    private function _do_count_all($args)
    {
         $query = "SELECT count(*) FROM ";

         if (!key_exists('from', $args)) return null;

         $query .= $args['from'] . " ";

         if (key_exists('where', $args) && ($args['where'] !=""))
         {
             $query .= 'WHERE ' . $args['where'];
         }

         P('debug')->write("<b>SQL</b>: " . $query."<br/>");
         $result = mysqli_query($this->_get_connection(), $query);
         $row = mysqli_fetch_array($result, MYSQLI_NUM);
         if (!$row[0]) return 0;
         return $row[0];
    }
    
    private function _get_connection()
    {
        $key = $this->_current_db;
        if (!isset($key)) return false;
        if (!isset($this->_connections[$key])) return false;
        return $this->_connections[$key];
    }
    
   /**
    * Return a key string
    */
    private function _gen_db_cache_key()
    {
        $key = $this->_current_db;
        if (!isset($key)) return "";
        if (!isset($this->_db_list[$key])) return false;
        $db = $this->_db_list[$key];
        return $db['host'] . $db['database'] . $db['port'];
    }
    
    private function _get_full_host_name($host, $port)
    {
        if (is_null($port) || ($port=='')) return $host;
        return $host . ":" . $port;
    }
}

?>
