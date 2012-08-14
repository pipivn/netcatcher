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
 * @link          https://github.com/lamtq/pipi
 * @author        thanquoclam@gmail.com
 */

class Debugger extends Plugin
{
    private $_is_on;
    private $_last_timestamp;

    private $_messages = array();
    private $_tags = array();

    public function dependencies_map()
    {
        return array(
            'error' => 'error'
        );
    }
    
    function on_init()
    {
        $this->_is_on = $this->read_config("default_on", false);
        $this->_magic_word = $this->read_config("magic_word", "DEBUG");
        $this->_warning_timespan_from = $this->read_config("warning_timespan_from", 0.002);
        $this->_last_timestamp = microtime(true);
    }

    function before_run()
    {
        $this->detect_debug_mode();
    }

    function after_run()
    {
        $this->show();
    }

    function on()
    {
        $this->_is_on = true;
    }

    function off()
    {
        $this->_is_on = false;
    }
    
    function write($message, $tags = array('info'), $data = null)
    {
        if (isset($this->_is_on) && $this->_is_on) {
            $message_object = array();
            $message_object['message'] = $message;
            if (!empty($data)) $message_object['data'] = $data;
            $trace = debug_backtrace();
            if (count($trace)>0){
                $message_object['trace'] = $trace[0];
            }
            $message_object['timespan'] = microtime(true) - $this->_last_timestamp;
            if (gettype($tags)=='string') $tags = array($tags);
            if ($message_object['timespan'] > $this->_warning_timespan_from){
                $tags[] = 'warning';
            }
            $this->_last_timestamp = microtime(true);
            switch (gettype($tags)) {
                case 'string': 
                    $tags = array($tags);
                    break;
                case 'array':
                    break;
                default:
                    $tags = array();
            }
            foreach ($tags as $tag) {
                if (empty($this->_tags[$tag])) {
                    $this->_tags[$tag] = 1;
                } else {
                    $this->_tags[$tag]++;
                }
            }
            $message_object['tags'] =  $tags;
            array_push($this->_messages, $message_object);
        }
    }
    
    public function detect_debug_mode()
    {
        if (isset($_GET[$this->_magic_word]))
        {
            $this->on();
        }
    }
    
