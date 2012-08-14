<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <title><?php echo APP_NAME?></title>
        <link rel="shortcut icon" href="<?php echo url('static/image/favicon.ico') ?>" type="image/x-icon" />
        <link href="<?php echo url('static/css/common.css') ?>" rel="stylesheet" type="text/css"/>
        <link href="<?php echo url('static/css/codemirror.css') ?>" rel="stylesheet" type="text/css"/>
        <style type="text/css">.CodeMirror {border-top: 1px solid black; border-bottom: 1px solid black;}</style>
        <link href="<?php echo url('static/css/style.css') ?>" rel="stylesheet" type="text/css"/>

    </head>
    <body>
        <div class="container">
            <?php echo P('tpl')->html('common/message.tpl.php');?>

        	<div class="header_container" class="section">
                <?php echo P('tpl')->html('common/header.tpl.php');?>
            </div>
            <div id="side">
                <?php echo $side?>
            </div>
            <div id="main">
                <?php echo $main?>
            </div>

            <div class="footer_container" class="section">
                <?php echo P('tpl')->html('common/footer.tpl.php')?>
            </div>
        </div>

        <?php P('js')->combine_script("jquery-1.7.2.min.js");?>
        <?php P('js')->combine_script("codemirror.js");?>
        <?php P('js')->combine_script("javascript.js");?>

        <?php P('js')->combine_inline("
            $(document).ready(function() {
                var editor = CodeMirror.fromTextArea(document.getElementById('code'), {
                    lineNumbers: true,
                    matchBrackets: true,
                    width: '760px',
                    height: '1000px'
                });

                $('a._ajax').click(function() {

                    if ($(this).parent().hasClass('selected')) return;

                    $('.selected').removeClass('selected');
                    $(this).parent().addClass('selected');

                    var key = $(this).attr('href').replace('#!', '');
                    $.ajax(window.location.pathname + '?ajax=' + key)
                    .done(function(response) {
                        $('._ajax_target').html(response);
                    })
                    .fail(function() { alert('error'); });
                });
            });
        ");?>
    </body>
</html>