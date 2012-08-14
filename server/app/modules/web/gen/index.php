<?php
require APP_DIR . 'common/Api.php';

class Index extends Api
{
    public function gen()
    {
        return array(P('id')->gen(), P('id')->gen(), P('id')->gen());
    }
    
    public function process()
    {
        $this->response_success($this->gen());
    }
}