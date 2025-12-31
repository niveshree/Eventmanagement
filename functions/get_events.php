<?php
header('Content-Type: application/json');
require_once '../config/database.php';

// Get database connection
$conn = getDBConnection();

if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

$response = [];

try {
    // Based on your table fields, here's the correct SQL
    $sql = "SELECT 
        id,
        event_name,
        event_date as start,
        venu_address as location,
        description,
        mobile as phone,
        email,
        status,
        location_url,
        created_at,
        address as client_address,
        client_name
    FROM events 
    WHERE deleted = 0 
    ORDER BY event_date ASC";

    // For MySQLi
    if ($conn instanceof mysqli) {
        $result = $conn->query($sql);

        if (!$result) {
            throw new Exception("Query failed: " . $conn->error);
        }

        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
    }
    // For PDO
    else if ($conn instanceof PDO) {
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        throw new Exception("Unknown database connection type");
    }

    // Debug: Check what data we're getting
    // error_log(print_r($events, true));
    // print_r($events);

    $formattedEvents = array_map(function ($event) {
        // Safely access array keys
        $status = isset($event['status']) ? strtolower($event['status']) : 'pending';
        $calendarColor = getCalendarColor($status);

        // Make sure start date is valid
        $startDate = $event['start'] ?? null;

        // If no date or invalid date, use created_at
        if (!$startDate || $startDate == '0000-00-00' || strtotime($startDate) === false) {
            $startDate = $event['created_at'] ?? date('Y-m-d');
        }

        // Format the date properly
        if ($startDate && strtotime($startDate) !== false) {
            $startDate = date('Y-m-d', strtotime($startDate));
        } else {
            $startDate = date('Y-m-d'); // Fallback to today
        }

        return [
            'id' => $event['id'] ?? null,
            'title' => $event['event_name'] ?? 'Untitled Event',
            'start' => $startDate,
            'extendedProps' => [
                'calendar' => $status, // Use lowercase status
                'location' => $event['location'] ?? ($event['venu_address'] ?? ''),
                'description' => $event['description'] ?? '',
                'phone' => $event['phone'] ?? $event['mobile'] ?? '',
                'email' => $event['email'] ?? '',
                'budget' => 0,
                'map_url' => $event['location_url'] ?? '',
                'client_name' => $event['client_name'] ?? '',
                'client_address' => $event['client_address'] ?? ''
            ],
            'backgroundColor' => $calendarColor,
            'display' => 'block',
            'textColor' => '#fff'
        ];
    }, $events);

    echo json_encode($formattedEvents);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function getCalendarColor($type)
{
    $type = strtolower($type);
    $colors = [
        'pending' => 'background: linear-gradient(270deg, rgb(242 136 150) -10%, var(--bs-primary) 100%);',     // Purple
        'completed' => '#28c76f',   // Green
        'cancel' => '#ea5455',      // Red
        'cancelled' => '#ea5455',   // Red (alternative spelling)
        'confirmed' => '#ff9f43',   // Orange
        'tentative' => '#00cfe8'    // Cyan
    ];
    return $colors[$type] ?? 'background: linear-gradient(270deg, rgb(242 136 150) -10%, var(--bs-primary) 100%);';
}
