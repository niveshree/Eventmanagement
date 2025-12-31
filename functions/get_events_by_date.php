<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$conn = getDBConnection();

if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

// try {
$date = $_GET['date'] ?? date('Y-m-d');

$sql = "SELECT 
        id,
        event_name,
        event_date,
        client_name,
        mobile as phone,
        venu_address,
        email,
        price,
        status,
        location_url,
        description,
        created_at
    FROM events 
    WHERE deleted = 0 
    AND DATE(event_date) = ?
    ORDER BY event_date ASC";
// print_r($sql);
// For MySQLi
if ($conn instanceof mysqli) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
}
// For PDO
else if ($conn instanceof PDO) {
    $stmt = $conn->prepare($sql);
    $stmt->execute([$date]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

echo json_encode($events);
// } catch (Exception $e) {
//     http_response_code(500);
//     echo json_encode(['error' => $e->getMessage()]);
// }
