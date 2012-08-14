<?php
require ROOT . 'common/Page.php';

class Index extends Page
{
    public function render_main()
    {
    	$page = P('param')->read('page', 1);
        $pagesize = 20;
        $entry_list = LogRepository::get_list($page, $pagesize);
        $total = LogRepository::count_total();
        
        return P('tpl')->html('common/entry_list.tpl.php', array(
            'entry_list' => $entry_list,
            'paging' => array(
                'page' => $page,
                'total'=> $total,
                'pagesize' => $pagesize
            ),
        ));
    }
    
	public function render_sidebar()
    {
    	return P('tpl')->html('common/sidebar.tpl.php', array(
            'selected_site' => P('param')->read('site', ""),
        	'sites' => LogRepository::get_sites()
        ));
    }
        
    public function render()
    {
        echo P('tpl')->html('default.layout.tpl.php', array(
            'header' => $this->render_header(),
            'main' => $this->render_main(),
        	'sidebar' => $this->render_sidebar(),
            'footer' => $this->render_footer()        
        ));
    }
}