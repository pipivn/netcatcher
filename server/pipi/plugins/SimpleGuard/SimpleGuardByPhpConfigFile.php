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
require dirname(__FILE__) . "/SimpleGuard.php";

class SimpleGuardByPhpConfigFile extends SimpleGuard
{
    public function on_init()
    {
        parent::on_init();
        $this->_users_config_file = $this->read_config("users_config_file", PIPI_DIR . "../users.config.php");
        $this->_enable_password_hashing = $this->read_config("enable_password_hashing", true);
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
        require_once $this->_users_config_file;
        if (!empty($GLOBALS['USERS']))
        {
            if (!empty($GLOBALS['USERS'][$login_name]))
            {
                $user = $GLOBALS['USERS'][$login_name];
                if (md5($password) == $user['password_hash'])
                {
                    return array(
                        'user_id' => $user['user_id'],
                        'unique_name' => $login_name,
                        'display_name' => $user['display_name']
                    );
                }
            }
        }
        return null;
    }
}

?>
