<?php

function home_url()
{ 
    return WEB_DIR;
}

function url($url, $query = array())
{
    if (!empty($query)) {
        $params = array();
        foreach ($query as $key => $value) {
            $params[] = $key . '=' . urlencode($value);
        }
        $url .= '?' . implode('&', $params);
    }
    return WEB_DIR . $url;
}

function alink($text, $ref = "")
{
    return "<a href='" . (empty($ref) ? "javascript:;" : url($ref)) . "'>$text</a>";
}

function echo_if_ne(&$value, $text = null)
{
    if (!empty($value)) {
        echo (null == $text) ? $value : $text;
    }
}

function if_ne(&$value, $text = null)
{
    if (!empty($value)) {
        return (null == $text) ? $value : $text;
    }
    return '';
}

function url64($url)
{
    return '_' . base64_encode($url);
}

function url64_decode($url)
{
    if (substr($url,0,1) == '_') { //base64 encoded, start with _ charracter
        return base64_decode(substr($url,1));
    } else {
        return $url;
    }
}

