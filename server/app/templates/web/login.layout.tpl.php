<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <title><?php echo APP_NAME?></title>
        <link rel="shortcut icon" href="<?php echo url('static/image/favicon.ico') ?>" type="image/x-icon" />
        <link href="<?php echo url('static/css/common.css') ?>" rel="stylesheet" type="text/css"/>
        <link href="<?php echo url('static/css/style.css') ?>" rel="stylesheet" type="text/css"/>

        <?php P('js')->combine_script("jquery.min.js");?>
    </head>
    <body>
        <div class="container">
            <?php echo P('tpl')->html('common/message.tpl.php');?>

        	<div class="header_container" class="section">
                <?php echo P('tpl')->html('common/header.tpl.php');?>
            </div>
            <div id="main">
                <?php echo $main?>
            </div>
            <div class="footer_container" class="section">
                <?php echo P('tpl')->html('common/footer.tpl.php')?>
            </div>
        </div>

    </body>
</html>