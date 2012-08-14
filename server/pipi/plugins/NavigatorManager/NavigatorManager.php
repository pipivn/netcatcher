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

class NavigatorManager extends Plugin
{
    /* States */
    protected $_navigator_map;
    protected $_currrent_navigator;
    
    /**
     * Initialize plugin
     */
    public function on_init()
    {
    }

    public function setup($navigator_map)
    {
        // Calculate navigator level
        $stop = false;
        
        for ($i=0; $i<count($navigator_map); $i++) {
            $stop = true;
            foreach ($navigator_map as &$navigator) {
                if (empty($navigator['level'])) {
                    $stop = false;
                    if (!empty($navigator['parent']) && !empty($navigator_map[$navigator['parent']])) {
                        
                        if (!empty($navigator_map[$navigator['parent']]['level'])) {
                            $navigator['level'] = $navigator_map[$navigator['parent']]['level'] + 1;
                        }
                    } else {
                        $navigator['level'] = 1;
                    }
                }
            }
            if ($stop) break;
        }
        // Update state
        $this->_navigator_map = $navigator_map;
    }

    public function get_navigator_list($level)
    {
        $navigator_list = array();
        foreach ($this->_navigator_map as $key=>$navigator) {
            if ($navigator['level']<=$level) {
                $navigator_list[$key] = $navigator;
            }
        }
        return $navigator_list;
    }

    public function get_navigator_path($key = null)
    {
        if (empty($key) && !empty($this->_currrent_navigator)) $key = $this->_currrent_navigator;
        if (empty($key)) return false;

        $navigator_list = array();
        do {
            $nv = !empty($this->_navigator_map[$key]) ? $this->_navigator_map[$key] : false;
            if (!empty($nv)) {
                $navigator_list[$key] = $nv;
                $key = !empty($nv['parent']) ? $nv['parent'] : false;
                if (empty($key)) $nv = false;
            }
        } while (!empty($nv));
        return array_reverse($navigator_list);
    }

    public function select($key)
    {
        if (!empty($this->_navigator_map[$key])) {
            $this->_currrent_navigator = $key;
        } else {
            return false;
        }
    }

    public function get_selected_navigator($stop_level = null)
    {
        if (empty($stop_level)) {
            return !empty($this->_currrent_navigator) ? $this->_currrent_navigator : '';
        } else {
            $key = $this->_currrent_navigator;
             do {
                $nv = !empty($this->_navigator_map[$key]) ? $this->_navigator_map[$key] : false;
                if (!empty($nv)) {
                    if (!empty($nv['level']) && ($nv['level']<=$stop_level)) return $key;
                    if (empty($nv['parent'])) return $key;
                    $key = $nv['parent'];
                }
            } while (!empty($nv));
            return false;
        }
    }
}
