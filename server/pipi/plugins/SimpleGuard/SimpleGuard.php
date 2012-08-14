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

class SimpleGuard extends Plugin
{
    protected $_session_key;
    protected $_session_timeout;
    protected $_current_usercard;

    /**
     * @see Plugin::dependencies_map()
     */
    public function dependencies_map()
    {
        return array();
    }
    
    /**
     * @see Plugin::on_init()
     */
    public function on_init()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        $this->_session_key = $this->read_config("session_key", "SimpleSessionAuth");
        $this->_session_timeout = $this->read_config("session_timeout", "30");        
    }
    
    /**
     * User already login to system
     */
    public function is_already_login()
    {
        $this->get_current_usercard();
        return !empty($this->_current_usercard);
    }
    
    /**
     * if user already login, return his usercard
     * a usercard is json string like this:
     * {
     * 		user_id: long
     *      	unique_name: string
     *      	display_name: string
     *      	logged_in_time: timestamp
     * }
     * else, return null
     */
    public function get_current_usercard()
    {
        if (empty($this->_current_usercard)) {
            if (!empty($_SESSION[$this->_session_key])) {
                $this->_current_usercard = json_decode($_SESSION[$this->_session_key]);
            }
        }
        return $this->_current_usercard;
    }

    /**
     * Attemp to login with their username and password
     * - Check & create a usercard for them
     * - If they are ok, then let them in
     * @param string $username
     * @param string $password
     */
    public function login($username, $password)
    {
        $usercard = $this->create_usercard($username, $password);
        if (!empty($usercard)) {
            $this->_let_user_in($usercard);
        }
    }
    
    /**
     * Override it with your create_usercard function 
     * By default, everyone are allowed
     * return a usercard like this:
     * array(
     *         user_id: long - required
     *         unique_name: string - option
     *         display_name: string - required
     * )
     */     
    protected function create_usercard($login_name, $password)
    {
        return array(
            'user_id' => 0,
            'display_name' => $login_name
        );
    }
    
    /**
     * Login as system actor
     */
    public function sys_login()
    {
        $usercard = json_encode(array(
            'user_id' => -1,
            'display_name' => "system"
        ));
        if (!empty($usercard)) {
            $this->_let_user_in($usercard);
        }
    }
    
    /**
     * Let user logout of system
     */
    public function logout()
    {
        unset($this->_usercard);
        unset($_SESSION[$this->_session_key]);
    }

    /*
     * PRIVATE FUNCTIONS
     */

    private function _let_user_in($usercard)
    {
        $usercard['logged_in_time'] = time();
        $_SESSION[$this->_session_key] = json_encode($usercard);
    }
}

?>
