<?php
require_once './functions/auth.php';
require_once './functions/event_crud.php';
require_once './functions/client_crud.php';
requireLogin();

$eventCRUD = new EventCRUD();
$clientCRUD = new ClientCrud();
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $file_name = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $file_name = $eventCRUD->uploadAttachment($_FILES['attachment']);
    }
    $data = [
        'event_date' => $_POST['event_date'] ?? "",
        'event_name' => $_POST['event_name'] ?? "",
        'client_name' => $_POST['client_name'] ?? "",
        'mobile' => $_POST['mobile'] ?? "",
        'address' => $_POST['address'] ?? "",
        'email' => $_POST['email'] ?? "",
        'venu_address' => $_POST['venu_address'] ?? "",
        'venu_contact' => $_POST['venu_contact'] ?? "",
        'requirements' => $_POST['requirements'] ?? "",
        'description' => $_POST['description'] ?? "",
        'model' => $_POST['model'] ?? "",
        'image_url' => $_POST['image_url'] ?? "",
        'location_url' => $_POST['location_url'] ?? "",
        'price' => $_POST['price'] ?? "",
        'status' => $_POST['status'] ?? "",
    ];

    $result = $eventCRUD->createEvent($data, $file_name);
}

// Get statistics
$totalEvents = $eventCRUD->countEvents();
$totalClients = $clientCRUD->countClients();

// Get event status counts
$allEvents = $eventCRUD->getAllEvents();
$status_counts = ['pending' => 0, 'ongoing' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0];
if ($allEvents && is_array($allEvents)) {
    foreach ($allEvents as $event) {
        $status = $event['status'] ?? 'pending';
        if (isset($status_counts[$status])) {
            $status_counts[$status]++;
        }
    }
}

// Get upcoming events (next 7 days)
$upcomingEvents = [];
$conn = getDBConnection();
$sql = "SELECT * FROM events 
        WHERE event_date >= CURDATE() 
        AND event_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        ORDER BY event_date ASC 
        LIMIT 5";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $upcomingEvents[] = $row;
    }
}

// Get recent client with event details - FIXED QUERY WITH ALL NECESSARY FIELDS
$recentClient = null; // CHANGED FROM $client TO $recentClient to avoid conflict
$conn = getDBConnection();
$sql = "
SELECT 
    c.id AS client_id,
    c.name AS client_name,
    c.email AS client_email,
    c.phone AS client_phone,
    c.picture AS client_picture,
    c.location AS client_location,
    e.id AS event_id,
    e.event_date,
    e.event_name,
    e.status AS event_status,
    e.venu_address AS event_venue,
    e.mobile AS event_mobile,  -- This field exists in events table
    e.email AS event_email,    -- This field exists in events table
    e.price AS event_price
FROM events e
INNER JOIN client c ON e.client_name = c.id
WHERE c.id IS NOT NULL
ORDER BY e.event_date DESC
LIMIT 1
";

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $recentClient = $result->fetch_assoc();
    
    // Debug: Uncomment to see what data you're getting
    // echo "<pre>Recent Client Data: ";
    // print_r($recentClient);
    // echo "</pre>";
}

// Get monthly revenue
$monthlyRevenue = [];
$sql = "SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            SUM(price) as revenue
        FROM events 
        WHERE status = 'completed'
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month DESC
        LIMIT 6";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $monthlyRevenue[] = $row;
    }
}

$conn->close();

// Calculate totals for charts
$completedEvents = $status_counts['completed'] ?? 0;
$activeEvents = ($status_counts['ongoing'] ?? 0) + ($status_counts['progress'] ?? 0);
$pendingEvents = $status_counts['pending'] ?? 0;

// Calculate event completion rate
$completionRate = $totalEvents > 0 ? round(($completedEvents / $totalEvents) * 100) : 0;

// Get today's events
$todaysEvents = 0;
$conn = getDBConnection();
$sql = "SELECT COUNT(*) as count FROM events WHERE DATE(event_date) = CURDATE()";
$result = $conn->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $todaysEvents = $row['count'];
}
$conn->close();

