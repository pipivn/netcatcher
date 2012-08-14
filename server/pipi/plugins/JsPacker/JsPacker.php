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

/**
 * JsPacker Plugin
 *
 *
 */

define('JSPACKER_PRODUCTION_MODE', 0);
define('JSPACKER_DEVELOPEMENT_MODE', 1);
define('JSPACKER_COMPILE_MODE', 2);

class JsPacker extends Plugin
{

    public function dependencies_map()
    {
        return array(
            'debug' => 'debug'
        );
    }
    
    /* Configuration */
    protected $_default_development;
    protected $_mode_flag; //development | production
    protected $_file_dir;
    protected $_web_dir;

    /* State */
    protected $_mode;
    protected $_compile = false;
    protected $_first_script = true;
    protected $_script_pool = array();
    
    /**
     * Initialize plugin
     */
    public function on_init()
    {
        $this->_default_development = $this->read_config("development_as_default", false);
        $this->_mode_flag = $this->read_config("mode_flag", 'JSMODE');
        $this->_file_dir = $this->read_config("file_dir", '');
        $this->_web_dir = $this->read_config("web_dir", '');
        $this->_offline_compiler = $this->read_config("offline_compiler", false);
    }
    
    public function before_run()
    {
        $this->detect_mode();
    }

    public function after_run()
    {
        if ($this->_mode == JSPACKER_COMPILE_MODE) {
            $this->pack();
        }
    }

    /* PUBLIC FUNCTIONS*/

    public function set_dir($file_dir)
    {
        $this->_file_dir = $file_dir;
    }
    
    public function combine_inline($script)
    {
        switch ($this->_mode) {
            case JSPACKER_DEVELOPEMENT_MODE:
                echo $this->write_inline($script);
                break;
            case JSPACKER_COMPILE_MODE:
                $this->add_to_compile_list($this->create_temp($script));
                break;
        }
    }

    public function combine_script($script)
    {
        switch ($this->_mode) {
            case JSPACKER_PRODUCTION_MODE:
                if ($this->_first_script) {
                    echo $this->write_script($this->_web_dir . $this->get_production_name());
                    $this->_first_script = false;
                }
                break;
            case JSPACKER_DEVELOPEMENT_MODE:
                echo $this->write_script($this->_web_dir . $script);
                break;
            case JSPACKER_COMPILE_MODE:
                if ($this->_first_script) {
                    echo $this->write_script($this->_web_dir . $this->get_production_name() . "?rand=" . rand());
                    $this->_first_script = false;
                }
                $this->add_to_compile_list($this->_file_dir . $script);
                break;
        }
    }

    /* Write script reference without pack it in production */
    public function single_script($path)
    {
        $path = trim($path);
        if ((0 !== strpos($path, 'http'))&&(!in_array($path[0], array('/','.','..')))) {
            $path = $this->_web_dir . $path;
        }
        echo $this->write_script($path);
    }

    /* PRIVATE FUNCTIONS */

    private function add_to_compile_list($script_path)
    {
        $this->_script_pool[] = $script_path;
    }

    private function pack()
    {
        $production_dir = $this->_file_dir . 'production';
        if (!file_exists($production_dir)) {
            mkdir($production_dir, 0755);
        }

        $this->_script_pool = array_unique($this->_script_pool);
        $inputs = '';
        foreach ($this->_script_pool as  $ref) {
            $inputs .= '--js ' .  $ref . ' ';
        }
        
        $output = $this->_file_dir . $this->get_production_name();

        if ($this->_offline_compiler) {
            $compiler = dirname(__FILE__) . '/closure-compiler/compiler.jar';
            $this->debug("offline compile: java -jar $compiler $inputs --js_output_file $output");
            shell_exec("java -jar $compiler $inputs --js_output_file $output");
        } else {
            $this->debug("online compile:");
        }
        //clean
        //$this->clear_temps();
    }

    private function get_production_name()
    {
        return 'production/all.js';
        //return 'production/' . Executer::get_current_action() . '.js';
    }
    
    private function create_temp($script)
    {
        $temp_dir = sys_get_temp_dir() . '/pipi_jspacker';
        if (!file_exists($temp_dir)) {
            mkdir($temp_dir); 
        }
        
        $path = $temp_dir . '/' . md5($script) . '.js';
        
        $fp = fopen($path, 'w');
        fwrite($fp, $script);
        fclose($fp);
        
        return $path;
    }
    
    private function clear_temps()
    {
        $temp_dir = sys_get_temp_dir() . '/pipi_jspacker';
        if (is_dir($temp_dir)) {
            $objects = scandir($temp_dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($temp_dir."/".$object) == "dir") 
                        rmdir($temp_dir."/".$object);
                     else 
                        unlink($temp_dir."/".$object);
                }
            }
         reset($objects);
         rmdir($temp_dir);
       }
    }
    
    private function detect_mode()
    {
        if (!empty($this->_mode_flag) && isset($_GET[$this->_mode_flag]))
        {
            switch ($_GET[$this->_mode_flag])
            {
                case '0':
                    $this->_mode = JSPACKER_PRODUCTION_MODE;
                    break;
                case '1':
                    $this->_mode = JSPACKER_DEVELOPEMENT_MODE;
                    break;
                case '2':
                    $this->_mode = JSPACKER_COMPILE_MODE;
                    break;
            }
        }

        if (!isset($this->_mode)) {
            if ($this->_default_development) {
                $this->_mode = JSPACKER_DEVELOPEMENT_MODE;
            } else {
                $this->_mode = JSPACKER_PRODUCTION_MODE;
            }
        }

        if (($this->_mode == JSPACKER_PRODUCTION_MODE) && !$this->exist_production())
        {
            $this->_mode = JSPACKER_COMPILE_MODE;
        }

        $this->debug('mode = ' . $this->_mode . " (flag = " . $this->_mode_flag. "; pro = 0;  dev = 1; com = 2)");
    }

    private function exist_production()
    {
        return file_exists($this->_file_dir . $this->get_production_name());
    }

    private function write_inline($script)
    {
        return "<script type='text/javascript'>" . $script . "</script>";
    }
    
    private function write_script($script, $inline = false)
    {
        return "<script type='text/javascript' src='$script'></script>";
    }
}