<?php
require APP_DIR . 'common/Page.php';

class Register extends Page
{
    public function render()
    {
        if (P('guard')->is_already_login()) {
            //already logged in -> redirect
            $return_url = url64_decode(P('param')->read('return', home_url()));
            $this->redirect($return_url);
        } else {
            //otherwise, start register process
            $register_form = P('view')->create_object(array(
                'shortname' => 'string',
                'fullname' => 'string',
                'email' => 'string',
                'password' => 'string'
            ));
            
            if ($_POST) {
                $register_form->from_post();
                P('service')->load('UserService')->register($register_form);
                if (P('message')->no_error()) {
                   $main = P('tpl')->html('common/register_send.tpl.php', array('register_form' => $register_form));
                }
            }
            
            if (empty($site))
            {
                $main = P('tpl')->html('common/register_form.tpl.php', array('register_form' => $register_form));
            }
            
            echo P('tpl')->html('login.layout.tpl.php', array(
                'main' => $main
            ));
        }
    }
}