// Helper function to sanitize output
function sanitize($data) {
    return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="description" content="Event Management Dashboard">
    <title>Event Calendar Dashboard</title>
    <?php include_once('./include/header.php'); ?>
    <style>
        .flatpickr-wrapper {
            left: 15px;
        }

        .template-customizer-open-btn {
            display: none !important;
        }

        /* ===== RESPONSIVE CALENDAR & LAYOUT ===== */

        /* General responsive fixes */
        .container-xxl {
            padding-left: 12px;
            padding-right: 12px;
        }

        @media (max-width: 768px) {
            .container-xxl {
                padding-left: 8px;
                padding-right: 8px;
            }
        }
        /* This Month Card */
        .month-client-card {
            background: #f3f3f3 !important;
            border-radius: 14px !important;
            padding: 14px 16px !important;
            color: #8b1c3d !important;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05) !important;
        }

        /* Date */
        .month-client-card .event-date {
            font-size: 0.75rem !important;
            color: #c24c6a !important;
        }

        /* Client name */
        .month-client-card .client-name {
            font-size: 1rem !important;
            font-weight: 700 !important;
            margin: 2px 0 0 !important;
        }

        /* Avatar */
        .client-avatar-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #d65a7c;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden; /* 🔥 REQUIRED */
        }

        .client-avatar-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* 🔥 REQUIRED */
        }

        .client-avatar-circle i {
            color: #fff;
            font-size: 1rem;
        }


        /* Venue */
        .month-client-card .venue-text {
            font-size: 0.8rem !important;
            margin: 6px 0 2px !important;
            color: #b24a67 !important;
        }

        /* Status */
        .month-client-card .status-text {
            font-size: 0.8rem !important;
            margin-bottom: 6px !important;
        }

        .status-confirmed { color: #198754; font-weight: 600 !important; }
        .status-pending   { color: #ffc107; font-weight: 600 !important; }
        .status-cancelled { color: #dc3545; font-weight: 600 !important; }
        .status-completed { color: #0d6efd; font-weight: 600 !important; }

        /* Action icons */
        .action-row {
            display: flex !important;
            gap: 12px !important;
            justify-content: flex-end !important;
        }

        .action-row a {
            color: #8b1c3d !important;
            font-size: 1rem !important;
        }

        .action-row a:hover {
            color: #d6336c !important;
        }

        /* Mobile polish */
        @media (max-width: 576px) {
            .month-client-card {
                padding: 12px !important;
            }

            .client-name {
                font-size: 0.95rem !important;
            }

            .action-row {
                gap: 14px !important;
            }
        }

        /* Calendar wrapper responsive behavior */
        .app-calendar-wrapper {
            margin-bottom: 1rem;
            overflow: hidden;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .app-calendar-wrapper .row {
            margin: 0;
            flex-wrap: nowrap;
            width: 100%;
        }

        /* Sidebar (Desktop) */
        .app-calendar-sidebar {
            min-width: 280px;
            max-width: 320px;
            background: #fff;
            height: calc(100vh - 200px);
            overflow-y: auto;
            position: sticky;
            top: 20px;
            border-right: 1px solid #e9ecef;
        }

        /* Calendar Content Area */
        .app-calendar-content {
            flex: 1;
            min-width: 0;
            background: #fff;
            border-radius: 0.5rem;
        }

        /* FullCalendar Container */
        #calendar {
            min-height: 600px;
            width: 100%;
            display: block !important;
            position: relative;
            z-index: 1;
            padding: 5px;
        }
        /* Dashboard Cards */
        .dashboard-card {
            border-radius: 14px;
            border: none;
            box-shadow: 0 6px 18px rgba(0,0,0,0.06);
        }

        /* Client Card */
        .client-card {
            background: #fff;
            transition: all 0.25s ease;
        }

        .client-card:hover {
            box-shadow: 0 10px 24px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        /* Avatar */
        .client-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0d6efd, #5a9bff);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        /* Action icons */
        .client-actions a {
            font-size: 1.1rem;
            padding: 6px;
        }

        /* Status dots */
        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        /* Mobile adjustments */
        @media (max-width: 576px) {
            .client-card {
                flex-direction: column;
            }

            .client-actions {
                margin-top: 10px;
            }

            .client-actions a {
                font-size: 1.3rem;
            }
        }


        /* MOBILE SPECIFIC STYLES */
        @media (max-width: 767.98px) {

            /* Stack rows vertically on mobile */
            .app-calendar-wrapper .row {
                flex-direction: column;
                display: block;
            }

            /* Hide desktop sidebar on mobile */
            .app-calendar-sidebar.d-none.d-md-block {
                display: none !important;
            }

            /* Full width calendar on mobile */
            .app-calendar-content {
                width: 100% !important;
                max-width: 100% !important;
                flex: 0 0 100% !important;
                padding: 0 !important;
                border-radius: 0.5rem;
            }

            /* Calendar adjustments for mobile */
            #calendar {
                min-height: 450px;
                padding: 3px;
            }

            /* FullCalendar Toolbar Mobile Optimization */
            .fc .fc-toolbar {
                flex-direction: column;
                gap: 8px;
                padding: 8px 0;
                width: 100%;
                margin-bottom: 5px !important;
            }

            .fc .fc-toolbar-title {
                font-size: 1.1rem !important;
                text-align: center;
                margin: 3px 0 !important;
                width: 100%;
                order: 1;
                line-height: 1.2;
            }

            .fc .fc-toolbar-chunk {
                display: flex;
                justify-content: center;
                width: 100%;
                flex-wrap: wrap;
            }

            .fc .fc-button-group {
                order: 2;
                width: 100%;
                display: flex;
                justify-content: center;
                margin: 3px 0 !important;
            }

            .fc .fc-button {
                padding: 0.35rem 0.7rem !important;
                font-size: 0.8rem !important;
                margin: 0 2px !important;
                min-width: 36px;
                height: 36px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .fc .fc-today-button {
                order: 3;
                width: 100%;
                max-width: 180px;
                margin: 3px auto 0 !important;
                padding: 0.4rem 1rem !important;
                font-size: 0.85rem !important;
            }

            /* Calendar grid for mobile */
            .fc .fc-scrollgrid {
                min-width: 100%;
                table-layout: fixed;
                border: none !important;
            }

            .fc .fc-col-header {
                width: 100% !important;
            }

            .fc .fc-col-header-cell {
                padding: 4px 1px !important;
                font-size: 0.75rem;
                border: 1px solid #dee2e6 !important;
            }

            .fc .fc-col-header-cell-cushion {
                font-size: 0.7rem !important;
                padding: 2px;
                font-weight: 600;
                display: inline-block;
                width: 100%;
                text-align: center;
            }

            /* Calendar days mobile */
            .fc .fc-daygrid-day {
                min-height: 65px;
                padding: 1px;
                border: 1px solid #dee2e6 !important;
            }

            .fc .fc-daygrid-day-frame {
                min-height: 60px;
                padding: 2px;
                display: flex;
                flex-direction: column;
                position: relative;
            }

            .fc .fc-daygrid-day-number {
                font-size: 0.75rem !important;
                width: 24px !important;
                height: 24px !important;
                padding: 0 !important;
                margin: 2px !important;
                position: absolute;
                top: 2px;
                right: 2px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50% !important;
            }

            .fc .fc-day-today .fc-daygrid-day-number {
                background-color: var(--bs-primary) !important;
                color: white !important;
                font-weight: bold;
            }

            /* Calendar events mobile */
            .fc .fc-daygrid-event {
                font-size: 0.65rem !important;
                padding: 1px 2px !important;
                margin: 0.5px 0 !important;
                line-height: 1.1;
                border-radius: 2px !important;
                border: none !important;
            }

            .fc-event-title {
                font-size: 0.6rem !important;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                display: block;
                font-weight: 500;
            }

            .fc-daygrid-day-events {
                margin-top: 20px !important;
                min-height: 15px;
            }

            /* More events indicator */
            .fc-daygrid-more-link {
                font-size: 0.6rem !important;
                padding: 0 !important;
                margin-top: 1px !important;
            }

            /* Mobile controls */
            .mobile-calendar-controls .btn {
                width: 100%;
                margin-bottom: 8px;
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }

            /* Dashboard cards mobile */
            .dashboard-card {
                margin-bottom: 1rem;
                border-radius: 0.5rem;
            }

            .col-xl-6,
            .col-lg-6 {
                width: 100%;
                padding: 0 6px;
            }

            /* Client card mobile optimization */
           .recent-clients-container {
                overflow-x: hidden;
            }
            
            .client-card {
                margin-bottom: 0.75rem;
                border-radius: 0.5rem;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            }
            
            .client-card .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.8rem;
                white-space: nowrap;
            }
            
            .client-card h6 {
                font-size: 0.9rem;
                margin-bottom: 0.25rem;
            }
            
            .client-card small {
                font-size: 0.75rem;
            }
            
            .client-card .badge {
                font-size: 0.65rem;
                padding: 0.25rem 0.5rem;
            }
        }
        @media (max-width: 400px) {
            .client-card .d-flex.gap-2 {
                flex-direction: column;
                gap: 0.5rem !important;
            }
            
            .client-card .btn-sm {
                width: 100%;
            }
        }
        @media (min-width: 768px) {
            .client-card {
                transition: all 0.2s ease;
            }
            
            .client-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }
        }

        /* TABLET SPECIFIC STYLES (768px - 991px) */
        @media (min-width: 768px) and (max-width: 991.98px) {
            .app-calendar-sidebar {
                min-width: 220px;
                max-width: 240px;
                height: calc(100vh - 180px);
            }

            .app-calendar-content {
                flex: 1;
            }

            #calendar {
                min-height: 550px;
                padding: 10px;
            }

            .fc .fc-toolbar {
                flex-wrap: wrap;
                padding: 10px 0;
            }

            .fc .fc-toolbar-title {
                font-size: 1.2rem !important;
            }

            .fc .fc-button {
                padding: 0.4rem 0.8rem !important;
                font-size: 0.85rem !important;
                min-width: 40px;
            }

            .fc .fc-daygrid-day {
                min-height: 80px;
            }

            .fc .fc-daygrid-event {
                font-size: 0.75rem;
            }

            .fc-event-title {
                font-size: 0.7rem;
            }

            /* Dashboard cards tablet */
            .col-xl-6,
            .col-lg-6 {
                width: 100%;
                margin-bottom: 1rem;
            }

            .row.g-0 {
                margin: 0;
            }
        }

        /* DESKTOP SPECIFIC STYLES (992px and above) */
        @media (min-width: 992px) {
            .app-calendar-wrapper {
                min-height: 700px;
            }

            .app-calendar-sidebar {
                height: calc(100vh - 220px);
                border-radius: 0.5rem 0 0 0.5rem;
            }

            #calendar {
                min-height: 700px;
                padding: 15px;
            }

            .fc .fc-toolbar-title {
                font-size: 1.4rem !important;
            }

            .fc .fc-button {
                padding: 0.5rem 1rem !important;
                font-size: 0.9rem !important;
            }

            .d-block.d-md-none {
                display: none !important;
            }

            /* Desktop calendar events */
            .fc .fc-daygrid-event {
                font-size: 0.8rem;
                padding: 2px 4px;
                margin: 1px 0;
            }

            .fc-event-title {
                font-size: 0.75rem;
            }
        }

        /* EXTRA SMALL DEVICES (below 576px) */
        @media (max-width: 575.98px) {
            #calendar {
                min-height: 380px;
            }

            .fc .fc-toolbar-title {
                font-size: 1rem !important;
            }

            .fc .fc-button {
                padding: 0.3rem 0.6rem !important;
                font-size: 0.75rem !important;
                min-width: 32px;
                height: 32px;
            }

            .fc .fc-today-button {
                font-size: 0.8rem !important;
                padding: 0.35rem 0.8rem !important;
                max-width: 150px;
            }

            .fc .fc-col-header-cell {
                padding: 3px 0 !important;
            }

            .fc .fc-col-header-cell-cushion {
                font-size: 0.65rem !important;
            }

            .fc .fc-daygrid-day {
                min-height: 55px;
            }

            .fc .fc-daygrid-day-frame {
                min-height: 50px;
            }

            .fc .fc-daygrid-day-number {
                width: 20px !important;
                height: 20px !important;
                font-size: 0.7rem !important;
            }

            .fc .fc-daygrid-event {
                font-size: 0.6rem !important;
                padding: 0.5px 1.5px !important;
                margin: 0.25px 0 !important;
            }

            .fc-event-title {
                font-size: 0.55rem !important;
            }

            .fc-daygrid-day-events {
                margin-top: 18px !important;
            }

            /* Container padding for extra small */
            .container-p-y {
                padding: 0.5rem !important;
            }

            /* Mobile buttons */
            .mobile-calendar-controls .btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.85rem;
            }

            /* Smaller client cards */
            .client-card {
                padding: 10px !important;
            }

            .client-card h6 {
                font-size: 0.9rem;
                margin-bottom: 0.3rem !important;
            }

            .client-card small {
                font-size: 0.75rem;
            }
        }

        /* Event color coding */
        .fc-event.completed {
            background-color: #28a745 !important;
            border-color: #28a745 !important;
            color: white !important;
        }

        .fc-event.pending {
            background-color: #ffc107 !important;
            border-color: #ffc107 !important;
            color: #000 !important;
        }

        .fc-event.ongoing {
            background-color: #17a2b8 !important;
            border-color: #17a2b8 !important;
            color: white !important;
        }

        .fc-event.confirmed {
            background-color: #007bff !important;
            border-color: #007bff !important;
            color: white !important;
        }

        .fc-event.cancelled {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            color: white !important;
        }

        /* Event hover effects */
        .fc-event {
            cursor: pointer;
            transition: all 0.2s ease;
            border-left-width: 3px !important;
        }

        .fc-event:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
        }

        /* Status dots */
        .status-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 6px;
        }

        /* Client card enhancements */
        .client-card {
            transition: all 0.3s ease;
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
        }

        .client-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
            border-color: #dee2e6;
        }

        /* Form responsiveness */
        .event-sidebar .offcanvas-body {
            padding: 1rem;
        }

        @media (max-width: 576px) {
            .event-sidebar {
                width: 100% !important;
            }

            .event-form .btn {
                width: 100%;
                margin-bottom: 8px;
            }

            .event-form .d-flex.justify-content-between {
                flex-direction: column;
            }

            .offcanvas-header {
                padding: 1rem;
            }

            .offcanvas-body {
                padding: 1rem;
            }

            .form-label {
                font-size: 0.9rem;
                margin-bottom: 0.3rem;
            }

            .form-control,
            .form-select {
                font-size: 0.9rem;
                padding: 0.4rem 0.75rem;
            }

            textarea.form-control {
                min-height: 80px;
            }
        }

        /* Chart responsiveness */
        canvas {
            max-width: 100%;
            height: auto !important;
        }

        /* Utility classes for responsiveness */
        .text-truncate-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .text-truncate-1 {
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Loading state for calendar */
        .calendar-loading {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 400px;
            background: #f8f9fa;
            border-radius: 0.5rem;
        }

        /* Print styles */
        @media print {

            .app-calendar-sidebar,
            .mobile-calendar-controls,
            .btn,
            .navbar,
            .footer,
            .offcanvas {
                display: none !important;
            }

            .app-calendar-content {
                width: 100% !important;
                border: none !important;
            }

            #calendar {
                min-height: auto;
                border: none !important;
            }

            .fc-event {
                page-break-inside: avoid;
            }
        }

        /* Fix for calendar view buttons on mobile */
        .fc .fc-button-group .fc-button {
            flex: 1;
            text-align: center;
            min-width: 0;
        }

        /* Better spacing for dashboard cards */
        .dashboard-card .card-body {
            padding: 1rem;
        }

        @media (max-width: 768px) {
            .dashboard-card .card-body {
                padding: 0.75rem;
            }
        }

        /* Improve form select on mobile */
        select.form-control,
        select.form-select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
        }

        /* Touch-friendly form elements */
        .form-control:focus,
        .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        /* Better contrast for disabled state */
        .form-control:disabled,
        .form-select:disabled {
            background-color: #e9ecef;
            opacity: 1;
        }

        /* Responsive table for events modal */
        @media (max-width: 768px) {
            #dateEventsModal .modal-dialog {
                margin: 0.5rem;
                max-width: calc(100% - 1rem);
            }

            #dateEventsModal .modal-content {
                border-radius: 0.5rem;
            }

            #dateEventsModal .modal-header {
                padding: 1rem;
            }

            #dateEventsModal .modal-body {
                padding: 1rem;
                max-height: 70vh;
                overflow-y: auto;
            }

            #dateEventsModal .modal-footer {
                padding: 0.75rem 1rem;
            }

            .event-card {
                padding: 0.75rem !important;
                margin-bottom: 0.5rem !important;
            }

            .event-card h6 {
                font-size: 0.9rem;
            }

            .event-card .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.8rem;
            }
        }
    
    /* Event Card Container */
    .event-card-container {
        border-radius: 16px;
        overflow: hidden;
    }

    /* Main Event Card */
    .event-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 6px 20px rgba(139, 28, 61, 0.08);
        border: 1px solid rgba(139, 28, 61, 0.1);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .event-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(139, 28, 61, 0.15);
    }

    .event-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #8b1c3d 0%, #d65a7c 100%);
        border-radius: 16px 16px 0 0;
    }

    /* Event Header */
    .event-header {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        margin-bottom: 20px;
    }

    .event-date-badge {
        background: linear-gradient(135deg, #8b1c3d, #c24c6a);
        color: white;
        border-radius: 12px;
        padding: 12px;
        min-width: 60px;
        text-align: center;
        box-shadow: 0 4px 12px rgba(139, 28, 61, 0.2);
    }

    .date-day {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1;
    }

    .date-month {
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        opacity: 0.9;
        margin-top: 2px;
    }

    .client-info {
        flex: 1;
    }

    /* Client Avatar */
    .client-avatar {
        position: relative;
        width: 48px;
        height: 48px;
    }

    .avatar-img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid white;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .avatar-fallback {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: linear-gradient(135deg, #8b1c3d, #c24c6a);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        font-weight: 600;
        border: 3px solid white;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .client-name {
        font-size: 1.1rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 4px;
    }

    .event-name {
        font-size: 0.85rem;
        color: #718096;
    }

    /* Status Badge */
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
    }

    .warning-bg { background: rgba(255, 193, 7, 0.15); border: 1px solid rgba(255, 193, 7, 0.3); }
    .primary-bg { background: rgba(13, 110, 253, 0.15); border: 1px solid rgba(13, 110, 253, 0.3); }
    .info-bg { background: rgba(23, 162, 184, 0.15); border: 1px solid rgba(23, 162, 184, 0.3); }
    .success-bg { background: rgba(40, 167, 69, 0.15); border: 1px solid rgba(40, 167, 69, 0.3); }
    .danger-bg { background: rgba(220, 53, 69, 0.15); border: 1px solid rgba(220, 53, 69, 0.3); }

    .warning-text { color: #856404; }
    .primary-text { color: #084298; }
    .info-text { color: #055160; }
    .success-text { color: #0f5132; }
    .danger-text { color: #842029; }

    /* Event Details */
    .event-details {
        background: white;
        border-radius: 12px;
        padding: 15px;
        margin: 15px 0;
        border: 1px solid #e9ecef;
    }

    .detail-item {
        display: flex;
        align-items: center;
        margin-bottom: 12px;
    }

    .detail-item:last-child {
        margin-bottom: 0;
    }

    .detail-icon {
        width: 36px;
        height: 36px;
        background: #f8f9fa;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        color: #8b1c3d;
        font-size: 1rem;
    }

    .detail-label {
        font-size: 0.75rem;
        color: #718096;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .detail-value {
        font-size: 0.9rem;
        color: #2d3748;
        font-weight: 600;
        margin-top: 2px;
    }

    /* Event Actions */
    .event-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
        gap: 10px;
        margin-top: 20px;
    }

    .btn-action {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 10px 5px;
        border-radius: 10px;
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
        border: none;
    }

    .btn-action i {
        font-size: 1.2rem;
        margin-bottom: 4px;
    }

    .btn-action span {
        font-size: 0.75rem;
    }

    .btn-client {
        background: rgba(139, 28, 61, 0.1);
        color: #8b1c3d;
    }

    .btn-event {
        background: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
    }

    .btn-call {
        background: rgba(40, 167, 69, 0.1);
        color: #28a745;
    }

    .btn-email {
        background: rgba(108, 117, 125, 0.1);
        color: #6c757d;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .btn-client:hover { background: #8b1c3d; color: white; }
    .btn-event:hover { background: #0d6efd; color: white; }
    .btn-call:hover { background: #28a745; color: white; }
    .btn-email:hover { background: #6c757d; color: white; }

    /* Quick Stats */
    .quick-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-top: 15px;
    }

    .stat-item {
        background: white;
        border-radius: 12px;
        padding: 12px;
        text-align: center;
        border: 1px solid #e9ecef;
        transition: all 0.2s ease;
    }

    .stat-item:hover {
        border-color: #8b1c3d;
        transform: translateY(-2px);
    }

    .stat-icon {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #8b1c3d, #c24c6a);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 8px;
        font-size: 1rem;
    }

    .stat-value {
        font-size: 1.1rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 2px;
    }

    .stat-label {
        font-size: 0.75rem;
        color: #718096;
        font-weight: 500;
    }

    /* Event Progress */
    .event-progress {
        padding: 15px;
        background: white;
        border-radius: 12px;
        border: 1px solid #e9ecef;
    }

    .progress-label small {
        font-size: 0.8rem;
        font-weight: 600;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .event-header {
            flex-direction: column;
            align-items: stretch;
        }
        
        .event-date-badge {
            align-self: flex-start;
        }
        
        .event-actions {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .quick-stats {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 576px) {
        .event-card {
            padding: 15px;
        }
        
        .event-actions {
            grid-template-columns: 1fr;
        }
        
        .quick-stats {
            grid-template-columns: 1fr;
        }
    }

    /* Animation */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .event-card {
        animation: fadeIn 0.5s ease-out;
    }

    /* Event Card Container */
    .event-card-container {
        border-radius: 16px;
        overflow: hidden;
    }

    /* Main Event Card */
    .event-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 6px 20px rgba(139, 28, 61, 0.08);
        border: 1px solid rgba(139, 28, 61, 0.1);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .event-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(139, 28, 61, 0.15);
    }

    .event-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #8b1c3d 0%, #d65a7c 100%);
        border-radius: 16px 16px 0 0;
    }

    /* Event Header */
    .event-header {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        margin-bottom: 20px;
    }

    .event-date-badge {
        background: linear-gradient(135deg, #8b1c3d, #c24c6a);
        color: white;
        border-radius: 12px;
        padding: 12px;
        min-width: 60px;
        text-align: center;
        box-shadow: 0 4px 12px rgba(139, 28, 61, 0.2);
    }

    .date-day {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1;
    }

    .date-month {
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        opacity: 0.9;
        margin-top: 2px;
    }

    .client-info {
        flex: 1;
    }

    /* Client Avatar */
    .client-avatar {
        position: relative;
        width: 48px;
        height: 48px;
    }

    .avatar-img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid white;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .avatar-fallback {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: linear-gradient(135deg, #8b1c3d, #c24c6a);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        font-weight: 600;
        border: 3px solid white;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .client-name {
        font-size: 1.1rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 4px;
    }

    .event-name {
        font-size: 0.85rem;
        color: #718096;
    }

    /* Status Badge */
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
    }

    .warning-bg { background: rgba(255, 193, 7, 0.15); border: 1px solid rgba(255, 193, 7, 0.3); }
    .primary-bg { background: rgba(13, 110, 253, 0.15); border: 1px solid rgba(13, 110, 253, 0.3); }
    .info-bg { background: rgba(23, 162, 184, 0.15); border: 1px solid rgba(23, 162, 184, 0.3); }
    .success-bg { background: rgba(40, 167, 69, 0.15); border: 1px solid rgba(40, 167, 69, 0.3); }
    .danger-bg { background: rgba(220, 53, 69, 0.15); border: 1px solid rgba(220, 53, 69, 0.3); }

    .warning-text { color: #856404; }
    .primary-text { color: #084298; }
    .info-text { color: #055160; }
    .success-text { color: #0f5132; }
    .danger-text { color: #842029; }

    /* Event Details */
    .event-details {
        background: white;
        border-radius: 12px;
        padding: 15px;
        margin: 15px 0;
        border: 1px solid #e9ecef;
    }

    .detail-item {
        display: flex;
        align-items: center;
        margin-bottom: 12px;
    }

    .detail-item:last-child {
        margin-bottom: 0;
    }

    .detail-icon {
        width: 36px;
        height: 36px;
        background: #f8f9fa;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        color: #8b1c3d;
        font-size: 1rem;
    }

    .detail-label {
        font-size: 0.75rem;
        color: #718096;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .detail-value {
        font-size: 0.9rem;
        color: #2d3748;
        font-weight: 600;
        margin-top: 2px;
    }

    /* Event Actions */
    .event-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
        gap: 10px;
        margin-top: 20px;
    }

    .btn-action {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 10px 5px;
        border-radius: 10px;
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
        border: none;
    }

    .btn-action i {
        font-size: 1.2rem;
        margin-bottom: 4px;
    }

    .btn-action span {
        font-size: 0.75rem;
    }

    .btn-client {
        background: rgba(139, 28, 61, 0.1);
        color: #8b1c3d;
    }

    .btn-event {
        background: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
    }

    .btn-call {
        background: rgba(40, 167, 69, 0.1);
        color: #28a745;
    }

    .btn-email {
        background: rgba(108, 117, 125, 0.1);
        color: #6c757d;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .btn-client:hover { background: #8b1c3d; color: white; }
    .btn-event:hover { background: #0d6efd; color: white; }
    .btn-call:hover { background: #28a745; color: white; }
    .btn-email:hover { background: #6c757d; color: white; }

    /* Quick Stats */
    .quick-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-top: 15px;
    }

    .stat-item {
        background: white;
        border-radius: 12px;
        padding: 12px;
        text-align: center;
        border: 1px solid #e9ecef;
        transition: all 0.2s ease;
    }

    .stat-item:hover {
        border-color: #8b1c3d;
        transform: translateY(-2px);
    }

    .stat-icon {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #8b1c3d, #c24c6a);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 8px;
        font-size: 1rem;
    }

    .stat-value {
        font-size: 1.1rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 2px;
    }

    .stat-label {
        font-size: 0.75rem;
        color: #718096;
        font-weight: 500;
    }

    /* Event Progress */
    .event-progress {
        padding: 15px;
        background: white;
        border-radius: 12px;
        border: 1px solid #e9ecef;
    }

    .progress-label small {
        font-size: 0.8rem;
        font-weight: 600;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .event-header {
            flex-direction: column;
            align-items: stretch;
        }
        
        .event-date-badge {
            align-self: flex-start;
        }
        
        .event-actions {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .quick-stats {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 576px) {
        .event-card {
            padding: 15px;
        }
        
        .event-actions {
            grid-template-columns: 1fr;
        }
        
        .quick-stats {
            grid-template-columns: 1fr;
        }
    }

    /* Animation */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .event-card {
        animation: fadeIn 0.5s ease-out;
    }
    .recent-event-card {
    background: #fff;
    border-radius: 14px;
    padding: 16px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.recent-event-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 28px rgba(0,0,0,0.12);
}

/* Avatar */
.client-avatar-circle {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #6f42c1;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.client-avatar-circle img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.client-avatar-circle i {
    color: #fff;
    font-size: 1.3rem;
}

/* Info rows */
.info-row {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
    color: #555;
    margin-top: 6px;
}

.info-row i {
    color: #6f42c1;
}

/* Status badge */
.status-badge {
    display: inline-block;
    margin-top: 10px;
    padding: 4px 12px;
    font-size: 0.75rem;
    border-radius: 50px;
    font-weight: 600;
}

.status-badge.completed {
    background: #d1e7dd;
    color: #0f5132;
}

.status-badge.pending {
    background: #fff3cd;
    color: #664d03;
}

.status-badge.cancelled {
    background: #f8d7da;
    color: #842029;
}

/* Actions */
.action-row {
    display: flex;
    justify-content: space-between;
    border-top: 1px dashed #ddd;
    padding-top: 10px;
}

.action-row a {
    color: #6c757d;
    font-size: 1.1rem;
    transition: 0.2s;
}

.action-row a:hover {
    color: #6f42c1;
}

</style>
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->
            <?php include_once('./include/menu.php'); ?>
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <?php include_once('./include/navbar.php'); ?>

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <!-- Mobile Calendar Controls (Visible only on mobile) -->
                        <div class="d-block d-md-none mb-3 mobile-calendar-controls">
                            <div class="row g-2">
                                <div class="col-6">
                                    <button style="background: linear-gradient(270deg, rgb(242 136 150) -10%, var(--bs-primary) 100%);"
                                        class="btn btn-primary w-100"
                                        data-bs-toggle="offcanvas"
                                        data-bs-target="#addEventSidebar">
                                        <i class="icon-base ti tabler-plus icon-16px me-1"></i>
                                        <span class="d-none d-sm-inline">Add Event</span>
                                        <span class="d-inline d-sm-none">Add</span>
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button class="btn btn-outline-primary w-100"
                                        data-bs-toggle="offcanvas"
                                        data-bs-target="#mobileCalendarSidebar">
                                        <i class="fas fa-filter me-1"></i>
                                        <span class="d-none d-sm-inline">Filters</span>
                                        <span class="d-inline d-sm-none">Filter</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card app-calendar-wrapper">
                            <div class="row g-0 flex-column flex-md-row">
                                <!-- Calendar Sidebar (Visible only on desktop) -->
                                <div class="col-md-4 app-calendar-sidebar border-end d-none d-md-block">
                                    <div class="border-bottom p-4 my-sm-0 mb-3">
                                        <button style="background: linear-gradient(270deg, rgb(242 136 150) -10%, var(--bs-primary) 100%);"
                                            class="btn btn-primary btn-toggle-sidebar w-100"
                                            data-bs-toggle="offcanvas"
                                            data-bs-target="#addEventSidebar"
                                            aria-controls="addEventSidebar">
                                            <i class="icon-base ti tabler-plus icon-16px me-2"></i>
                                            <span class="align-middle">Add Event</span>
                                        </button>
                                    </div>
                                    <div class="px-3 pt-2">
                                        <!-- inline calendar (flatpicker) -->
                                        <div class="inline-calendar"></div>
                                    </div>
                                    <hr class="mb-4 mx-n4 mt-3" />
                                    <div class="px-4 pb-2">
                                        <!-- Filter -->
                                        <div>
                                            <h5 class="mb-3">Event Filters</h5>
                                        </div>

                                        <div class="form-check form-check-secondary mb-4 ms-2">
                                            <input
                                                class="form-check-input select-all"
                                                type="checkbox"
                                                id="selectAll"
                                                data-value="all"
                                                checked />
                                            <label class="form-check-label" for="selectAll">All Events</label>
                                        </div>

                                        <div class="app-calendar-events-filter text-heading">
                                            <div class="form-check form-check-secondary mb-3 ms-2">
                                                <input
                                                    class="form-check-input input-filter"
                                                    type="checkbox"
                                                    id="select-completed"
                                                    data-value="completed"
                                                    checked />
                                                <label class="form-check-label" for="select-completed">Completed</label>
                                            </div>
                                            <div class="form-check form-check-danger mb-3 ms-2">
                                                <input
                                                    class="form-check-input input-filter"
                                                    type="checkbox"
                                                    id="select-cancelled"
                                                    data-value="cancelled"
                                                    checked />
                                                <label class="form-check-label" for="select-cancelled">Cancelled</label>
                                            </div>
                                            <div class="form-check form-check-warning mb-3 ms-2">
                                                <input
                                                    class="form-check-input input-filter"
                                                    type="checkbox"
                                                    id="select-pending"
                                                    data-value="pending"
                                                    checked />
                                                <label class="form-check-label" for="select-pending">Pending</label>
                                            </div>
                                            <div class="form-check form-check-info mb-3 ms-2">
                                                <input
                                                    class="form-check-input input-filter"
                                                    type="checkbox"
                                                    id="select-ongoing"
                                                    data-value="ongoing"
                                                    checked />
                                                <label class="form-check-label" for="select-ongoing">On Going</label>
                                            </div>
                                            <div class="form-check form-check-primary mb-3 ms-2">
                                                <input
                                                    class="form-check-input input-filter"
                                                    type="checkbox"
                                                    id="select-confirmed"
                                                    data-value="confirmed"
                                                    checked />
                                                <label class="form-check-label" for="select-confirmed">Confirmed</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- /Calendar Sidebar -->

                                <!-- Calendar Content -->
                                <div class="col-12 col-md-8 app-calendar-content">
                                    <div class="card shadow-none border-0">
                                        <div class="card-body p-2 p-md-3">
                                            <!-- FullCalendar -->
                                            <div id="calendar"></div>
                                        </div>
                                    </div>
                                </div>
                                <!-- /Calendar Content -->
                            </div>
                        </div>

                        <!-- Mobile Filter Sidebar (Hidden on desktop) -->
                        <div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="mobileCalendarSidebar">
                            <div class="offcanvas-header border-bottom">
                                <h5 class="offcanvas-title">Calendar Filters</h5>
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body">
                                <div class="mb-4">
                                    <div class="inline-calendar-mobile"></div>
                                </div>
                                <hr class="mb-4" />
                                <div>
                                    <h5 class="mb-3">Event Filters</h5>
                                    <div class="form-check form-check-secondary mb-4">
                                        <input
                                            class="form-check-input select-all-mobile"
                                            type="checkbox"
                                            id="mobileSelectAll"
                                            data-value="all"
                                            checked />
                                        <label class="form-check-label" for="mobileSelectAll">All Events</label>
                                    </div>

                                    <div class="app-calendar-events-filter text-heading">
                                        <div class="form-check form-check-secondary mb-3">
                                            <input
                                                class="form-check-input input-filter-mobile"
                                                type="checkbox"
                                                id="mobileSelectCompleted"
                                                data-value="completed"
                                                checked />
                                            <label class="form-check-label" for="mobileSelectCompleted">Completed</label>
                                        </div>
                                        <div class="form-check form-check-danger mb-3">
                                            <input
                                                class="form-check-input input-filter-mobile"
                                                type="checkbox"
                                                id="mobileSelectCancelled"
                                                data-value="cancelled"
                                                checked />
                                            <label class="form-check-label" for="mobileSelectCancelled">Cancelled</label>
                                        </div>
                                        <div class="form-check form-check-warning mb-3">
                                            <input
                                                class="form-check-input input-filter-mobile"
                                                type="checkbox"
                                                id="mobileSelectPending"
                                                data-value="pending"
                                                checked />
                                            <label class="form-check-label" for="mobileSelectPending">Pending</label>
                                        </div>
                                        <div class="form-check form-check-info mb-3">
                                            <input
                                                class="form-check-input input-filter-mobile"
                                                type="checkbox"
                                                id="mobileSelectOngoing"
                                                data-value="ongoing"
                                                checked />
                                            <label class="form-check-label" for="mobileSelectOngoing">On Going</label>
                                        </div>
                                        <div class="form-check form-check-primary mb-3">
                                            <input
                                                class="form-check-input input-filter-mobile"
                                                type="checkbox"
                                                id="mobileSelectConfirmed"
                                                data-value="confirmed"
                                                checked />
                                            <label class="form-check-label" for="mobileSelectConfirmed">Confirmed</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Add Event Sidebar -->
                        <div class="offcanvas offcanvas-end event-sidebar" tabindex="-1" id="addEventSidebar" aria-labelledby="addEventSidebarLabel">
                            <div class="offcanvas-header border-bottom">
                                <h5 class="offcanvas-title" id="addEventSidebarLabel">Add Event</h5>
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body">
                                <form class="event-form pt-0" id="event-form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="event_date" class="form-label">Event Date <span class="text-danger">*</span></label>
                                        <input type="date" name="event_date" class="form-control" id="event_date" required />
                                    </div>
                                    <div class="mb-3">
                                        <label for="event_name" class="form-label">Event Name <span class="text-danger">*</span></label>
                                        <input type="text" name="event_name" class="form-control" placeholder="Event Name" id="event_name" required />
                                    </div>
                                    <div class="mb-3">
                                        <label for="client_name" class="form-label">Client name <span class="text-danger">*</span></label>
                                        <?php $client = $clientCRUD->getAllClientsSimple() ?>
                                        <select class="form-control" name="client_name" id="js-example-basic-single" required>
                                            <option value="">Select Client</option>
                                            <?php foreach ($client as $clie): ?>
                                                <option value="<?= $clie['id'] ?>"><?= htmlspecialchars($clie['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="mobile" class="form-label">Mobile <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text" name="mobile" placeholder="0987654321" id="mobile" required />
                                    </div>
                                    <div class="mb-3">
                                        <label for="address" class="form-label">Address</label>
                                        <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input class="form-control" type="email" name="email" id="email" placeholder="Enter Email" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="venu_address" class="form-label">Venue (Address)</label>
                                        <textarea class="form-control" id="venu_address" name="venu_address" rows="2"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="venu_contact" class="form-label">Venue (Contact)</label>
                                        <input class="form-control" type="text" name="venu_contact" placeholder="Enter Contact" id="venu_contact" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="requirements" class="form-label">Requirements</label>
                                        <input class="form-control" type="text" name="requirements" placeholder="Enter Requirements" id="requirements" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="model" class="form-label">Model</label>
                                        <input class="form-control" type="text" name="model" placeholder="Enter Model" id="model" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="image_url" class="form-label">Image Url</label>
                                        <input class="form-control" type="text" name="image_url" placeholder="https://test.jpg/" id="image_url" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="location_url" class="form-label">Location Url</label>
                                        <input class="form-control" type="text" name="location_url" placeholder="Google Maps URL" id="location_url" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="price" class="form-label">Price</label>
                                        <input class="form-control" type="number" name="price" placeholder="Enter Price" id="price" step="0.01" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                        <select id="status" name="status" class="form-select" required>
                                            <option value="">Select Status</option>
                                            <option value="pending">Pending</option>
                                            <option value="ongoing">On Going</option>
                                            <option value="confirmed">Confirmed</option>
                                            <option value="completed">Completed</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="attachment" class="form-label">Image Attachment</label>
                                        <input class="form-control" type="file" id="attachment" name="attachment" accept="image/*" />
                                    </div>
                                    <div class="d-flex justify-content-between mt-4 gap-2">
                                        <div>
                                            <button type="submit" id="addEventBtn" class="btn btn-primary btn-add-event me-2">
                                                Add Event
                                            </button>
                                            <button type="button" class="btn btn-label-secondary btn-cancel" data-bs-dismiss="offcanvas">
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <br>
                        
                        <!-- Dashboard Cards -->
                        <div class="row">
                            <!-- Recent Clients -->
                           <div class="col-xl-6 col-lg-6 mb-4">
                                <h4 class="fw-semibold mb-3">Recent Event</h4>

                                <?php if (empty($recentClient)): ?>
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i>
                                        No recent events found.
                                    </div>
                                <?php else: ?>
                                    <div class="recent-event-card">

                                        <!-- Top Row -->
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <div>
                                                <small class="text-muted d-block">
                                                    <i class="bi bi-calendar-event me-1"></i>
                                                    <?= (!empty($recentClient['event_date']) && $recentClient['event_date'] !== '0000-00-00')
                                                        ? date('d M Y', strtotime($recentClient['event_date']))
                                                        : 'Date not set'; ?>
                                                </small>

                                                <h6 class="mb-0 fw-bold">
                                                    <?= sanitize($recentClient['client_name'] ?? 'Unknown Client'); ?>
                                                </h6>
                                            </div>

                                            <!-- Avatar -->
                                            <div class="client-avatar-circle">
                                                <?php if (!empty($recentClient['client_picture'])): ?>
                                                    <img src="<?= sanitize('./' . ltrim($recentClient['client_picture'], './')); ?>"
                                                        alt="<?= sanitize($recentClient['client_name']); ?>"
                                                        onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\'bi bi-person-fill\'></i>';">
                                                <?php else: ?>
                                                    <i class="bi bi-person-fill"></i>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Event -->
                                        <div class="info-row">
                                            <i class="bi bi-calendar-check"></i>
                                            <span><?= sanitize($recentClient['event_name'] ?? 'Not specified'); ?></span>
                                        </div>

                                        <!-- Location -->
                                        <div class="info-row">
                                            <i class="bi bi-geo-alt-fill"></i>
                                            <span><?= sanitize($recentClient['client_location'] ?? 'Not assigned'); ?></span>
                                        </div>

                                        <!-- Status -->
                                        <?php
                                        $status = $recentClient['event_status'] ?? 'pending';
                                        ?>
                                        <span class="status-badge <?= $status; ?>">
                                            <?= ucfirst($status); ?>
                                        </span>

                                        <!-- Actions -->
                                        <div class="action-row mt-3">
                                            <?php if (!empty($recentClient['client_phone'])): ?>
                                                <a href="tel:<?= sanitize($recentClient['client_phone']); ?>" title="Call">
                                                    <i class="bi bi-telephone-fill"></i>
                                                </a>
                                            <?php endif; ?>

                                            <?php if (!empty($recentClient['client_email'])): ?>
                                                <a href="mailto:<?= sanitize($recentClient['client_email']); ?>" title="Email">
                                                    <i class="bi bi-envelope-fill"></i>
                                                </a>
                                            <?php endif; ?>

                                            <a href="view_client.php?id=<?= $recentClient['client_id']; ?>" title="View Client">
                                                <i class="bi bi-person-lines-fill"></i>
                                            </a>

                                            <a href="view_event.php?id=<?= $recentClient['event_id']; ?>" title="View Event">
                                                <i class="bi bi-eye-fill"></i>
                                            </a>
                                        </div>

                                    </div>
                                <?php endif; ?>
                            </div>


                            <!-- Event Status Distribution -->
                            <div class="col-xl-6 col-lg-6 mb-4">
                                <div class="card dashboard-card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">Event Distribution</h5>
                                        <a href="events.php" class="btn btn-sm btn-outline-primary">Manage</a>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="text-center py-4">
                                                    <div class="h1 fw-bold text-primary mb-2"><?php echo $completedEvents; ?></div>
                                                    <span class="text-primary">Completed</span>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-center py-4">
                                                    <div class="h1 fw-bold text-warning mb-2"><?php echo $pendingEvents; ?></div>
                                                    <span class="text-warning">Pending</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <canvas id="eventDistributionChart" height="150"></canvas>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-6">
                                                <div class="d-flex align-items-center">
                                                    <span class="status-dot bg-success me-2"></span>
                                                    <small>Completed: <?php echo $completedEvents; ?></small>
                                                </div>
                                                <div class="d-flex align-items-center mt-2">
                                                    <span class="status-dot bg-warning me-2"></span>
                                                    <small>Pending: <?php echo $pendingEvents; ?></small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="d-flex align-items-center">
                                                    <span class="status-dot bg-info me-2"></span>
                                                    <small>Active: <?php echo $activeEvents; ?></small>
                                                </div>
                                                <div class="d-flex align-items-center mt-2">
                                                    <span class="status-dot bg-secondary me-2"></span>
                                                    <small>Total: <?php echo $totalEvents; ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- / Content -->

                    <!-- Footer -->
                    <?php include_once('./include/footer.php'); ?>
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>

        <!-- Drag Target Area To SlideIn Menu On Small Screens -->
        <div class="drag-target"></div>
    </div>

    <!-- Date Events Modal -->
    <div class="modal fade" id="dateEventsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dateEventsModalLabel">
                        <i class="bi bi-calendar-event me-2"></i>
                        Events for <span id="selectedDate"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="eventsContainer" class="events-container">
                        <!-- Events will be loaded here -->
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading events...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    <script src="./assets/vendor/libs/jquery/jquery.js"></script>
    <script src="./assets/vendor/libs/popper/popper.js"></script>
    <script src="./assets/vendor/js/bootstrap.js"></script>
    <script src="./assets/vendor/libs/node-waves/node-waves.js"></script>
    <script src="./assets/vendor/libs/pickr/pickr.js"></script>
    <script src="./assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="./assets/vendor/libs/hammer/hammer.js"></script>
    <script src="./assets/vendor/libs/i18n/i18n.js"></script>
    <script src="./assets/vendor/js/menu.js"></script>

    <!-- Vendors JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/locales-all.global.min.js"></script>
    <script src="./assets/vendor/libs/@form-validation/popular.js"></script>
    <script src="./assets/vendor/libs/@form-validation/bootstrap5.js"></script>
    <script src="./assets/vendor/libs/@form-validation/auto-focus.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="./assets/vendor/libs/moment/moment.js"></script>
    <script src="./assets/vendor/libs/flatpickr/flatpickr.js"></script>
    <!-- Main JS -->
    <script src="./assets/js/main.js"></script>
    <!-- Page JS -->
    <script src="./assets/js/app-calendar-events.js"></script>
    <script src="./assets/js/app-calendar.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check form submission result
            var resul = "<?php echo isset($result) && $result ? true : false ?>";
            console.log(resul);
            if (resul) {
                $(document).ready(function() {
                    $(".custom-toast.success-toast").click();
                });
            }

            // Initialize Select2 with responsive settings
            $('#js-example-basic-single').select2({
                width: '100%',
                dropdownParent: $('#addEventSidebar'),
                placeholder: "Select Client",
                allowClear: true,
                minimumResultsForSearch: 10
            });

            // Calendar Responsive Initialization
            let calendar;
            let resizeTimer;

            // Initialize calendar
            function initCalendar() {
                // Get calendar events from PHP
                const events = <?php echo json_encode($allEvents); ?>;

                // Format events for FullCalendar
                const calendarEvents = events.map(event => {
                    const status = event.status || 'pending';
                    return {
                        id: event.id,
                        title: event.event_name,
                        start: event.event_date,
                        extendedProps: {
                            client: event.client_name,
                            status: status,
                            venue: event.venu_address,
                            description: event.description,
                            phone: event.mobile,
                            email: event.email
                        },
                        className: `fc-event ${status}`,
                        backgroundColor: getStatusColor(status)
                    };
                });

                // Initialize FullCalendar
                const calendarEl = document.getElementById('calendar');
                calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: window.innerWidth < 768 ? 'dayGridMonth,dayGridWeek' : 'dayGridMonth,dayGridWeek,dayGridDay'
                    },
                    events: calendarEvents,
                    height: 'auto',
                    contentHeight: 'auto',
                    aspectRatio: window.innerWidth < 768 ? 1 : 1.8,
                    eventClick: function(info) {
                        // Show event details
                        const event = info.event;
                        const date = event.start.toLocaleDateString();
                        const modal = $('#dateEventsModal');
                        modal.find('#selectedDate').text(date);

                        // Populate events container
                        const eventsContainer = $('#eventsContainer');
                        const phone = event.extendedProps.phone || '';
                        const email = event.extendedProps.email || '';
                        
                        let contactButtons = '';
                        if (phone) {
                            contactButtons += `
                                <a href="tel:${phone}" 
                                class="btn btn-sm btn-outline-success"
                                title="Call">
                                <i class="bi bi-telephone-fill"></i>
                                </a>`;
                        }
                        if (email) {
                            contactButtons += `
                                <a href="mailto:${email}" 
                                class="btn btn-sm btn-outline-primary"
                                title="Email">
                                <i class="bi bi-envelope-fill"></i>
                                </a>`;
                        }
                        
                        eventsContainer.html(`
                            <div class="event-card mb-3 p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">${event.title}</h6>
                                        <small class="text-muted d-block">
                                            <i class="bi bi-clock me-1"></i>
                                            ${date}
                                        </small>
                                        <small class="text-muted d-block">
                                            <i class="bi bi-person me-1"></i>
                                            Client: ${event.extendedProps.client}
                                        </small>
                                        <small class="text-muted">
                                            <i class="bi bi-geo-alt me-1"></i>
                                            ${event.extendedProps.venue || 'Venue not specified'}
                                        </small>
                                    </div>
                                    <span class="badge bg-${getStatusBadgeColor(event.extendedProps.status)}">
                                        ${event.extendedProps.status}
                                    </span>
                                </div>
                                <div class="d-flex gap-2 flex-wrap mt-2">
                                    ${contactButtons}
                                    <a href="view_event.php?id=${event.id}" 
                                    class="btn btn-sm btn-outline-info"
                                    title="View Details">
                                    <i class="bi bi-eye-fill"></i>
                                    </a>                                    
                                    <a href="edit_event.php?id=${event.id}" 
                                    class="btn btn-sm btn-outline-warning"
                                    title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                    </a>
                                </div>
                            </div>
                        `);

                        modal.modal('show');
                    },
                    datesSet: function(info) {
                        // Adjust calendar on view change
                        adjustCalendarLayout();
                    },
                    viewDidMount: function() {
                        adjustCalendarLayout();
                    }
                });

                calendar.render();

                // Apply initial layout adjustment
                setTimeout(adjustCalendarLayout, 100);
            }

            // Adjust calendar layout based on screen size
            function adjustCalendarLayout() {
                const isMobile = window.innerWidth < 768;

                if (isMobile) {
                    // Mobile-specific adjustments
                    calendar.setOption('headerToolbar', {
                        left: 'prev,next',
                        center: 'title',
                        right: 'today'
                    });

                    calendar.setOption('aspectRatio', 1);

                    // Adjust day cell height for mobile
                    const dayCells = document.querySelectorAll('.fc-daygrid-day');
                    dayCells.forEach(cell => {
                        cell.style.minHeight = '65px';
                    });
                } else {
                    // Desktop settings
                    calendar.setOption('headerToolbar', {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,dayGridWeek,dayGridDay'
                    });

                    calendar.setOption('aspectRatio', 1.8);
                }

                calendar.updateSize();
            }

            // Get status color for calendar events
            function getStatusColor(status) {
                switch (status) {
                    case 'completed':
                        return '#28a745';
                    case 'pending':
                        return '#ffc107';
                    case 'ongoing':
                        return '#17a2b8';
                    case 'confirmed':
                        return '#007bff';
                    case 'cancelled':
                        return '#dc3545';
                    default:
                        return '#6c757d';
                }
            }

            // Get badge color for status
            function getStatusBadgeColor(status) {
                switch (status) {
                    case 'completed':
                        return 'success';
                    case 'pending':
                        return 'warning';
                    case 'ongoing':
                        return 'info';
                    case 'confirmed':
                        return 'primary';
                    case 'cancelled':
                        return 'danger';
                    default:
                        return 'secondary';
                }
            }

            // Handle window resize
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    if (calendar) {
                        adjustCalendarLayout();
                    }
                }, 250);
            });

            // Initialize calendar
            initCalendar();

            // Event Distribution Chart
            const distributionCtx = document.getElementById('eventDistributionChart').getContext('2d');
            const distributionData = {
                labels: ['Completed', 'Active', 'Pending'],
                datasets: [{
                    data: [<?php echo $completedEvents; ?>, <?php echo $activeEvents; ?>, <?php echo $pendingEvents; ?>],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)'
                    ],
                    borderWidth: 1
                }]
            };

            new Chart(distributionCtx, {
                type: 'doughnut',
                data: distributionData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    cutout: '70%'
                }
            });

            // Handle form submission for mobile
            $('#event-form').on('submit', function(e) {
                if (window.innerWidth < 768) {
                    // Add loading state for mobile
                    const btn = $('#addEventBtn');
                    btn.html('<span class="spinner-border spinner-border-sm me-2"></span> Adding...');
                    btn.prop('disabled', true);

                    // Re-enable button after 3 seconds if still on page
                    setTimeout(function() {
                        btn.html('Add Event');
                        btn.prop('disabled', false);
                    }, 3000);
                }
            });

            // Filter handling
            $('.input-filter, .input-filter-mobile').on('change', function() {
                const selectedStatuses = [];
                $('.input-filter:checked, .input-filter-mobile:checked').each(function() {
                    selectedStatuses.push($(this).data('value'));
                });

                // Filter calendar events
                if (calendar) {
                    const allEvents = calendar.getEvents();
                    allEvents.forEach(event => {
                        const eventStatus = event.extendedProps.status;
                        if (selectedStatuses.includes(eventStatus)) {
                            event.setProp('display', 'auto');
                        } else {
                            event.setProp('display', 'none');
                        }
                    });
                }
            });

            // Select all checkboxes
            $('.select-all, .select-all-mobile').on('change', function() {
                const isChecked = $(this).is(':checked');
                $('.input-filter, .input-filter-mobile').prop('checked', isChecked).trigger('change');
            });

            // Auto refresh dashboard every 5 minutes
            setTimeout(function() {
                window.location.reload();
            }, 300000); // 5 minutes

            // Handle mobile sidebar close on filter selection
            $('.input-filter-mobile').on('change', function() {
                setTimeout(() => {
                    if (window.innerWidth < 768) {
                        const sidebar = bootstrap.Offcanvas.getInstance(document.getElementById('mobileCalendarSidebar'));
                        if (sidebar) {
                            sidebar.hide();
                        }
                    }
                }, 500);
            });
        });
    </script>
</body>

</html>