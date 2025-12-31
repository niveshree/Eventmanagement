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

// Set headers for download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));

// Clear output buffer
ob_clean();
flush();
readfile($file_path);
exit;
?>