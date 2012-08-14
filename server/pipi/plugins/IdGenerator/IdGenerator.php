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


class IdGenerator extends Plugin
{
    /* Configurations */
    protected $_low = 0;
    protected $_max;
    protected $_range;
    
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
        if (!isset($_SESSION)) {
            session_start();
        }
        $this->_range = $this->read_config("range", 100);
    }

    protected function load_current_state()
    {

        if (!empty($_SESSION['__pipi_hilow_max'])) $this->_max = $_SESSION['__pipi_hilow_max'];
        if (!empty($_SESSION['__pipi_hilow_low'])) $this->_low = $_SESSION['__pipi_hilow_low'];
        if (!empty($_SESSION['__pipi_hilow_range'])) $this->_range = $_SESSION['__pipi_hilow_range'];
    }

    protected function save_current_state()
    {
        $_SESSION['__pipi_hilow_max'] = $this->_max;
        $_SESSION['__pipi_hilow_low'] = $this->_low;
        $_SESSION['__pipi_hilow_range'] = $this->_range;
    }

	/**
     * Generate an id
     */
    public function gen()
    {
        $this->load_current_state();
        if (!isset($this->_max))
        {
            $this->_max = $this->get_and_update_max();
        }
        if (isset($this->_max)) //load successfully
        {
            $this->_low++;
            if ($this->_low > $this->_range)
            {
                $this->_max = $this->get_and_update_max();
                $this->_low = 1;
            }
            $this->save_current_state();
            return $this->_max + $this->_low;
        }
        return false;
    }
    
    protected function get_and_update_max()
    {
    
    }
    
}
