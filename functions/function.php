<?php
// Start session FIRST
session_start();

// Include your functions
include_once('./functions/function.php');

function getBaseUrl()
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptPath = dirname($_SERVER['SCRIPT_NAME']);

    return $protocol . $host . $scriptPath;
}

// Correct logic: Redirect to login if NOT logged in
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: login.php');
    exit(); // Always exit after header redirect
}
exit;
