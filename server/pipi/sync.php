<?php

/**
 * PHP versions 4 and 5
 *
 * pipi sync: an utility to quick setup a pipi application
 * Copyright 2011, lamtq (thanquoclam@gmail.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @link        https://github.com/lamtq/pipi
 * @author      thanquoclam@gmail.com
 */

include 'pipi.php';

class Installer
{
    function sync_plugins($config_file)
    {
        include $config_file;
        if ( isset($GLOBALS['PLUGINS']) )
        {
            system("git checkout .");
            $keeps = array();
            foreach ($GLOBALS['PLUGINS'] as $key => $value)
            {
                echo "Update " . $value['class_name'] . "\n";
                $keeps[] = $value['class_name'];
            }
            $dirs = array_filter(glob(PIPI_DIR . 'plugins/*'), 'is_dir');
            
            foreach ($dirs as $dir)
            {
                $name = end(explode("/", $dir));
                if (!in_array($name, $keeps))
                {
                    system("rm -rf " . PIPI_DIR . 'plugins/' . $name);
                }
            }
        }
    }
    
    function create_skeleton($skeleton, $reset = false)
    {
        $dir = PIPI_DIR . "skeletons/" . $skeleton;
        if (file_exists($dir) && is_dir($dir))
        {
            system("cp -r" . ($reset ? "f" : "i") . " " . $dir . "/* " . dirname(PIPI_DIR));
            system("cp -r" . ($reset ? "f" : "i") . " " . $dir . "/.[^.]* " . dirname(PIPI_DIR));
        }
    }
}

if (PHP_SAPI === 'cli')
{   
    $installer = new Installer();
    $options = getopt("c:s:f::");
    $config_file = $options["c"];
    $skeleton = $options["s"];
    $reset = isset($options["f"]);
    
    echo "=[ PIPI SYNC ]=================================================\n";
    
    echo "Open config file: " . $config_file . " \n";
    if (isset($config_file))
    {
        if (file_exists($config_file)) {
            echo "Sync plugins...\n";
            $installer->sync_plugins($config_file);
            echo "Done. \n";
        } else {
            echo "File not found.";
        }
    }
    
    if (isset($skeleton))
    {
        echo "Create skeleton...\n";
        $installer->create_skeleton($skeleton, $reset);
        echo "Done. \n";
    }
}
else
{
    echo "[Pipi sync] :: CLI only!"; 
}