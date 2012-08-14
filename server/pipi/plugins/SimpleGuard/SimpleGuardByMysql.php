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
 * @author           thanquoclam@gmail.com
 */
require dirname(__FILE__) . "/SimpleGuard.php";

class SimpleGuardByMysql extends SimpleGuard
{
	
	public function dependencies_map()
    {
        return array( 
        	'mysql' => 'mysql'
    	);
    }
    
    public function on_init()
    {
        parent::on_init();
        $this->_user_database = $this->read_config("user_database", "master");
        $this->_user_table = $this->read_config("user_table", "users");
        $this->_login_name_column = $this->read_config("login_name_column", "username");
        $this->_password_hash_column = $this->read_config("password_hash_column", "password");
    }
    
    /**
     * Check loginname & password
     * Return a usercard like this:
     * array(
     *         user_id: long - required
     *         unique_name: string - option
     *         display_name: string - required
     * )
     */ 
    protected function create_usercard($login_name, $password)
    {
        $this->P('mysql')->connect($this->_user_database);
        $password = md5($password);
        $row = $this->P('mysql')->select_row('
            SELECT 
               `id`,
               `' . $this->_login_name_column . '` as u,
               `' . $this->_password_hash_column . '` as p
            FROM ' . $this->_user_table . '
            WHERE `' . $this->_login_name_column . '` = "' . $login_name . '"
              AND `' . $this->_password_hash_column . '` = "' . $password . '"
        ');
        
        if (!empty($row)) {            
            return array(
                'user_id' => $row['id'],
                'unique_name' => $row['u'],
                'display_name' => $row['u']
            );
        }
        return null;
    }    
}

?>
