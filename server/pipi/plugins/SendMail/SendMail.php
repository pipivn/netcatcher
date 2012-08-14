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

class SendMail extends Plugin
{

    /**
     * Initialize plugin
     */
    public function on_init()
    {
    }

    public function send($from, $to, $subject, $message)
    {
        $to      = 'nobody@example.com';
        $headers = 'From: ' . $from . "\r\n" .
                   'Reply-To: ' . $from . "\r\n" .
                   'X-Mailer: PHP/' . phpversion();
        mail($to, $subject, $message, $headers);
    }
}
