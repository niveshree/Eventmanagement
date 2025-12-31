<?php
require_once './functions/auth.php';
require_once './functions/event_crud.php';
require_once './functions/mobile_view.php';
require_once './functions/client_crud.php';

requireLogin();
$eventCRUD  = new EventCRUD();
$clientCRUD = new ClientCrud();// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $eventId = (int)$_GET['id'];
    
    // Delete the event
    if ($eventCRUD->deleteEvent($eventId)) {
        // Redirect back to the same page with success message
        $_SESSION['message'] = 'Event deleted successfully';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Failed to delete event';
        $_SESSION['message_type'] = 'danger';
    }
    
    // Redirect to remove delete parameters from URL
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit();
}


/* ===============================
   PAGINATION
================================ */
$limit  = 25;
$page   = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

/* ===============================
   FILTERS
================================ */
$currentFilter  = $_GET['status'] ?? 'all';
$selectedMonth  = $_GET['month'] ?? '';
$isUpcoming     = ($currentFilter === 'upcoming');

$currentMonthStart = date('Y-m-01');
$currentMonthEnd   = date('Y-m-t');

/* ===============================
   FETCH EVENTS
================================ */
if ($isUpcoming) {
    $events      = $eventCRUD->getUpcomingEventsPaginated(
        $limit,
        $offset,
        $currentMonthStart,
        $currentMonthEnd
    );
    $totalEvents = $eventCRUD->countUpcomingEvents(
        $currentMonthStart,
        $currentMonthEnd
    );
} else {
    $events      = $eventCRUD->getEventsPaginated(
        $limit,
        $offset,
        $selectedMonth,
        ($currentFilter === 'all') ? '' : $currentFilter
    );
    $totalEvents = $eventCRUD->countFilteredEvents(
        $selectedMonth,
        ($currentFilter === 'all') ? '' : $currentFilter
    );
}

$totalPages = ceil($totalEvents / $limit);

/* ===============================
   STATUS COUNTS (NO UPCOMING)
================================ */
$status_counts = [
    'all'       => $totalEvents,
    'pending'   => 0,
    'confirmed' => 0,
    'ongoing'   => 0,
    'completed' => 0,
    'cancelled' => 0
];

foreach ($events as $event) {
    $s = $event['status'] ?? 'pending';
    if (isset($status_counts[$s])) {
        $status_counts[$s]++;
    }
}

/* ===============================
   PAGE TITLE
================================ */
$pageTitle = match ($currentFilter) {
    'upcoming'  => 'Upcoming Events (This Month)',
    'pending'   => 'Pending Events',
    'confirmed' => 'Confirmed Events',
    'ongoing'   => 'Ongoing Events',
    'completed' => 'Completed Events',
    'cancelled' => 'Cancelled Events',
    default     => 'All Events'
};

/* ===============================
   PAGINATION URL
================================ */
function generatePaginationUrl($pageNum) {
    $params = $_GET;
    $params['page'] = $pageNum;
    return '?' . http_build_query($params);
}
?>

<!doctype html>
<html lang="en" data-bs-theme="light">
<?php include_once('./include/header.php'); ?>
<?php
// Add this before your table
$status_counts = [
    'all' => count($events),
    'pending' => 0,
    'confirmed' => 0,
    'ongoing' => 0,
    'completed' => 0,
    'cancelled' => 0
];

// Count events by status
foreach ($events as $event) {
    $status = $event['status'] ?? 'pending';
    if (isset($status_counts[$status])) {
        $status_counts[$status]++;
    }
}
?>