    public function show()
    {
        if ($this->_is_on == false) return;
        $script = '
            <script>
                function getElementsByClassName(classname, node)  {
                    if(!node) node = document.getElementsByTagName("body")[0];
                    var a = [];
                    var re = new RegExp("(^|\\\\s)" + classname + "(\\\\s|$)");
                    var els = node.getElementsByTagName("*");
                    for(var i=0,j=els.length; i<j; i++)
                        if(re.test(els[i].className))a.push(els[i]);
                    return a;
                }

                function PBDebuggerHide(id)
                {
                    document.getElementById(id).style.display = "none";
                }
                
                function PBDebuggerShow(id)
                {
                    document.getElementById(id).style.display = "block";
                }

                function PBDebuggerToggle(id)
                {
                    if (document.getElementById(id).style.display == "none") {
                        document.getElementById(id).style.display = "block";
                    }
                    else document.getElementById(id).style.display = "none"
                }

                function PBDebuggerTagSelect()
                {
                    var tag = document.getElementById("PBDebuggerConsoleTags").value;
                    var node = document.getElementById("PBDebuggerConsoleLog");
                    var arr = [];
                    var i;
                    if (tag) {
                        arr = node.getElementsByTagName("tr")
                        for (i=0; i<arr.length; i++) {
                            arr[i].style.display = "none";
                        }
                        arr = getElementsByClassName(tag, node);
                        for (i=0; i<arr.length; i++) {
                            arr[i].style.display = "block";
                        }
                    } else {
                        arr = node.getElementsByTagName("tr")
                        for (i=0; i<arr.length; i++) {
                            arr[i].style.display = "block";
                        }
                    }
                }
            </script>
        ';

        $css = '
            <style type="text/css">
                #PBDebugger {
                        font-family: Arial;
                        font-size: 12px;
                        line-height: 1.5;
                        color: #FFFFFF;
                        text-align: left;
                        margin: 0;
                        padding: 0;
                }
                #PBDebugger input {
                		padding:0;
                		margin:0;
                		height:10px;
                }

                .PBDebugger_info{
                        background :none;
                }

                .PBDebugger_warning{
                        background-color: #FF5;
                }

                .PBDebugger_error{
                        background-color: #FAA;
                }

                .PBDebugger_bar {
                        position: fixed;
                        background: #555;
                        bottom: 0;
                        right: 0;
                        width:100%;
                        opacity: 05;
                        padding: 5px;                        
                        z-index: 10000;                        
                }

                .PBDebugger_detail {
                        margin: 3px 0px 0px 0px;
                }

                .PBDebugger_bar a {
                        color: white;
                        padding: 3px 2px;
                        text-decoration: none;
                }

                .PBDebugger_bar a:hover {
                        background: white;
                        color: #555;
                }

                .PBDebugger_detail li {
                        display: inline;
                        padding: 0pt 5px;
                }

                .PBDebugger_panel {
                        position: fixed;
                        bottom: 0;
                        left: 0;
                        right: 0;
                        color: #000000;
                        font-weight: normal;
                        padding: 0 10px 40px 10px;
                        width: 99%;
                        min-height: 150px;
                        max-height: 500px;
                        overflow: auto;
                        background: #EFEFEF;
                        border-top: 1px solid #A4A4A4;
                        z-index:9999;
                        font-family: Fixed, monospace;
                }
                
                .PBDebugger_header {
                        font-size: 16px;
                        font-weight: bold;
                }

                #PBDebuggerConsoleLog .sql{
                        color:blue;
                }


            </style>
        ';

        echo $script . $css;

        // memory used
        $memoryInfo = '';
        if (function_exists('memory_get_usage'))
        {
          $totalMemory = sprintf('<b>%.1f</b>', (memory_get_usage() / 1024));
          $memoryInfo = $totalMemory . ' KB';
        }

        // execution time
        $totalTime = (microtime(true) - $this->_last_timestamp) * 1000;
          $timeInfo = sprintf(($totalTime <= 1) ? '<b>%.3f</b>' : '<b>%.0f</b>', $totalTime) .' ms';

        //error notification
        $error_count = count($this->P('error')->stack());
        if ($error_count > 0) {
            $error_info = "<b style='color:#c41'>(" . $error_count . ")</b>";
        } else {
            $error_info = '';
        }
        
        //debug bar layout
        $result = '
            <div id="PBDebugger">
                <div class="PBDebugger_bar">
                    <div style="margin-right:5px;float:right;">
                        ' . $memoryInfo . '  /  ' . $timeInfo . '
                        <a href="#" onclick="document.getElementById(\'PBDebugger\').style.display=\'none\'; return false;"><b>[x]</b></a>
                    </div>
                    
                    <ul class="PBDebugger_detail">
                        <li><a href="javascript:void(0);" onclick="PBDebuggerToggle(\'PBDebuggerConsoleLog\');PBDebuggerHide(\'PBDebuggerPlugin\');PBDebuggerHide(\'PBDebuggerErrorLog\');">Console</a></li>
                        <li><a href="javascript:void(0);" onclick="PBDebuggerToggle(\'PBDebuggerErrorLog\');PBDebuggerHide(\'PBDebuggerPlugin\');PBDebuggerHide(\'PBDebuggerConsoleLog\');">Error ' . $error_info . '</a></li>
                        <li><a href="javascript:void(0);" onclick="PBDebuggerToggle(\'PBDebuggerPlugin\');PBDebuggerHide(\'PBDebuggerErrorLog\');PBDebuggerHide(\'PBDebuggerConsoleLog\');">Plugins</a></li>
                    </ul>
                </div>

                <div id="PBDebuggerConsoleLog" class="PBDebugger_panel" style="display: none"><span class="PBDebugger_header">Console</span>
                    <br/>' . $this->getConsoleInfo() . '
                </div>

                <div id="PBDebuggerErrorLog" class="PBDebugger_panel" style="display: none"><span class="PBDebugger_header">Errors</span>
                    <br/>' . $this->getErrorInfo() . '
                </div>
                
                <div id="PBDebuggerPlugin" class="PBDebugger_panel" style="display: none"><span class="PBDebugger_header">Plugins</span>
                    <br/>'. $this->printArray('[ Plugins ]', isset($GLOBALS['PLUGINS']) ? $GLOBALS['PLUGINS'] : '(not found)') . '
                </div>
            </div>
        ';
        echo $result;
    }
    

    /* PRIVATE AREA */
    
    private function printArray($name, $values)
    {
        $result = '<br/><pre>'.htmlentities(print_r($values, true)).'</pre>';
        return $result;
    }
    
    private function getGlobalsArray()
    {
        $values = array();
        foreach (array('cookie', 'server', 'get', 'post', 'files', 'env', 'session') as $name)
        {
            if (!isset($GLOBALS['_'.strtoupper($name)]))
            {
                continue;
            }

            $values[$name] = array();
            foreach ($GLOBALS['_'.strtoupper($name)] as $key => $value)
            {
                $values[$name][$key] = $value;
            }

            ksort($values[$name]);
        }
        ksort($values);

        return $values;
    }

    private function getErrorInfo()
    {
        $html = '';
        if ($this->P('error')->has_error()) {
            foreach ($this->P('error')->stack() as $error) {
                $line = '[' . $error['code'] . '] ';
                $line .= $this->P("error")->build_message($error['code'], $error['args']);
                $html .= $line . '<br/>';
            }
            return $html;
        } else {
            return 'All is well!';
        }
    }

    private function getConsoleInfo()
    {
        $result = '';
        if (!empty($this->_tags)) {
            $result .= '<select id="PBDebuggerConsoleTags" onchange="PBDebuggerTagSelect();">';
            $result .= "<option value=''>All</option>";
            foreach ($this->_tags as $tag=>$count) {
                $result .= "<option value='$tag'>$tag ($count)</option>";
            }
            $result .= '</select>';
        }
        $result .= '<table>';
        foreach($this->_messages as $msg_obj)
        {
            if (!empty($msg_obj['tags'])) {
                $result .= '<tr class="' . implode(' ', $msg_obj['tags']). '">';
            } else {
                $result .= '<tr>';
            }
            
            $result .= '<td style="vertical-align:top;">' . sprintf('%.3f',  $msg_obj['timespan']) . '</td>';
            $result .= '<td>' . htmlentities($msg_obj['message']) . '</td><td>';
            if (!empty($msg_obj['data'])) {
                $result .= '<input type="text" value="' . htmlentities($msg_obj['data']) . '"></input>';
            } else {
                $result .= '</td></tr>';
            }
        }
        return $result . '</table>';
    }
}