<?php
session_start();

// Start session with security
function secure_session_start()
{
    $session_name = 'secure_session';
    $secure = true;
    $httponly = true;

    if (ini_set('session.use_only_cookies', 1) === FALSE) {
        header("Location: ./error.php?err=Could not initiate a safe session (ini_set)");
        exit();
    }

    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => $cookieParams["lifetime"],
        'path' => '/',
        'domain' => $cookieParams["domain"],
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);

    session_name($session_name);
    session_start();
    session_regenerate_id(true);
}

// Check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Login function
function login($username, $password)
{
    require_once './config/database.php';
    $conn = getDBConnection();

    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, username, password, role, full_name FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $stmt->store_result();

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    if ($stmt->num_rows == 1) {
        $stmt->bind_result($id, $username, $hashed_password, $role, $full_name);
        $stmt->fetch();
        if (password_verify($password, $hashed_password)) {
            // Password is correct
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
            $_SESSION['full_name'] = $full_name;
            $_SESSION['login_time'] = time();

            // Update last login
            $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $update_stmt->bind_param("i", $id);
            $update_stmt->execute();

            return true;
        }
    }

    return false;
}

// Logout function
function logout()
{
    $_SESSION = array();

    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );

    session_destroy();
}

// Check user role
function checkRole($required_role)
{
    if (!isset($_SESSION['role'])) {
        return false;
    }

    // Admin has access to everything
    if ($_SESSION['role'] == 'admin') {
        return true;
    }

    return $_SESSION['role'] == $required_role;
}

// Redirect if not logged in
function requireLogin()
{
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// Redirect if not admin
function requireAdmin()
{
    requireLogin();

    if (!checkRole('admin')) {
        header("Location: index.php");
        exit();
    }
}
