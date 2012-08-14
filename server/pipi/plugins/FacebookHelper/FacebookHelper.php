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

/**
 * JsPacker Plugin
 *
 *
 */

class FacebookHelper extends Plugin
{
    /* configuration */
    protected $_app_id;
    protected $_app_secret;
	protected $_permissions;
    /* state */

    public function dependencies_map ()
    {
        return array(
            'debug' => 'debug'
        );
    }
	
    
    /**
     * Initialize plugin
     */
    public function on_init()
    {
        $this->_app_id = $this->read_config("app_id", false);
        $this->_app_secret = $this->read_config("app_secret", false);
    $this->_permissions = $this->read_config("default_permissions", "");
    }
	
	public function set_permission($permissions)
	{
    $this->_permissions = $permissions;
	}

    public function get_facebook_user()
    {
        $fb_cookie = array();
        if (isset($_COOKIE['fbs_' . $this->_app_id])) {
            parse_str(trim($_COOKIE['fbs_' . $this->_app_id], '\\"'), $fb_cookie);
            ksort($fb_cookie);
            $payload = '';
            foreach ($fb_cookie as $key => $value) {
                if ($key != 'sig') {
                  $payload .= $key . '=' . $value;
                }
            }
            if (md5($payload . $this->_app_secret) != $fb_cookie['sig']) {
                    return false;
            }

            $uid = $fb_cookie['uid'];
            $user = $this->_get_data_by_curl('https://graph.facebook.com/me?access_token=' . $fb_cookie['access_token']);
            $user['access_token'] = $fb_cookie['access_token'];
            return $user;
        }
        return false;
    }
	
	public function get_profile_picture_url($fb_id)
	{
    return "http://graph.facebook.com/$fb_id/picture";
	}

    public function reference_script()
    {
        return "http://connect.facebook.net/en_US/all.js";
    }

    public function register_script()
    {
        return "<script type=text/javascript>
            $(document).ready(function() {
                FB.init({
                        appId : '" . $this->_app_id . "',
                        status : true, // check login status
                        cookie : true // enable cookies to allow the server to access the session
                });
            });

            function facebook_login(callback)
            {
                FB.login( function(response) {
                    if (response.session) {
                        if (response.perms) {
            	if (callback) callback();
                            // user is logged in and granted some permissions.
                            // perms is a comma separated list of granted permissions
                        } else {
                            // user is logged in, but did not grant any permissions
                        }
                        //window.location.reload(true);
                    } else {
                        // user is not logged in
                    }
                }, {perms:'" . $this->_permissions . "'});
            }
    	
    	function facebook_login_as_new_user(callback)
    	{
        FB.getLoginStatus(function(response) {
        	if (response.session) {
            FB.logout(function(response){
            	facebook_login(callback);
            });
        	} else {
            facebook_login(callback);
        	}
        });
    	}
        </script>";
    }
	
	function send_message($message, $pageid, $token)
    {	
        $url = 'https://graph.facebook.com/' . $pageid . '/feed';
        $this->_post_data_by_curl($url, "message=$message&access_token=" . $token);
    }

    public function root_div()
    {
        return '<div id="fb-root"></div>';
    }

    public function print_js_script()
    {
        
    }

	private function _post_data_by_curl($url, $data, $type='json')
    {
        $ch = curl_init();    
        // set URL and other appropriate options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
        // grab URL and pass it to the browser
        $res = curl_exec($ch);	
        // close cURL resource, and free up system resources
        curl_close($ch);
        if ($res) { 
        	switch ($type)
        	{
            	case 'json':
                	return json_decode($res, true);
                	break;
        	}
        }
        return $res;
	}
	
    private function _get_data_by_curl($url, $type='json')
    {
        $ch = curl_init();
    
        // set URL and other appropriate options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Keep-Alive', 'Content-type: application/x-www-form-urlencoded;charset=UTF-8'));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
        // grab URL and pass it to the browser
        $res = curl_exec($ch);
    
        // close cURL resource, and free up system resources
        curl_close($ch);
        if ($res) { 
        	switch ($type)
        	{
            	case 'json':
                	return json_decode($res, true);
                	break;
        	}
        }
        return $res;
    }

}