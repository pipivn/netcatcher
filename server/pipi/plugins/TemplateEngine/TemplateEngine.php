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
 * @link          https://github.com/lamtq/pipi
 * @author        thanquoclam@gmail.com
 */

/**
 * DESCRIPTION
 * A plugin of pipi framework
 *
 */

class TemplateEngine extends Plugin
{
    protected $_template_dir;

    public function dependencies_map()
    {
        return array(
            'debug' => 'debug',
            'error' => 'error'
        );
    }
    
    public function on_init()
    {
        $this->P('error')->import_message_patterns(array(
            'TemplateEngine.file_not_found' => "Template file is not found '{path}'!"
        ));

        $this->_template_dir = $this->read_config("template_dir", './templates/');
    }


    public function set_dir($dir)
    {
        $this->_template_dir = $dir;
    }

    /**
     * Render html content use template file + data
     * @param <string> $template_file template file path
     * @param <mixed> $data
     * @return <string> html content
     */
    public function html($template_file, $data = array())
    {
        $template_full_path = $this->_template_dir . $template_file;

        //#. Extract values
        foreach ($data as $var_name => $value){
            global $$var_name;
        }
        extract($data);
        $this->debug('render template file \'' . $template_file . '\'', array(), json_encode($data));
        
        //#. Render
        if(file_exists($template_full_path)){
            ob_start();
            include($template_full_path);
            $contents = ob_get_contents();
            ob_end_clean();
        } else {
            $this->P('error')->raise('TemplateEngine.file_not_found', array('path'=>$template_full_path));
            $this->debug('template_data = ' . json_encode($data));
            $contents = '';
        }
        return $contents;
    }
}

/**
 * HISTORY 
 *
 * Feb 6, 2011 by lamtq (thanquoclam@gmail.com)
 * created file
 *
 */
?>
