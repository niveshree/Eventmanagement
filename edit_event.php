<?php
require_once './functions/auth.php';
require_once './functions/event_crud.php';
require_once './functions/client_crud.php';
requireLogin();

$eventCRUD = new EventCRUD();
$clientCRUD = new ClientCrud();
$message = '';
$id = $_GET['id'] ?? "";

if ($id == '') {
    header("Location: events.php");
    exit;
}

$event = $eventCRUD->getEventById($id);
if (!$event) {
    header("Location: events.php");
    exit;
}

// Get all clients for dropdown
$clients = $clientCRUD->getAllClientsSimple();

// Find current client
$currentClient = null;
$currentClientId = '';
if (!empty($event['client_name'])) {
    foreach ($clients as $client) {
        if ($client['name'] == $event['client_name'] || $client['id'] == $event['client_name']) {
            $currentClient = $client;
            $currentClientId = $client['id'];
            break;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $file_name = $event['attachment'] ?? null;
    
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        if ($event['attachment']) {
            $eventCRUD->deleteAttachment($event['attachment']);
        }
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
        'status' => $_POST['status'] ?? "",
    ];

    $result = $eventCRUD->updateEvent($id, $data, $file_name);
    
    if ($result) {
        // Redirect directly without JavaScript
        header("Location: events.php?success=1&id=" . $id);
        exit;
    } else {
        $message = '<div class="alert alert-danger">Failed to update event. Please try again.</div>';
    }
}
?>

<!doctype html>
<html lang="en" data-bs-theme="light">
<?php include_once('./include/header.php'); ?>

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
                    <!-- Main Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <!-- Page Header with Gradient -->
                        <div class="page-header mb-4">
                            <div class="bg-gradient-warning rounded-4 p-4 p-md-5 position-relative overflow-hidden">
                               
                                <div class="row align-items-center position-relative">
                                    <div class="col-lg-8">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="avatar avatar-lg me-3">
                                                <div class="avatar-initial bg-white text-warning rounded-3 shadow-sm">
                                                    <i class="bx bx-edit fs-4"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <h3 class="text-white mb-1">Edit Event</h3>
                                                <p class="text-white opacity-75 mb-0">Update event information and details</p>
                                            </div>
                                        </div>
                                        <nav aria-label="breadcrumb">
                                            <ol class="breadcrumb breadcrumb-light">
                                                <li class="breadcrumb-item">
                                                    <a href="index.php" class="text-white opacity-75">Dashboard</a>
                                                </li>
                                                <li class="breadcrumb-item">
                                                    <a href="events.php" class="text-white opacity-75">Events</a>
                                                </li>
                                                <li class="breadcrumb-item">
                                                    <a href="view_event.php?id=<?= $id ?>" class="text-white opacity-75">View Event</a>
                                                </li>
                                                <li class="breadcrumb-item active text-white">Edit Event</li>
                                            </ol>
                                        </nav>
                                    </div>
                                    <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                                        <a href="view_event.php?id=<?= $id ?>" class="btn btn-light btn-hover-lift shadow-sm me-2">
                                            <i class="bx bx-show me-2"></i>View Event
                                        </a>
                                        <a href="events.php" class="btn btn-outline-light btn-hover-lift shadow-sm">
                                            <i class="bx bx-arrow-back me-2"></i>Back to Events
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Main Form Card -->
                        <div class="card shadow-sm border-0 overflow-hidden mb-4">
                            <div class="card-header bg-transparent border-bottom p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h5 class="mb-0">
                                        <i class="bx bx-calendar-edit text-warning me-2"></i>
                                        Event Information - <?= htmlspecialchars($event['event_name'] ?? 'Event') ?>
                                    </h5>
                                    <span class="badge bg-light text-dark border">
                                        <i class="bx bx-asterisk text-danger fs-tiny"></i> Required fields
                                    </span>
                                </div>
                            </div>
                            
                            <form action="<?php echo $_SERVER['PHP_SELF'] ?>?id=<?= $id ?>" method="POST" enctype="multipart/form-data" id="eventForm" class="needs-validation" novalidate>
                                <div class="card-body p-4">
                                    <!-- Basic Information Section -->
                                    <div class="mb-5">
                                        <h6 class="section-title mb-4">
                                            <i class="bx bx-info-circle text-warning me-2"></i>
                                            Basic Information
                                        </h6>
                                        <div class="row g-4">
                                            <!-- Event Date -->
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input type="date" 
                                                           class="form-control shadow-sm" 
                                                           name="event_date" 
                                                           id="event_date"
                                                           value="<?= htmlspecialchars($event['event_date'] ?? '') ?>"
                                                           required>
                                                    <label for="event_date" class="form-label">
                                                        Event Date <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="invalid-feedback">Please select the event date</div>
                                                    <div class="form-text ms-1">Select the date of the event</div>
                                                </div>
                                            </div>

                                            <!-- Event Name -->
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input type="text" 
                                                           class="form-control shadow-sm" 
                                                           name="event_name" 
                                                           id="event_name"
                                                           placeholder="Event Name"
                                                           value="<?= htmlspecialchars($event['event_name'] ?? '') ?>"
                                                           required>
                                                    <label for="event_name" class="form-label">
                                                        Event Name <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="invalid-feedback">Please enter the event name</div>
                                                    <div class="form-text ms-1">e.g., Wedding Ceremony, Corporate Meeting</div>
                                                </div>
                                            </div>

                                            <!-- Client Selection -->
                                            <div class="col-md-6">
                                                <label for="clientDropdown" class="form-label">
                                                    Client <span class="text-danger">*</span>
                                                </label>
                                                
                                                <div class="dropdown" id="clientDropdownContainer">
                                                    <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" 
                                                            type="button" 
                                                            id="clientDropdownBtn"
                                                            data-bs-toggle="dropdown" 
                                                            aria-expanded="false"
                                                            style="border-radius: 0.5rem; padding: 0.75rem 1rem; border: 2px solid #e0e0e0; min-height: 46px;">
                                                        <span id="selectedClientText">
                                                            <?php if (!empty($currentClient)): ?>
                                                                <?= htmlspecialchars($currentClient['name']) ?> 
                                                                <?php if (!empty($currentClient['phone'])): ?>
                                                                    (<?= htmlspecialchars($currentClient['phone']) ?>)
                                                                <?php endif; ?>
                                                            <?php else: ?>
                                                                Select a client...
                                                            <?php endif; ?>
                                                        </span>
                                                    </button>
                                                    <input type="hidden" name="client_name" id="selectedClient" value="<?= $currentClient['name'] ?? '' ?>" required>
                                                    
                                                    <div class="dropdown-menu w-100 p-2" aria-labelledby="clientDropdownBtn" style="max-height: 300px; ">
                                                        <!-- Search input -->
                                                        <div class="mb-2">
                                                            <input type="text" 
                                                                class="form-control form-control-sm" 
                                                                placeholder="Search clients..." 
                                                                id="clientSearchInput"
                                                                style="border-radius: 0.5rem; border: 1px solid #e0e0e0;">
                                                        </div>
                                                        
                                                        <!-- Client list -->
                                                        <div class="client-list" style="max-height: 250px; overflow-y: auto;">
                                                            <?php foreach ($clients as $client): ?>
                                                                <a class="dropdown-item client-option" 
                                                                href="#" 
                                                                data-id="<?= $client['id'] ?>"
                                                                data-text="<?= htmlspecialchars($client['name']) ?> (<?= htmlspecialchars($client['phone'] ?? 'No phone') ?>)"
                                                                data-client-name="<?= htmlspecialchars($client['name']) ?>"
                                                                data-client-phone="<?= htmlspecialchars($client['phone'] ?? '') ?>"
                                                                data-client-email="<?= htmlspecialchars($client['email'] ?? '') ?>"
                                                                data-client-address="<?= htmlspecialchars($client['address'] ?? '') ?>"
                                                                <?= ($currentClient && $currentClient['id'] == $client['id']) ? 'data-selected="true"' : '' ?>>
                                                                    <div class="d-flex justify-content-between align-items-center">
                                                                        <div>
                                                                            <strong><?= htmlspecialchars($client['name']) ?></strong>
                                                                            <?php if (!empty($client['phone'])): ?>
                                                                                <div class="text-muted small"><?= htmlspecialchars($client['phone']) ?></div>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                        <i class="bi bi-check-lg text-primary" style="display: <?= ($currentClient && $currentClient['id'] == $client['id']) ? 'block' : 'none' ?>;"></i>
                                                                    </div>
                                                                </a>
                                                            <?php endforeach; ?>
                                                            
                                                            <?php if (empty($clients)): ?>
                                                                <div class="dropdown-item text-muted text-center">
                                                                    No clients found
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        
                                                        <!-- Add client option -->
                                                        <div class="dropdown-divider"></div>
                                                        
                                                    </div>
                                                </div>
                                                
                                                <div class="invalid-feedback d-none" id="clientError">Please select a client</div>
                                                <div class="form-text ms-1 mt-1">
                                                    <a href="add-client.php" class="text-primary" target="_blank">
                                                        <i class="bx bx-plus-circle me-1"></i> Add new client
                                                    </a>
                                                </div>
                                            </div>



                                        </div>
                                    </div>

                                    <!-- Contact Information Section -->
                                    <div class="mb-5">
                                        <h6 class="section-title mb-4">
                                            <i class="bx bx-phone text-warning me-2"></i>
                                            Contact Information
                                        </h6>
                                        <div class="row g-4">
                                            <!-- Mobile -->
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input type="tel" 
                                                           class="form-control shadow-sm" 
                                                           name="mobile" 
                                                           id="mobile"
                                                           placeholder="Mobile Number"
                                                           value="<?= htmlspecialchars($event['mobile'] ?? '') ?>"
                                                           pattern="[0-9]{10}"
                                                           required>
                                                    <label for="mobile" class="form-label">
                                                        Mobile Number <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="invalid-feedback">Please enter a valid 10-digit mobile number</div>
                                                    <div class="form-text ms-1">10 digits without spaces</div>
                                                </div>
                                            </div>

                                            <!-- Email -->
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input type="email" 
                                                           class="form-control shadow-sm" 
                                                           name="email" 
                                                           id="email"
                                                           placeholder="Email Address"
                                                           value="<?= htmlspecialchars($event['email'] ?? '') ?>">
                                                    <label for="email" class="form-label">Email Address</label>
                                                    <div class="invalid-feedback">Please enter a valid email address</div>
                                                    <div class="form-text ms-1">client@example.com</div>
                                                </div>
                                            </div>

                                            <!-- Address -->
                                            <div class="col-12">
                                                <div class="form-floating">
                                                    <textarea class="form-control shadow-sm" 
                                                              name="address" 
                                                              id="address" 
                                                              placeholder="Client Address"
                                                              style="height: 100px;"><?= htmlspecialchars($event['address'] ?? '') ?></textarea>
                                                    <label for="address" class="form-label">Client Address</label>
                                                    <div class="form-text ms-1">Enter the complete client address</div>
                                                    <div class="character-count mt-1">
                                                        <small class="text-muted"><span id="addressCount"><?= strlen($event['address'] ?? '') ?></span>/200 characters</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Venue Details Section -->
                                    <div class="mb-5">
                                        <h6 class="section-title mb-4">
                                            <i class="bx bx-map text-warning me-2"></i>
                                            Venue Details
                                        </h6>
                                        <div class="row g-4">
                                            <!-- Venue Address -->
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <textarea class="form-control shadow-sm" 
                                                              name="venu_address" 
                                                              id="venu_address" 
                                                              placeholder="Venue Address"
                                                              style="height: 100px;"
                                                              required><?= htmlspecialchars($event['venu_address'] ?? '') ?></textarea>
                                                    <label for="venu_address" class="form-label">
                                                        Venue Address <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="invalid-feedback">Please enter the venue address</div>
                                                    <div class="form-text ms-1">Complete address of the event venue</div>
                                                    <div class="character-count mt-1">
                                                        <small class="text-muted"><span id="venueAddressCount"><?= strlen($event['venu_address'] ?? '') ?></span>/300 characters</small>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Venue Contact -->
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input type="text" 
                                                           class="form-control shadow-sm" 
                                                           name="venu_contact" 
                                                           id="venu_contact"
                                                           placeholder="Venue Contact"
                                                           value="<?= htmlspecialchars($event['venu_contact'] ?? '') ?>">
                                                    <label for="venu_contact" class="form-label">Venue Contact</label>
                                                    <div class="form-text ms-1">Contact person or number at the venue</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Event Details Section -->
                                    <div class="mb-5">
                                        <h6 class="section-title mb-4">
                                            <i class="bx bx-detail text-warning me-2"></i>
                                            Event Details
                                        </h6>
                                        <div class="row g-4">
                                            <!-- Requirements -->
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input type="text" 
                                                           class="form-control shadow-sm" 
                                                           name="requirements" 
                                                           id="requirements"
                                                           placeholder="Requirements"
                                                           value="<?= htmlspecialchars($event['requirements'] ?? '') ?>">
                                                    <label for="requirements" class="form-label">Requirements</label>
                                                    <div class="form-text ms-1">e.g., Sound system, Catering, Decorations</div>
                                                </div>
                                            </div>

                                            <!-- Model/Type -->
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input type="text" 
                                                           class="form-control shadow-sm" 
                                                           name="model" 
                                                           id="model"
                                                           placeholder="Model/Type"
                                                           value="<?= htmlspecialchars($event['model'] ?? '') ?>">
                                                    <label for="model" class="form-label">Model/Type</label>
                                                    <div class="form-text ms-1">e.g., Corporate, Wedding, Birthday</div>
                                                </div>
                                            </div>

                                            <!-- Description -->
                                            <div class="col-12">
                                                <div class="form-floating">
                                                    <textarea class="form-control shadow-sm" 
                                                              name="description" 
                                                              id="description" 
                                                              placeholder="Event Description"
                                                              style="height: 120px;"><?= htmlspecialchars($event['description'] ?? '') ?></textarea>
                                                    <label for="description" class="form-label">Event Description</label>
                                                    <div class="form-text ms-1">Detailed description of the event</div>
                                                    <div class="character-count mt-1">
                                                        <small class="text-muted"><span id="descriptionCount"><?= strlen($event['description'] ?? '') ?></span>/500 characters</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Media & Status Section -->
                                    <div class="mb-4">
                                        <h6 class="section-title mb-4">
                                            <i class="bx bx-images text-warning me-2"></i>
                                            Media & Status
                                        </h6>
                                        <div class="row g-4">
                                            <!-- Image URL -->
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input type="url" 
                                                           class="form-control shadow-sm" 
                                                           name="image_url" 
                                                           id="image_url"
                                                           placeholder="Image URL"
                                                           value="<?= htmlspecialchars($event['image_url'] ?? '') ?>">
                                                    <label for="image_url" class="form-label">Image URL</label>
                                                    <div class="form-text ms-1">URL of event image or reference photo</div>
                                                </div>
                                                <?php if (!empty($event['image_url'])): ?>
                                                <div class="mt-3">
                                                    <label class="form-label d-block">Current Image:</label>
                                                    <img src="<?= htmlspecialchars($event['image_url']) ?>" 
                                                         alt="Event Image" 
                                                         class="img-thumbnail rounded border-warning"
                                                         style="max-height: 150px; max-width: 100%; object-fit: cover;"
                                                         onerror="this.style.display='none';">
                                                </div>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Location URL -->
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input type="url" 
                                                           class="form-control shadow-sm" 
                                                           name="location_url" 
                                                           id="location_url"
                                                           placeholder="Location URL"
                                                           value="<?= htmlspecialchars($event['location_url'] ?? '') ?>">
                                                    <label for="location_url" class="form-label">Google Maps URL</label>
                                                    <div class="form-text ms-1">Paste Google Maps embed or share URL</div>
                                                </div>
                                                <?php if (!empty($event['location_url'])): ?>
                                                <div class="mt-3">
                                                    <label class="form-label d-block">Map Preview:</label>
                                                    <div class="border rounded" style="height: 150px; overflow: hidden;">
                                                        <iframe src="<?= htmlspecialchars($event['location_url']) ?>" 
                                                                width="100%" 
                                                                height="100%" 
                                                                style="border:0;" 
                                                                allowfullscreen 
                                                                loading="lazy" 
                                                                referrerpolicy="no-referrer-when-downgrade">
                                                        </iframe>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Status -->
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <select class="form-select shadow-sm" 
                                                            name="status" 
                                                            id="status"
                                                            required>
                                                        <option value="">Select status...</option>
                                                        <option value="pending" <?= ($event['status'] ?? '') == 'pending' ? 'selected' : '' ?>>Pending</option>
                                                        <option value="ongoing" <?= ($event['status'] ?? '') == 'ongoing' ? 'selected' : '' ?>>Ongoing</option>
                                                        <option value="confirmed" <?= ($event['status'] ?? '') == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                                        <option value="completed" <?= ($event['status'] ?? '') == 'completed' ? 'selected' : '' ?>>Completed</option>
                                                        <option value="cancelled" <?= ($event['status'] ?? '') == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                    </select>
                                                    <label for="status" class="form-label">
                                                        Status <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="invalid-feedback">Please select event status</div>
                                                    <div class="form-text ms-1">
                                                        Current status: 
                                                        <span class="badge <?php 
                                                            $statusColor = 'bg-secondary';
                                                            if (isset($event['status'])) {
                                                                switch($event['status']) {
                                                                    case 'pending': $statusColor = 'bg-secondary text-dark'; break;
                                                                    case 'ongoing': $statusColor = 'bg-info text-dark'; break;
                                                                    case 'confirmed': $statusColor = 'bg-warning'; break;
                                                                    case 'completed': $statusColor = 'bg-success'; break;
                                                                    case 'cancelled': $statusColor = 'bg-danger'; break;
                                                                }
                                                            }
                                                            echo $statusColor;
                                                        ?> ms-2">
                                                            <?= htmlspecialchars(ucfirst($event['status'] ?? 'Not set')) ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Attachment -->
                                            <div class="col-md-6">
                                                <div class="card border-dashed h-100">
                                                    <div class="card-body p-4">
                                                        <div class="d-flex align-items-start mb-4">
                                                            <div class="avatar avatar-xl me-3">
                                                                <div class="avatar-initial bg-light-warning rounded-circle">
                                                                    <i class="bx bx-paperclip text-warning fs-4"></i>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-1">Event Attachment</h6>
                                                                <p class="text-muted fs-tiny mb-0">Images, PDF, Word • Max 5MB</p>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Current Attachment -->
                                                        <?php if (!empty($event['attachment'])): ?>
                                                        <div class="mb-3">
                                                            <label class="form-label d-block">Current Attachment:</label>
                                                            <div class="d-flex align-items-center justify-content-between bg-light rounded p-3">
                                                                <div class="d-flex align-items-center">
                                                                    <i class="bx bx-file text-muted fs-4 me-2"></i>
                                                                    <div>
                                                                        <span class="d-block"><?= htmlspecialchars($event['attachment']) ?></span>
                                                                        <small class="text-muted">Click to download</small>
                                                                    </div>
                                                                </div>
                                                                <div>
                                                                    <a href="uploads/<?= htmlspecialchars($event['attachment']) ?>" 
                                                                       target="_blank" 
                                                                       class="btn btn-sm btn-outline-warning me-1">
                                                                        <i class="bx bx-show"></i>
                                                                    </a>
                                                                    <a href="uploads/<?= htmlspecialchars($event['attachment']) ?>" 
                                                                       class="btn btn-sm btn-warning" 
                                                                       download>
                                                                        <i class="bx bx-download"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <?php endif; ?>
                                                        
                                                        <!-- New Attachment Upload -->
                                                        <div class="d-grid">
                                                            <input type="file" class="form-control" 
                                                                   id="attachment" name="attachment" 
                                                                   accept="image/*,.pdf,.doc,.docx">
                                                            <label for="attachment" class="btn btn-outline-warning btn-sm mt-2">
                                                                <i class="bx bx-upload me-1"></i> Choose New File
                                                            </label>
                                                        </div>
                                                        <div id="filePreview" class="mt-3 d-none">
                                                            <label class="form-label d-block">New File Preview:</label>
                                                            <img src="" class="img-thumbnail rounded border-success" style="max-height: 100px;">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Event Meta Information -->
                                    <div class="alert alert-warning border-warning bg-warning bg-opacity-10 mb-0">
                                        <div class="d-flex align-items-center">
                                            <i class="bx bx-info-circle me-2"></i>
                                            <div>
                                                <small>Event created: <?= date('d M Y, h:i A', strtotime($event['created_at'] ?? '')) ?></small><br>
                                                <small>Last updated: <?= date('d M Y, h:i A', strtotime($event['updated_at'] ?? '')) ?></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Actions -->
                                <div class="card-footer bg-transparent border-top p-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <a href="events.php" class="btn btn-label-secondary btn-hover-lift me-2">
                                                <i class="bx bx-x me-1"></i> Cancel
                                            </a>
                                            <a href="view_event.php?id=<?= $id ?>" class="btn btn-outline-warning btn-hover-lift">
                                                <i class="bx bx-show me-1"></i> View Event
                                            </a>
                                        </div>
                                        <div>
                                            <button type="reset" class="btn btn-outline-warning btn-hover-lift me-2">
                                                <i class="bx bx-reset me-1"></i> Reset Changes
                                            </button>
                                            <button type="submit" class="btn btn-warning btn-hover-lift px-4" id="submitBtn">
                                                <i class="bx bx-save me-1"></i> Update Event
                                                <span class="spinner-border spinner-border-sm d-none ms-1" role="status" aria-hidden="true" id="loadingSpinner"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- / Content wrapper -->

                <!-- Footer -->
                <?php include_once('./include/footer.php'); ?>
            </div>
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
        <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->

    <?php include_once('./include/script.php'); ?>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('eventForm');
        const submitBtn = document.getElementById('submitBtn');
        const loadingSpinner = document.getElementById('loadingSpinner');
        const clientSelect = document.getElementById('clientSelect');
        const mobileInput = document.getElementById('mobile');
        const emailInput = document.getElementById('email');
        const addressInput = document.getElementById('address');
        
        // Form validation
        (function () {
            'use strict'
            
            var forms = document.querySelectorAll('.needs-validation')
            
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
        
        // Character counters
        const textareas = {
            'address': 200,
            'venu_address': 300,
            'description': 500
        };
        
        Object.entries(textareas).forEach(([id, maxLength]) => {
            const textarea = document.getElementById(id);
            const counter = document.getElementById(id + 'Count');
            
            if (textarea && counter) {
                textarea.addEventListener('input', function() {
                    const length = this.value.length;
                    counter.textContent = length;
                    
                    if (length > maxLength) {
                        this.value = this.value.substring(0, maxLength);
                        counter.textContent = maxLength;
                    }
                    
                    // Color coding
                    if (length > maxLength * 0.9) {
                        counter.style.color = '#dc3545';
                    } else if (length > maxLength * 0.75) {
                        counter.style.color = '#ffc107';
                    } else {
                        counter.style.color = '#6c757d';
                    }
                });
                
                // Initialize
                textarea.dispatchEvent(new Event('input'));
            }
        });
        
        // Auto-fill contact info when client is selected
        if (clientSelect) {
            clientSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const clientPhone = selectedOption.dataset.phone || '';
                const clientEmail = selectedOption.dataset.email || '';
                const clientAddress = selectedOption.dataset.address || '';
                
                // Only auto-fill if fields are empty
                if (mobileInput && !mobileInput.value.trim()) {
                    mobileInput.value = clientPhone;
                }
                if (emailInput && !emailInput.value.trim()) {
                    emailInput.value = clientEmail;
                }
                if (addressInput && !addressInput.value.trim()) {
                    addressInput.value = clientAddress;
                }
            });
        }
        
        // File preview
        const fileInput = document.getElementById('attachment');
        const filePreview = document.getElementById('filePreview');
        
        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                const file = this.files[0];
                if (file) {
                    console.log('File selected:', file.name, 'Size:', file.size, 'Type:', file.type);
                    
                    // Validate size (5MB)
                    const maxSize = 5 * 1024 * 1024;
                    if (file.size > maxSize) {
                        showToast('Error: File size exceeds 5MB limit. Please choose a smaller file.', 'error');
                        this.value = '';
                        return;
                    }
                    
                    // Validate type
                    const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                    if (!validTypes.includes(file.type) && !file.type.startsWith('image/')) {
                        showToast('Error: Please select a valid file (Image, PDF, or Word document).', 'error');
                        this.value = '';
                        return;
                    }
                    
                    // Preview image if it's an image
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            filePreview.innerHTML = `
                                <label class="form-label d-block">New File Preview:</label>
                                <img src="${e.target.result}" class="img-thumbnail rounded border-success" style="max-height: 100px;">
                                <small class="d-block mt-1">${file.name}</small>
                            `;
                            filePreview.classList.remove('d-none');
                        };
                        reader.readAsDataURL(file);
                    } else {
                        // For non-image files
                        filePreview.innerHTML = `
                            <label class="form-label d-block">New File:</label>
                            <div class="avatar avatar-lg mx-auto mb-2">
                                <div class="avatar-initial bg-light-info rounded-circle">
                                    <i class="bx bx-file text-info fs-3"></i>
                                </div>
                            </div>
                            <small class="d-block mt-1">${file.name}</small>
                        `;
                        filePreview.classList.remove('d-none');
                    }
                } else {
                    filePreview.classList.add('d-none');
                }
            });
        }
        
        // Phone number formatting
        if (mobileInput) {
            mobileInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 10) {
                    value = value.substring(0, 10);
                }
                e.target.value = value;
            });
        }
        
        // Set minimum date to today
       /* const today = new Date().toISOString().split('T')[0];
        const eventDateInput = document.getElementById('event_date');
        if (eventDateInput) {
            eventDateInput.min = today;
            
            // Format the date for display
            if (eventDateInput.value) {
                const date = new Date(eventDateInput.value);
                const formattedDate = date.toISOString().split('T')[0];
                eventDateInput.value = formattedDate;
            }
        } */
        

        
        // Form submission
        form.addEventListener('submit', function(e) {
            // Show loading spinner
            submitBtn.disabled = true;
            loadingSpinner.classList.remove('d-none');
            
            // Validate file size if present
            if (fileInput && fileInput.files.length > 0) {
                const fileSize = fileInput.files[0].size;
                if (fileSize > 5 * 1024 * 1024) {
                    e.preventDefault();
                    showToast('File size must be less than 5MB', 'error');
                    submitBtn.disabled = false;
                    loadingSpinner.classList.add('d-none');
                    return false;
                }
            }
            
            return true;
        });
        
        // Enhance form controls with animations
        document.querySelectorAll('.form-control, .form-select').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.parentElement.classList.remove('focused');
                }
            });
        });
        
        // Toast notification function
        function showToast(message, type = 'info') {
            const toastContainer = document.createElement('div');
            toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
            toastContainer.style.zIndex = '1050';
            
            const toastId = 'toast-' + Date.now();
            const bgClass = type === 'error' ? 'bg-danger' : type === 'success' ? 'bg-success' : 'bg-info';
            
            toastContainer.innerHTML = `
                <div id="${toastId}" class="toast show" role="alert">
                    <div class="toast-header ${bgClass} text-white">
                        <i class="bx ${type === 'error' ? 'bx-error' : type === 'success' ? 'bx-check-circle' : 'bx-info-circle'} me-2"></i>
                        <strong class="me-auto">${type === 'error' ? 'Error' : type === 'success' ? 'Success' : 'Info'}</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                </div>
            `;
            
            document.body.appendChild(toastContainer);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                const toast = document.getElementById(toastId);
                if (toast) {
                    toast.remove();
                }
                toastContainer.remove();
            }, 5000);
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const dropdownBtn = document.getElementById('clientDropdownBtn');
        const selectedClientText = document.getElementById('selectedClientText');
        const selectedClientInput = document.getElementById('selectedClient');
        const searchInput = document.getElementById('clientSearchInput');
        const clientOptions = document.querySelectorAll('.client-option');
        const clientError = document.getElementById('clientError');
        
        // Select client
        clientOptions.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Get data from the clicked option
                const clientText = this.getAttribute('data-text');
                const clientId = this.getAttribute('data-id');
                const clientName = this.dataset.clientName;
                const clientPhone = this.dataset.clientPhone;
                const clientEmail = this.dataset.clientEmail;
                const clientAddress = this.dataset.clientAddress;
                
                // Update UI
                selectedClientText.textContent = clientText;
                selectedClientInput.value = clientName;
                
                // Update button appearance
                dropdownBtn.classList.remove('is-invalid', 'border-danger');
                dropdownBtn.classList.add('border-success');
                dropdownBtn.style.borderColor = '#28a745';
                clientError.classList.add('d-none');
                
                // Update all checkmarks
                clientOptions.forEach(option => {
                    option.querySelector('.bi-check-lg').style.display = 'none';
                });
                this.querySelector('.bi-check-lg').style.display = 'block';
                
                // Auto-fill contact fields only if they're empty
                const mobileInput = document.getElementById('mobile');
                const emailInput = document.getElementById('email');
                const addressInput = document.getElementById('address');
                
                if (mobileInput) {
                    mobileInput.value = clientPhone;
                }
                if (emailInput) {
                    emailInput.value = clientEmail;
                }
                if (addressInput) {
                    addressInput.value = clientAddress;
                }
                
                // Close dropdown
                const dropdown = bootstrap.Dropdown.getInstance(dropdownBtn);
                dropdown.hide();
                
                // Clear search
                if (searchInput) {
                    searchInput.value = '';
                    filterClients('');
                }
            });
        });
        
        // Search functionality
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                filterClients(this.value.toLowerCase());
            });
            
            searchInput.addEventListener('keydown', function(e) {
                // Prevent form submission when pressing enter in search
                if (e.key === 'Enter') {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        }
        
        function filterClients(searchTerm) {
            clientOptions.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (searchTerm === '' || text.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }
        
        // Clear search when dropdown closes
        dropdownBtn.addEventListener('hidden.bs.dropdown', function() {
            if (searchInput) {
                searchInput.value = '';
                filterClients('');
            }
        });
        
        // Form validation
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!selectedClientInput.value) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    dropdownBtn.classList.add('is-invalid', 'border-danger');
                    dropdownBtn.style.borderColor = '#dc3545';
                    clientError.classList.remove('d-none');
                    
                    // Focus the dropdown
                    dropdownBtn.focus();
                }
            });
        }
        
        // Remove error state when client is selected
        selectedClientInput.addEventListener('change', function() {
            dropdownBtn.classList.remove('is-invalid', 'border-danger');
            dropdownBtn.style.borderColor = '#28a745';
            clientError.classList.add('d-none');
        });
    });
    </script>

    <style>
        :root {
            --warning-gradient: linear-gradient(270deg, var(--bs-primary) -10%, var(--bs-primary) 100%);
        }
        
        .bg-gradient-warning {
            background: var(--warning-gradient) !important;
        }
        
        .form-floating > .form-control:focus,
        .form-floating > .form-control:not(:placeholder-shown) {
            padding-top: 1.625rem;
            padding-bottom: 0.625rem;
        }
        
        .form-floating > label {
            transition: all 0.2s ease;
        }
        
        .form-control, .form-select {
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
            border-radius: 0.5rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 0.25rem rgba(245, 158, 11, 0.1);
        }
        
        .form-control.shadow-sm:focus, .form-select.shadow-sm:focus {
            box-shadow: 0 0 0 0.25rem rgba(245, 158, 11, 0.1) !important;
        }
        
        .card {
            border: none;
            border-radius: 1rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-2px);
        }
        
        .card.border-dashed {
            border: 2px dashed #dee2e6 !important;
            background: #f8f9fa;
        }
        
        .btn-hover-lift {
            transition: all 0.3s ease;
        }
        
        .btn-hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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
        
        .bg-light-warning {
            background-color: rgba(245, 158, 11, 0.1) !important;
        }
        
        .bg-light-info {
            background-color: rgba(23, 162, 184, 0.1) !important;
        }
        
        .border-dashed {
            border-style: dashed !important;
        }
        
        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--bs-primary);
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #f0f2f5;
            margin-bottom: 1.5rem;
        }
        
        .character-count {
            text-align: right;
            font-size: 0.85rem;
        }
        
        /* Floating label focus state */
        .form-floating.focused label {
            color: var(--bs-primary);
            transform: scale(0.85) translateY(-0.5rem) translateX(0.15rem);
        }
        .bx-edit{
            color: var(--bs-primary);
        }
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .card-body {
                padding: 1.5rem !important;
            }
            
            .page-header {
                padding: 1rem !important;
            }
            
            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 1rem;
            }
            
            .d-flex.justify-content-between > div {
                width: 100%;
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
            
            .form-floating > .form-control {
                padding: 1rem 0.75rem;
            }
        }
        
    
    </style>
</body>
</html>