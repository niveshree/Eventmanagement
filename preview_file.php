<?php
require_once './functions/auth.php';
require_once './config/database.php';
requireLogin();

if (!isset($_GET['file']) || !isset($_GET['client_id'])) {
    header('HTTP/1.0 400 Bad Request');
    exit('Invalid request');
}

$file_name = basename($_GET['file']);
$client_id = intval($_GET['client_id']);

// Security: Verify the user has access to this client's files
require_once './functions/client_crud.php';
$clientCrud = new ClientCrud();
$client = $clientCrud->getClientByIdSimple($client_id);

if (!$client) {
    header('HTTP/1.0 404 Not Found');
    exit('Client not found');
}

// Function to get file path
function getSecureFilePath($filename) {
    $possible_paths = [
        './uploads/',
        'uploads/',
        './uploads/',
        __DIR__ . '/./uploads/',
        __DIR__ . '/uploads/'
    ];
    
    foreach ($possible_paths as $path) {
        $full_path = realpath($path . $filename);
        if ($full_path && file_exists($full_path)) {
            return $full_path;
        }
    }
    
    return null;
}

$file_path = getSecureFilePath($file_name);

if (!$file_path || !file_exists($file_path)) {
    header('HTTP/1.0 404 Not Found');
    exit('File not found');
}

$extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

// Allow only image previews for security
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

if (!in_array($extension, $allowed_extensions)) {
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
    readfile($file_path);
    exit;
}

// Set appropriate image headers
switch($extension) {
    case 'jpg':
    case 'jpeg':
        header('Content-Type: image/jpeg');
        break;
    case 'png':
        header('Content-Type: image/png');
        break;
    case 'gif':
        header('Content-Type: image/gif');
        break;
    case 'webp':
        header('Content-Type: image/webp');
        break;
    case 'bmp':
        header('Content-Type: image/bmp');
        break;
}

header('Content-Length: ' . filesize($file_path));
readfile($file_path);
exit;
?>