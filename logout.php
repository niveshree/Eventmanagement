<?php
// logout.php
// Auth.php is in parent directory, then functions folder
require_once './functions/auth.php';

// Call logout function
logout();

// Redirect to login.php which is in the SAME folder (event folder)
header("Location: login.php");
exit();
?>