<?php
require APP_DIR . 'common/Page.php';

class Logout extends Page
{
    public function render()
    {
        P('guard')->logout();
        $return_url = url64_decode(P('param')->read('return', home_url()));
        $this->redirect($return_url);
    }
}

