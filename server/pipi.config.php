<?php

/**
 * PHP versions 4 and 5 and 6
 *
 * pipi : A tiny PHP web framework
 * Copyright 2010-2011, lamtq (thanquoclam@gmail.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @link      https://github.com/lamtq/base
 * @author    thanquoclam@gmail.com
 * 
 * --------------------------------
 *  APPLICATION CONFIGURATION FILE
 * --------------------------------
 */


define('APP_NAME', 'NetCatcher 1.0');
define('WEB_DIR', '/');

$GLOBALS['PLUGINS'] = array(

    'error' => array(
        'class_name' => 'ErrorTracker',
        'disable' => false,
        'display' => true,
        'config' => array(
            'display' => true
        )
    ),

    'debug' => array(
        'class_name' => 'Debugger',
        'disable' => false,
        'config' => array(
            'default_on' => true,
            'magic_word' => 'DEBUG'
        ),
        'before_run' => true,
        'after_run' => true
    ),
    
    'id' => array(
    	'class_name' => 'IdGeneratorByMysql',
        'location' => 'plugins/IdGenerator/',
        'disable' => false,
        'config' => array(
            'range' => 100,
            'server' => 'main',
            'database' => 'master',
            'table' => 'hilow',
            'column' => 'max'
        )
    ),
    
    'param' => array(
        'class_name' => 'ParamsAnalyzer',
        'disable' => false,
        'config' => array()
    ),
    
    'form' => array(
        'class_name' => 'PiFormManager',
        'location' =>'plugins/PiForm/',
        'disable' => false,
        'config' => array(),
        'before_run' => true,
    ),

    'mobile' => array(
        'class_name' => 'MobileHelper'
    ),

    'message' => array(
        'class_name' => 'MessageStack'
    ),

    'bootstrap' => array(
        'class_name' => 'Bootstrap',
        'config' => array(
            'modules_dir' => ROOT . 'modules/',
            'default_module' => 'index'
        )
    ),
    
    'tpl' => array(
        'class_name' => 'TemplateEngine',
        'disable' => false,
        'config' => array(
            'template_dir' => ROOT . 'templates/'
        )
    ),

    'guard' => array(
        'class_name' => 'SimpleGuardByPhpConfigFile',
        'location' => 'plugins/SimpleGuard/',
        'disable' => false,
        'config' => array(
            'users_config_file' => ROOT . "users.config.php"
        )
    ),

    'navigator' => array(
        'class_name' => 'NavigatorManager'
    ),

    'ioc' => array(
        'class_name' => 'IoCManager',
        'config' => array(
            'service_dir' => ROOT . 'app/core/services/'
        )
    ),

    'dd' => array(
        'class_name' => 'DomainDriven',
        'config' => array()
    ),
    
    'view' => array(
        'class_name' => 'ViewHelper'
    ),
    
    'gmap' => array(
        'class_name' => 'GoogleMapHelper'
    ),
    
    'js' => array(
        'class_name' => 'JsPacker',
        'config' => array(
            'file_dir' => ROOT . 'static/js/',
            'web_dir' => WEB_DIR . 'static/js/',
            'offline_compiler' => true,
            'development_as_default' => true
        ),
        'before_run' => true,
        'after_run' => true
    ),

    'mysql' => array(
        'class_name' => 'MysqlHelper',
        'disable' => false,
        'config' => array(
            'databases' => array(
                'master' => array(
                    'host' => 'localhost',
                    'user' => 'root',
                    'password' => '',
                    'database' => 'netcatcher'
                )
            )
        )
    )
    /*
    'mongo' => array(
        'class_name' => 'MongoDbHelper',
        'config' => array(
            'servers' => array(
                'main' => array(
    				'connection' => 'mongodb://localhost:27017',
                    'options' => array()
                )
            )
        )
    ) */
    
);
