<?php

require APP_DIR . 'common/ProtectedPage.php';

class Index extends ProtectedPage
{
    public function render_side()
    {
        return P('tpl')->html('common/sidebar.tpl.php', array(
           'list' => array('vnexpress.net', 'rongbay.com')
        ));
    }

    public function render_main()
    {
        return "<a href='/agent'>Agents</a>";
    }

    public function render()
    {        
        echo P('tpl')->html('one_column.layout.tpl.php', array(
            'side' => $this->render_side(),
            'main' => $this->render_main()
        ));
    }
}