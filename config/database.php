<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'eventmanagment_db');

// Create connection
function getDBConnection()
{
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

// Create users table if not exists
function createUsersTable()
{
    $conn = getDBConnection();

    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100),
        role ENUM('admin', 'user') DEFAULT 'user',
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql) === TRUE) {
        $checkAdmin = "SELECT id FROM users WHERE username='admin'";
        $result = $conn->query($checkAdmin);

        if ($result->num_rows == 0) {
            $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
            $insertAdmin = "INSERT INTO users (username, email, password, full_name, role) 
                            VALUES ('admin', 'admin@example.com', '$hashed_password', 'Administrator', 'admin')";
            $conn->query($insertAdmin);
        }
    }

    $conn->close();
}

function generateCSRFToken()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

// Validate CSRF token
function validateCSRFToken($token)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Rate limiting function
function checkRateLimit($identifier, $maxRequests = 10, $timeWindow = 60)
{
    $cacheDir = './cache/rate_limit/';

    if (!file_exists($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }

    $cacheFile = $cacheDir . md5($identifier) . '.json';
    $currentTime = time();

    if (file_exists($cacheFile)) {
        $data = json_decode(file_get_contents($cacheFile), true);

        // Clean old entries
        $data['requests'] = array_filter($data['requests'], function ($timestamp) use ($currentTime, $timeWindow) {
            return ($currentTime - $timestamp) < $timeWindow;
        });

        // Count requests in time window
        $requestCount = count($data['requests']);

        if ($requestCount >= $maxRequests) {
            return false; // Rate limited
        }

        $data['requests'][] = $currentTime;
    } else {
        $data = [
            'requests' => [$currentTime],
            'first_request' => $currentTime
        ];
    }

    // Save updated data
    file_put_contents($cacheFile, json_encode($data));

    return true;
}

// Initialize table
createUsersTable();