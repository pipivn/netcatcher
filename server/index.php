<?php

/* START FROM HERE */

error_reporting(E_ALL);
define('ROOT', dirname(__FILE__) . '/');
define('APP_DIR', ROOT . 'app/');


require ROOT . 'pipi/pipi.php';
require ROOT . 'pipi.config.php';

require APP_DIR . 'common/functions.php';
require APP_DIR . 'language/vn.php';

require ROOT . 'app/core/all.php';

if (isset($_GET["api"]) || isset($_POST["api"])) {
    P('bootstrap')->set_dir(APP_DIR . 'modules/api/');
} else {
    if (P('mobile')->is_mobile_browser()) {
        P('tpl')->set_dir(APP_DIR . 'templates/wap/');
        P('bootstrap')->set_dir(APP_DIR . 'modules/wap/');
    } else {
        P('tpl')->set_dir(APP_DIR . 'templates/web/');
        P('bootstrap')->set_dir(APP_DIR . 'modules/web/');
    }
}

P('bootstrap')->execute();