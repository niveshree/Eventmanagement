<?php
require_once 'functions/auth.php';
require_once 'functions/user_crud.php';

// If already logged in, redirect
if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userCRUD = new UserCRUD();

    // Validate input
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);

    // Basic validation
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $error = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters!";
    } else {
        // Check if username or email exists
        $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $conn = getDBConnection();
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Username or email already exists!";
        } else {
            // Create user
            $data = [
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'full_name' => $full_name,
                'role' => 'user'
            ];

            if ($userCRUD->createUser($data)) {
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Registration failed! Please try again.";
            }
        }
    }
}
