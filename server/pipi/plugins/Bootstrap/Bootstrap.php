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

class Bootstrap extends Plugin
{
    /* Configurations */
    protected $_modules_dir;
    protected $_default_module;
    protected $_default_action;
    protected $_class_subfix;

    public function dependencies_map()
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
        $this->_modules_dir = $this->read_config("modules_dir", './modules/');
        $this->_class_subfix = $this->read_config("class_subfix", '');
    }
    
    public function set_dir($module_dir)
    {
        $this->_modules_dir = $module_dir;
    }

    public function execute()
    {
        $this->debug("Bootstrap execute", "info");
        //get module and action from url (after rewriting). @see .htaccess
        //$query = parse_url($url);
        if (!empty($_GET['path'])) {
            $path_items = explode('/', $_GET['path']);
        } else {
            $path_items = array('index');
        }
        $file_path = $this->_modules_dir . implode('/', $path_items) . ".php";
        
        if (file_exists($file_path)) {
            include $file_path;
            $class_name = ucfirst($path_items[count($path_items)-1]) . $this->_class_subfix;
            run($class_name);
        } else {
            $file_path = $this->_modules_dir . implode('/', $path_items) . "/index.php";
            if (file_exists($file_path)) {
                include $file_path;
                $class_name = 'index' . $this->_class_subfix;
                run($class_name);
            } else {
                $this->debug("File '$file_path' is not found.", "error");
                self::return_404();
            }
        }
    }

    public function return_404()
    {
        echo "<h1>404 Not Found</h1>";
        echo "The page that you have requested could not be found.";
        echo "<hr/>";
        echo "<div style='color:#eee;float:right;'>pipi</div>";
        
        die();
    }
}
