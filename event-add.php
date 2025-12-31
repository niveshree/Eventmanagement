<?php
require_once './functions/auth.php';
require_once './functions/event_crud.php';
require_once './functions/client_crud.php';
requireLogin();

$eventCRUD = new EventCRUD();
$clientCRUD = new ClientCrud();
$client = $clientCRUD->getAllClientsSimple();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle file upload
    $file_name = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $file_name = $eventCRUD->uploadAttachment($_FILES['attachment']);
    }

    // Get form data
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
    
    if ($eventCRUD->createEvent($data, $file_name)) {
        // Store success message in session
        $_SESSION['success_message'] = '✅ Event added successfully!';
        $_SESSION['message_type'] = 'success';
        
        // Redirect to events page
        header("Location: events.php");
        exit();
    } else {
        // Show error message on the same page
        echo '<script>
        Swal.fire({
            title: "Error!",
            text: "Failed to add event. Please try again.",
            icon: "error",
            confirmButtonColor: "#d33",
            confirmButtonText: "OK"
        });
        </script>';
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
                            <div class="bg-gradient-primary rounded-4 p-4 p-md-5 position-relative overflow-hidden">
                                
                                <div class="row align-items-center position-relative">
                                    <div class="col-lg-8">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="avatar avatar-lg me-3">
                                                <div class="avatar-initial bg-white text-primary rounded-3 shadow-sm">
                                                    <i class="bx bx-calendar-plus fs-4"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <h3 class="text-white mb-1">Add New Event</h3>
                                                <p class="text-white opacity-75 mb-0">Create a new event with all the necessary details</p>
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
                                                <li class="breadcrumb-item active text-white">Add Event</li>
                                            </ol>
                                        </nav>
                                    </div>
                                    <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                                        <a href="events.php" class="btn btn-light btn-hover-lift shadow-sm">
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
                                        <i class="bx bx-calendar-event text-primary me-2"></i>
                                        Event Information
                                    </h5>
                                    <span class="badge bg-light text-dark border">
                                        <i class="bx bx-asterisk text-danger fs-tiny"></i> Required fields
                                    </span>
                                </div>
                            </div>
                            
                            <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST" enctype="multipart/form-data" id="eventForm" class="needs-validation" novalidate>
                                <div class="card-body p-4">
                                    <!-- Basic Information Section -->
                                    <div class="mb-5">
                                        <h6 class="section-title mb-4">
                                            <i class="bx bx-info-circle text-primary me-2"></i>
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
                                                        <span id="selectedClientText">Select a client...</span>
                                                    </button>
                                                    <input type="hidden" name="client_name" id="selectedClient">
                                                    
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
                                                            <?php foreach ($client as $clie): ?>
                                                             
                                                                <a class="dropdown-item client-option" 
                                                                href="#" 
                                                                data-id="<?= $clie['id'] ?>"
                                                                data-text="<?= htmlspecialchars($clie['name']) ?> (<?= htmlspecialchars($clie['phone'] ?? 'No phone') ?>)"
                                                                data-client-name="<?= htmlspecialchars($clie['name']) ?>"
                                                                data-client-phone="<?= htmlspecialchars($clie['phone'] ?? '') ?>"
                                                                data-client-email="<?= htmlspecialchars($clie['email'] ?? '') ?>"
                                                                data-client-address="<?= htmlspecialchars($clie['address'] ?? '') ?>">
                                                                    <div class="d-flex justify-content-between align-items-center">
                                                                        <div>
                                                                            <strong><?= htmlspecialchars($clie['name']) ?></strong>
                                                                            <?php if (!empty($clie['phone'])): ?>
                                                                                <div class="text-muted small"><?= htmlspecialchars($clie['phone']) ?></div>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                        <i class="bi bi-check-lg text-primary" style="display: none;"></i>
                                                                    </div>
                                                                </a>
                                                            <?php endforeach; ?>
                                                            
                                                            <?php if (empty($client)): ?>
                                                                <div class="dropdown-item text-muted text-center">
                                                                    No clients found
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        
                                                        <!-- Add client option -->
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item text-primary" href="add-client.php" target="_blank">
                                                            <i class="bx bx-plus-circle me-2"></i> Add new client
                                                        </a>
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
                                            <i class="bx bx-phone text-primary me-2"></i>
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
                                                           placeholder="Email Address">
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
                                                              style="height: 100px;"></textarea>
                                                    <label for="address" class="form-label">Client Address</label>
                                                    <div class="form-text ms-1">Enter the complete client address</div>
                                                    <div class="character-count mt-1">
                                                        <small class="text-muted"><span id="addressCount">0</span>/200 characters</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Venue Details Section -->
                                    <div class="mb-5">
                                        <h6 class="section-title mb-4">
                                            <i class="bx bx-map text-primary me-2"></i>
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
                                                              required></textarea>
                                                    <label for="venu_address" class="form-label">
                                                        Venue Address <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="invalid-feedback">Please enter the venue address</div>
                                                    <div class="form-text ms-1">Complete address of the event venue</div>
                                                    <div class="character-count mt-1">
                                                        <small class="text-muted"><span id="venueAddressCount">0</span>/300 characters</small>
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
                                                           placeholder="Venue Contact">
                                                    <label for="venu_contact" class="form-label">Venue Contact</label>
                                                    <div class="form-text ms-1">Contact person or number at the venue</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Event Details Section -->
                                    <div class="mb-5">
                                        <h6 class="section-title mb-4">
                                            <i class="bx bx-detail text-primary me-2"></i>
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
                                                           placeholder="Requirements">
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
                                                           placeholder="Model/Type">
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
                                                              style="height: 120px;"></textarea>
                                                    <label for="description" class="form-label">Event Description</label>
                                                    <div class="form-text ms-1">Detailed description of the event</div>
                                                    <div class="character-count mt-1">
                                                        <small class="text-muted"><span id="descriptionCount">0</span>/500 characters</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Media & Status Section -->
                                    <div class="mb-4">
                                        <h6 class="section-title mb-4">
                                            <i class="bx bx-images text-primary me-2"></i>
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
                                                           placeholder="Image URL">
                                                    <label for="image_url" class="form-label">Image URL</label>
                                                    <div class="form-text ms-1">URL of event image or reference photo</div>
                                                </div>
                                            </div>

                                            <!-- Location URL -->
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input type="url" 
                                                           class="form-control shadow-sm" 
                                                           name="location_url" 
                                                           id="location_url"
                                                           placeholder="Location URL">
                                                    <label for="location_url" class="form-label">Google Maps URL</label>
                                                    <div class="form-text ms-1">Paste Google Maps embed or share URL</div>
                                                </div>
                                            </div>

                                            <!-- Status -->
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <select class="form-select shadow-sm" 
                                                            name="status" 
                                                            id="status"
                                                            required>
                                                        <option value="">Select status...</option>
                                                        <option value="pending">Pending</option>
                                                        <option value="ongoing">Ongoing</option>
                                                        <option value="confirmed">Confirmed</option>
                                                        <option value="completed">Completed</option>
                                                        <option value="cancelled">Cancelled</option>
                                                    </select>
                                                    <label for="status" class="form-label">
                                                        Status <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="invalid-feedback">Please select event status</div>
                                                    <div class="form-text ms-1">Current status of the event</div>
                                                </div>
                                            </div>

                                            <!-- Attachment -->
                                            <div class="col-md-6">
                                                <div class="card border-dashed h-100">
                                                    <div class="card-body text-center p-4">
                                                        <div class="avatar avatar-xl mx-auto mb-3">
                                                            <div class="avatar-initial bg-light-primary rounded-circle">
                                                                <i class="bx bx-paperclip text-primary fs-4"></i>
                                                            </div>
                                                        </div>
                                                        <h6 class="mb-2">Event Attachment</h6>
                                                        <p class="text-muted fs-tiny mb-3">Images, PDF, Word • Max 5MB</p>
                                                        <div class="d-grid">
                                                            <input type="file" class="form-control" 
                                                                   id="attachment" name="attachment" 
                                                                   accept="image/*,.pdf,.doc,.docx">
                                                            <label for="attachment" class="btn btn-outline-primary btn-sm mt-2">
                                                                <i class="bx bx-upload me-1"></i> Choose File
                                                            </label>
                                                        </div>
                                                        <div id="filePreview" class="mt-3 d-none">
                                                            <img src="" class="img-thumbnail rounded" style="max-height: 100px;">
                                                            <small class="d-block mt-1" id="fileName"></small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Actions -->
                                <div class="card-footer bg-transparent border-top p-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="events.php" class="btn btn-label-secondary btn-hover-lift">
                                            <i class="bx bx-x me-1"></i> Cancel
                                        </a>
                                        <div>
                                            <button type="reset" class="btn btn-outline-primary btn-hover-lift me-2">
                                                <i class="bx bx-reset me-1"></i> Reset Form
                                            </button>
                                            <button type="submit" class="btn btn-primary btn-hover-lift px-4" id="submitBtn">
                                                <i class="text-white bx bx-save me-1"></i> Save Event
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
        
        // File preview
        const fileInput = document.getElementById('attachment');
        const filePreview = document.getElementById('filePreview');
        const fileName = document.getElementById('fileName');
        
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
                        const img = filePreview.querySelector('img');
                        img.src = e.target.result;
                        fileName.textContent = file.name;
                        filePreview.classList.remove('d-none');
                    };
                    reader.readAsDataURL(file);
                } else {
                    // For non-image files
                    filePreview.innerHTML = `
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
        
        // Phone number formatting
        const mobileInput = document.getElementById('mobile');
        mobileInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 10) {
                value = value.substring(0, 10);
            }
            e.target.value = value;
        });
        
        // Set minimum date to today
      /*  const today = new Date().toISOString().split('T')[0];
        document.getElementById('event_date').min = today; */
        

        
        // Form submission
        form.addEventListener('submit', function(e) {
            // Show loading spinner
            submitBtn.disabled = true;
            loadingSpinner.classList.remove('d-none');
            
            // Validate file size if present
            if (fileInput.files.length > 0) {
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
        document.querySelectorAll('.form-control').forEach(input => {
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
    </script>
    
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropdownBtn = document.getElementById('clientDropdownBtn');
    const selectedClientText = document.getElementById('selectedClientText');
    const selectedClientInput = document.getElementById('selectedClient');
    const searchInput = document.getElementById('clientSearchInput');
    const clientOptions = document.querySelectorAll('.client-option'); // Changed from clientItems
    const clientError = document.getElementById('clientError');
    
    // Select client
    clientOptions.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation(); // Prevent page jump
            
            // Get data from the clicked option
            const clientText = this.getAttribute('data-text');
            const clientId = this.getAttribute('data-id');
            
            // Update UI
            selectedClientText.textContent = clientText;
            selectedClientInput.value = clientText.split(' (')[0]; // Get just the name part
            
            // Update button appearance
            dropdownBtn.classList.remove('is-invalid');
            dropdownBtn.classList.add('border-success');
            dropdownBtn.style.borderColor = '#28a745';
            clientError.classList.add('d-none');
            
            // Update all checkmarks
            clientOptions.forEach(option => {
                option.querySelector('.bi-check-lg').style.display = 'none';
            });
            this.querySelector('.bi-check-lg').style.display = 'block';
            
            // Get additional client data for auto-fill
            const clientName = clientText.split(' (')[0];
            const clientPhoneMatch = clientText.match(/\(([^)]+)\)/);
            const clientPhone = clientPhoneMatch ? clientPhoneMatch[1].replace('No phone', '') : '';
            
            // Auto-fill contact fields only if they're empty
            const mobileInput = document.getElementById('mobile');
            const emailInput = document.getElementById('email');
            const addressInput = document.getElementById('address');
            
            if (mobileInput && !mobileInput.value.trim()) {
                mobileInput.value = this.dataset.clientPhone || '';
            }
            if (emailInput && !emailInput.value.trim()) {
                emailInput.value = this.dataset.clientEmail || '';
            }
            if (addressInput && !addressInput.value.trim()) {
                addressInput.value = this.dataset.clientAddress || '';
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
    }
    
    function filterClients(searchTerm) {
        clientOptions.forEach(item => {
            const text = item.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
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
    const form = document.getElementById('eventForm');
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
    
    // Add CSS for selected client
    const style = document.createElement('style');
    style.textContent = `
        .client-option.selected {
            background-color: rgba(102, 126, 234, 0.1);
        }
        .client-option.selected .bi-check-lg {
            display: block !important;
        }
    `;
    document.head.appendChild(style);
});
</script>

    <style>
        :root {
            --primary-gradient: linear-gradient(270deg, rgb(242 136 150) -10%, var(--bs-primary) 100%);
        }
        
        .bg-gradient-primary {
            background: var(--primary-gradient) !important;
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
            box-shadow: 0 0 0 0.25rem var(--bs-primary);
        }
        
        /* .form-control.shadow-sm:focus, .form-select.shadow-sm:focus {
            box-shadow: 0 0 0 0.25rem var(--bs-primary) !important;
        } */
        
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
            box-shadow: 0 4px 12px var(--bs-primary);
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
            background-color: var(--bs-primary) !important;
        }
        
        .bg-light-info {
            background-color: var(--bs-primary) !important;
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

        .bx{
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
        
        /* Form section spacing */
        .mb-5 {
            margin-bottom: 2.5rem !important;
        }
        
        .mb-4 {
            margin-bottom: 2rem !important;
        }
        
        .g-4 {
            --bs-gutter-x: 1.5rem;
            --bs-gutter-y: 1.5rem;
        }
       
        /* Custom dropdown styles */
        #clientDropdownBtn {
            background-color: white;
            transition: all 0.3s ease;
        }

        #clientDropdownBtn:hover {
            border-color: #667eea !important;
        }

        #clientDropdownBtn:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.1);
        }

        .dropdown-menu {
            border: 2px solid #e0e0e0;
            border-radius: 0.5rem;
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }

        .client-item {
            padding: 8px 12px;
            border-radius: 0.375rem;
            margin: 2px 0;
            transition: all 0.2s ease;
        }

        .client-item:hover {
            background-color: rgba(102, 126, 234, 0.1);
            transform: translateX(2px);
        }

        .client-item .bx-check {
            font-size: 1.1rem;
        }

        /* Scrollbar for dropdown */
        #clientList::-webkit-scrollbar {
            width: 6px;
        }

        #clientList::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        #clientList::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        #clientList::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }
    </style>
    <?php include_once('./include/footer.php'); ?>
</body>
</html>