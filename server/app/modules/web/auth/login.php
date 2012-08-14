<?php
require APP_DIR . 'common/Page.php';

class Login extends Page
{
    public function render()
    {
        if ($_POST) {
            $username = P('param')->read('username', '');
            $password = P('param')->read('password', '');

            if (!empty($username) && !empty($password)) {
                P('guard')->login($username, $password);
            }
            if (!P('guard')->is_already_login()) {
                $this->error("Invalid username and password!");
            }
        }
        
        if (P('guard')->is_already_login()) {
            
            $return_url = url64_decode(P('param')->read('return', home_url()));
            $this->redirect($return_url);
        
        } else {

            $main = P('tpl')->html('common/login_form.tpl.php', array(
            	'login_name' => !empty($username) ? $username : ""
        	));
        	    
            echo P('tpl')->html('login.layout.tpl.php', array(
                'main' => $main
            ));
        }
    }
}