<!-- Add this CSS -->
<style>
    .status-tabs .nav-link {
        position: relative;
        padding: 0.75rem 1rem;
        border: none;
        background: transparent;
        color: #6c757d;
        font-weight: 500;
        border-bottom: 3px solid transparent;
        transition: all 0.3s;
    }

    .status-tabs .nav-link.active {
        color: #4b93ff;
        border-bottom-color: #4b93ff;
        background-color: rgba(75, 147, 255, 0.05);
    }

    .status-tabs .badge-counter {
        position: absolute;
        top: 4px;
        right: 4px;
        font-size: 0.6rem;
        min-width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .event-row {
        transition: all 0.3s;
    }

    .event-row:hover {
        background-color: rgba(75, 147, 255, 0.05);
        transform: translateY(-1px);
    }

    .table-hover tbody tr.event-row:hover {
        background-color: rgba(75, 147, 255, 0.05);
    }
    .cal-icon-wrap{
        padding: 5px 5px;
    position: absolute;
    bottom: 42%;
    right: 35%;
    }
    .fs-15x{
        font-size: 38px !important;
    }
</style>

<body class="bg-light-subtle">
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- menu -->
            <?php include_once('./include/menu.php'); ?>
            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->
                <!-- <php include_once('./include/navbar.php'); ?> -->
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <div class="container-xxl container-p-y">

                   <!-- Display messages -->
                    <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['message_type'] ?? 'info'; ?> alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php 
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                    endif; ?>
                    <?php if ($currentFilter === 'all'): ?>
                    <div class="page-header mb-4">
                        <div class="bg-gradient-success rounded-4 p-4 p-md-5 position-relative overflow-hidden">
                            <div class="position-absolute top-0 end-0 w-100 h-100 opacity-10">
                                <div class="position-absolute end-n4 top-n4">
                                    <div class="bg-white  p-8 opacity-10"></div>
                                </div>
                            </div>
                            <div class="row align-items-center position-relative">
                                <div class="col-lg-8">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar avatar-lg me-3">
                                            <div class=" cal-icon-wrap bg-white text-success rounded-3 shadow-sm">
                                                <i class="bx bx-calendar-event fs-15x"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h3 class="text-white mb-1">Event Management</h3>
                                            <p class="text-white opacity-75 mb-0">Manage all your events in one place</p>
                                        </div>
                                    </div>
                                    <nav aria-label="breadcrumb">
                                        <ol class="breadcrumb breadcrumb-light">
                                            <li class="breadcrumb-item">
                                                <a href="index.php" class="text-white opacity-75">Dashboard</a>
                                            </li>
                                            <li class="breadcrumb-item active text-white">Events</li>
                                        </ol>
                                    </nav>
                                </div>
                                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                                    <a href="event-add.php" class="btn btn-light btn-hover-lift shadow-sm">
                                        <i class="bx bx-plus me-2"></i>Add New Event
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-3 col-sm-6 mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="text-muted mb-1 d-block">Total Events</span>
                                            <h3 class="mb-0"><?php echo $totalEvents; ?></h3>
                                        </div>
                                        <div class="avatar avatar-lg">
                                            <div class="avatar-initial bg-light-primary rounded">
                                                <i class="bx bx-calendar text-primary"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6 mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="text-muted mb-1 d-block">Completed</span>
                                            <h3 class="mb-0"><?= $status_counts['completed'] ?></h3>
                                        </div>
                                        <div class="avatar avatar-lg">
                                            <div class="avatar-initial bg-light-success rounded">
                                                <i class="bx bx-check-circle text-success"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6 mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="text-muted mb-1 d-block">Ongoing</span>
                                            <h3 class="mb-0"><?= $status_counts['ongoing'] ?></h3>
                                        </div>
                                        <div class="avatar avatar-lg">
                                            <div class="avatar-initial bg-light-warning rounded">
                                                <i class="bx bx-time text-warning"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6 mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="text-muted mb-1 d-block">Pending</span>
                                            <h3 class="mb-0"><?= $status_counts['pending'] ?></h3>
                                        </div>
                                        <div class="avatar avatar-lg">
                                            <div class="avatar-initial bg-light-danger rounded">
                                                <i class="bx bx-alarm text-danger"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <!-- ===============================
                        EVENTS CARD
                    ================================ -->
                    <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between">
                        <h5><?= $pageTitle ?></h5>
                        <?php if (isset($_GET['status'])): ?>
                            <a href="event-add.php" class="btn btn-success">
                                <i class="bx bx-plus me-1"></i>Add New Event
                            </a>
                        <?php endif; ?>

                    </div>

                    <div class="card-body">

                    <!-- STATUS TAB (SINGLE ONLY) -->
                    <div class="mb-3">
                        <strong>Status:</strong>
                        <span class="badge bg-primary"><?= ucfirst($currentFilter) ?></span>
                        <span class="text-muted ms-2">
                            Showing <?= $totalEvents ?> events
                        </span>
                    </div>

                   <!-- Events Table -->
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">EVENT</th>
                                        <th>CLIENT</th>
                                        <th>DATE</th>
                                        <th>VENUE</th>

                                        <th>STATUS</th>
                                        <th class="text-end pe-4">ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody id="events-table-body">
                                    <?php if (!empty($events)): ?>
                                        <?php foreach ($events as $event): ?>
                                            <?php
                                            $status_colors = [
                                                'pending' => ['bg' => 'bg-secondary', 'text' => 'text-white'],
                                                'ongoing' => ['bg' => 'bg-info', 'text' => 'text-white'],
                                                'confirmed' => ['bg' => 'bg-warning', 'text' => 'text-dark'],
                                                'completed' => ['bg' => 'bg-success', 'text' => 'text-white'],
                                                'cancelled' => ['bg' => 'bg-danger', 'text' => 'text-white']
                                            ];
                                            $status = $event['status'] ?? 'pending';
                                            $color = $status_colors[$status] ?? $status_colors['pending'];
                                            $clientName = $event['client_name'];
                                            $clientPhone = $event['mobile'] ?? '';
                                            $clientEmail = $event['email'] ?? '';
                                            ?>

                                            <tr class="event-row"
                                                data-event-id="<?php echo htmlspecialchars($event['id']); ?>"
                                                data-status="<?php echo $status; ?>">
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="position-relative me-3">
                                                            <div class="avatar avatar-sm">
                                                                <div class="avatar-initial bg-light-success rounded-circle">
                                                                    <i class="bx bx-calendar text-success"></i>
                                                                </div>
                                                            </div>
                                                            <span class="position-absolute bottom-0 end-0 translate-middle p-1 <?php echo $color['bg']; ?> border border-2 border-white rounded-circle"></span>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0"><?php echo htmlspecialchars($event['event_name'] ?? 'N/A'); ?></h6>
                                                            <small class="text-muted">#<?php echo $event['id'] ?? ''; ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <small class="text-primary">
                                                            <i class="bx bx-user me-1"></i>
                                                            <?php echo htmlspecialchars($clientName); ?>
                                                        </small>
                                                        <?php if (!empty($clientPhone)): ?>
                                                            <small class="text-success mt-1">
                                                                <i class="bx bx-phone me-1"></i>
                                                                <?php echo htmlspecialchars($clientPhone); ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <i class="bx bx-calendar me-1"></i>
                                                        <?php echo date('d M Y', strtotime($event['event_date'] ?? '')); ?>
                                                    </small>
                                                    <?php if (!empty($event['event_time'])): ?>
                                                        <br>
                                                        <small class="text-muted">
                                                            <i class="bx bx-time me-1"></i>
                                                            <?php echo date('h:i A', strtotime($event['event_time'] ?? '')); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($event['venu_address'])): ?>
                                                        <div class="d-flex align-items-center">
                                                            <i class="bx bx-map text-warning me-1"></i>
                                                            <span title="<?php echo htmlspecialchars($event['venu_address']); ?>">
                                                                <?php
                                                                $venue = htmlspecialchars($event['venu_address']);
                                                                echo strlen($venue) > 30 ? substr($venue, 0, 30) . '...' : $venue;
                                                                ?>
                                                            </span>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>

                                                <td>
                                                    <span class="badge <?php echo $color['bg'] . ' ' . $color['text']; ?> px-3 py-1">
                                                        <i class="bx bx-circle fs-tiny me-1"></i>
                                                        <?php echo ucfirst($status); ?>
                                                    </span>
                                                </td>

                                                <td class="text-end pe-4">
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="view_event.php?id=<?php echo urlencode($event['id']); ?>"
                                                            class="btn btn-outline-info"
                                                            title="View Details">
                                                            <i class="bx bx-show"></i>
                                                        </a>
                                                        <a href="edit_event.php?id=<?php echo urlencode($event['id']); ?>"
                                                            class="btn btn-outline-warning"
                                                            title="Edit">
                                                            <i class="bx bx-edit"></i>
                                                        </a>
                                                        <button type="button"
                                                            class="btn btn-outline-danger delete-event"
                                                            title="Delete"
                                                            data-id="<?php echo htmlspecialchars($event['id']); ?>"
                                                            data-name="<?php echo htmlspecialchars($event['event_name'] ?? ''); ?>">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr id="no-events-row">
                                            <td colspan="8" class="text-center py-5">
                                                <div class="avatar avatar-xl mb-3">
                                                    <div class="avatar-initial bg-light rounded">
                                                        <i class="bx bx-calendar-plus text-muted fs-2"></i>
                                                    </div>
                                                </div>
                                                <h5 class="text-muted mb-2">No events found</h5>
                                                <p class="text-muted mb-4">Get started by adding your first event</p>
                                                <a href="event-add.php" class="btn btn-success">
                                                    <i class="bx bx-plus me-1"></i>Add New Event
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                    </div>

                    <!-- PAGINATION -->
                    <?php if ($totalPages > 1): ?>
                    <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="<?= generatePaginationUrl($i) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    </ul>
                    </nav>
                    <?php endif; ?>

                    </div>
                    </div>
                
                <!-- / Content wrapper -->

                <!-- Floating Add Button -->
                <!-- <a href="event-add.php" class="floating-add-btn" style="text-decoration: none;">
                    <div class="floating-add-btn-inner">
                        <div class="floating-add-btn-content">
                            <i class="bx bx-plus"></i>
                        </div>
                    </div>
                </a> -->

                <!-- Footer -->
                <?php include_once('./include/footer.php'); ?>
            </div>
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
        <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom-0">
                    <div class="avatar avatar-sm me-3">
                        <div class="avatar-initial bg-danger bg-opacity-25 rounded">
                            <i class="bx bx-trash text-danger"></i>
                        </div>
                    </div>
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="mb-2">Are you sure you want to delete the event?</p>
                    <h6 class="text-danger mb-3">"<span id="eventNameDelete"></span>"</h6>
                    <div class="alert alert-warning border-warning bg-warning bg-opacity-10 mb-0">
                        <i class="bx bx-error-circle me-2"></i>
                        This action cannot be undone. All event data and attachments will be permanently deleted.
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary btn-hover-lift" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDelete" class="btn btn-danger btn-hover-lift">
                        <i class="bx bx-trash me-1"></i>Delete Event
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php include_once('./include/script.php'); ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle delete button clicks
            document.querySelectorAll('.delete-event').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const eventId = this.getAttribute('data-id');
                    const eventName = this.getAttribute('data-name');

                    // Set modal content
                    document.getElementById('eventNameDelete').textContent = eventName || 'this event';
                    
                    // Build delete URL with current page parameters
                    const params = new URLSearchParams(window.location.search);
                    params.set('action', 'delete');
                    params.set('id', eventId);
                    
                    document.getElementById('confirmDelete').href = '?' + params.toString();

                    // Show modal
                    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                    deleteModal.show();
                });
            });
            
            // ... rest of your JavaScript ...
        });
    </script>
    <script>
        function filterEvents(status) {
            // Update active tab
            document.querySelectorAll('.nav-link').forEach(tab => {
                tab.classList.remove('active');
            });
            document.getElementById('tab-' + status).classList.add('active');

            // Show/hide events based on status
            const allRows = document.querySelectorAll('.event-row');
            const noEventsRow = document.getElementById('no-events-row');
            let visibleCount = 0;

            allRows.forEach(row => {
                if (status === 'all' || row.getAttribute('data-status') === status) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Show/hide "no events" message
            if (noEventsRow) {
                if (visibleCount === 0) {
                    noEventsRow.style.display = '';
                } else {
                    noEventsRow.style.display = 'none';
                }
            }

            // Update URL hash for bookmarking
            window.location.hash = status;
        }

        // Load from URL hash on page load
        document.addEventListener('DOMContentLoaded', function() {
            const hash = window.location.hash.substring(1);
            const validStatuses = ['all', 'pending', 'confirmed', 'ongoing', 'completed', 'cancelled'];

            if (validStatuses.includes(hash)) {
                filterEvents(hash);
            }

            // Add event listener for delete buttons
            document.querySelectorAll('.delete-event').forEach(button => {
                button.addEventListener('click', function() {
                    const eventId = this.getAttribute('data-id');
                    const eventName = this.getAttribute('data-name');

                    if (confirm(`Are you sure you want to delete "${eventName}"?`)) {
                        // Implement delete functionality here
                        console.log('Delete event:', eventId);
                        // You can make an AJAX call to delete the event
                    }
                });
            });
        });
    </script>

    <style>
        :root {
            --success-gradient: linear-gradient(270deg, rgb(242 136 150) -10%, var(--bs-primary) 100%);
        }

        .bg-gradient-success {
            background: var(--success-gradient) !important;
        }

        .card {
            border: none;
            border-radius: 1rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            border-bottom: 2px solid #f0f0f0;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            color: #6c757d;
        }

        .table tbody tr {
            border-bottom: 1px solid #f8f9fa;
            transition: background-color 0.2s ease;
        }

        .table tbody tr:last-child {
            border-bottom: none;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }

        .btn-hover-lift {
            transition: all 0.3s ease;
        }

        .btn-hover-lift:hover {
            transform: translateY(-2px);
            /* box-shadow: 0 4px 12px rgba(0,0,0,0.1); */
        }

        .badge {
            font-weight: 500;
            padding: 0.5em 1em;
        }

        .floating-add-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 1000;
            text-decoration: none;
        }

        .floating-add-btn-inner {
            width: 60px;
            height: 60px;
            background: linear-gradient(270deg, rgb(242 136 150) -10%, var(--bs-primary) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 20px rgb(242 136 150);
            transition: all 0.3s ease;
        }

        .floating-add-btn:hover .floating-add-btn-inner {
            transform: scale(1.1) rotate(90deg);
            box-shadow: 0 6px 25px rgb(242 136 150);
        }

        .floating-add-btn-content {
            color: white;
            font-size: 1.5rem;
        }

        .avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .avatar-initial {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .bg-light-primary {
            background-color: rgba(13, 110, 253, 0.1) !important;
        }

        .bg-light-success {
            background-color: rgba(40, 167, 69, 0.1) !important;
        }

        .bg-light-warning {
            background-color: rgba(255, 193, 7, 0.1) !important;
        }

        .bg-light-danger {
            background-color: rgba(220, 53, 69, 0.1) !important;
        }

        .page-link {
            border: none;
            color: #6c757d;
            border-radius: 0.5rem;
            margin: 0 2px;
        }

        .page-link:hover {
            background-color: #f8f9fa;
            color: #0d6efd;
        }

        .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .page-item.disabled .page-link {
            background-color: #f8f9fa;
            color: #adb5bd;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .page-header {
                padding: 1rem !important;
            }

            .stats-cards .col-md-3 {
                margin-bottom: 1rem;
            }

            .card-header {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .card-header h5 {
                margin-bottom: 1rem;
            }

            .table-responsive {
                border-radius: 1rem 1rem 0 0;
            }

            .floating-add-btn {
                bottom: 1rem;
                right: 1rem;
            }

            .floating-add-btn-inner {
                width: 50px;
                height: 50px;
            }
        }

        /* Empty state styling */
        .text-center.py-5 {
            padding: 3rem 1rem;
        }

        .text-center.py-5 .avatar-xl {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
        }

        /* Status badge colors */
        .badge.bg-warning.text-dark {
            color: #000 !important;
        }

        /* Button colors */
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }

        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
    </style>
    <?php include_once('./include/footer.php'); ?>
</body>
</html>