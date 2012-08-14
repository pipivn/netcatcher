<?php
require APP_DIR . 'common/ProtectedPage.php';

class Index extends ProtectedPage
{
    protected $tabs = array(
        'script' => array(
            'display_name' => 'Script',
            'link' => '#!script'
        ),
        'options' => array(
            'display_name' => 'Options',
            'link' => '#!options'
        ),
        'history' => array(
            'display_name' => 'History',
            'link' => '#!history'
        ),
        'log' => array(
            'display_name' => 'Log',
            'link' => '#!log'
        ),
    );

    public function render_sidebar()
    {
        $res = DataFactory::find_agent();
        
        if ($res->ok()) {
            return P('tpl')->html('common/sidebar.tpl.php', array(
                'agents' => $res->data
            ));
        } else {
            //show error message            
        }        
    }

    public function render_main()
    {
        $id = P('param')->read('id', 0);
        if ($id == 0) return "No agent selected.";

        $selected_tab = 'script';

        $content = P('tpl')->html('common/tab.tpl.php', array(
            'tabs' => $this->tabs,
            'selected' => $selected_tab,
            'body' => $this->render_tab($selected_tab)
        ));

        return P('tpl')->html('agent/main.tpl.php', array(
            'title' => $id,
            'content' => $content
        ));
    }

    public function render_tab($selected_tab)
    {
        switch ($selected_tab)
        {
            case 'script':
                return $this->render_tab_script();
            case 'options':
                return $this->render_tab_options();
            case 'history':
                return $this->render_tab_history();
            case 'log':
                return $this->render_tab_log();
        }
        return "";
    }

    public function render_tab_log()
    {
        return P('tpl')->html('agent/log.tpl.php' , array(
            'logs' => array('1','2')
        ));
    }

    public function render_tab_history()
    {
        return P('tpl')->html('agent/history.tpl.php' , array(
            'histories' => array('1','2')
        ));
    }

    public function render_tab_options()
    {
        return P('tpl')->html('agent/options.tpl.php' , array(
            'options' => array(
                array('label 1', 'value 1'),
                array('label 2', 'value 2')
            )
        ));
    }

    public function render_tab_script()
    {
        return P('tpl')->html('agent/script.tpl.php' , array(
            'script' => 'Dummy Script!!!'
        ));
    }

    public function ajax_replace($container_id, $html)
    {

    }

    public function render()
    {
        if ($this->is_ajax())
        {
            $selected_tab = $this->ajax_query();
            echo $this->render_tab($selected_tab);
        }
        else
        {
            echo P('tpl')->html('two_column.layout.tpl.php', array(
                'side' => $this->render_sidebar(),
                'main' => $this->render_main()
            ));
        }
    }
}