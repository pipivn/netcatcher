<?php
require APP_DIR . 'common/Page.php';

abstract class ProtectedPage extends Page
{
    public function is_ajax()
    {
        return $this->ajax_query() != '';
    }

    public function ajax_query()
    {
        $ajax = P('param')->read('ajax', '');
        if ($ajax != '') P('debug')->off();
        return $ajax;
    }

    public function run()
    {
        if (P('guard')->is_already_login()) {
            $this->render();
        } else {
            $this->redirect(url('auth/login', array('return' => url64($_SERVER['REQUEST_URI']))));
        }
    }

    /* COMMON RENDERING FUNCTION */

    public function render_message()
    {
        return P('tpl')->html('common/message.tpl.php', array(
            'message' => P('message')->last()
        ));
    }    
}