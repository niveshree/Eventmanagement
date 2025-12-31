<?php
function isMobileDevice()
{
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $mobileKeywords = [
        'mobile',
        'android',
        'iphone',
        'ipod',
        'blackberry',
        'opera mini',
        'opera mobi',
        'meego',
        'bolt',
        'fennec',
        'iemobile',
        'silk',
        'kindle'
    ];

    foreach ($mobileKeywords as $keyword) {
        if (stripos($userAgent, $keyword) !== false) {
            return true;
        }
    }
    return false;
}
