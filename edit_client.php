<?php
require_once './functions/client_crud.php';

$clientCrud = new ClientCrud();
$id = $_GET['id'] ?? "";
$client = $clientCrud->getClientByIdSimple($id);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $clientCrud->updateClientSimple($id, $_POST, $_FILES);
    
    if ($result['success']) {
        // Store success message in session
        $_SESSION['success_message'] = '✅ Client updated successfully!';
        $_SESSION['message_type'] = 'success';
        
        // Redirect to client list
        header("Location: client-list.php");
        exit();
    } else {
        // Show error on the same page
        $errorMessage = $result['message'] ?? 'Failed to update client. Please try again.';
        echo '<div class="alert alert-danger">' . htmlspecialchars($errorMessage) . '</div>';
    }
}
?>
<!doctype html>
<html
    lang="en"
    class=" layout-navbar-fixed layout-menu-fixed layout-compact "
    dir="ltr"
    data-skin="default"
    data-bs-theme="light"
    data-assets-path="././assets/"
    data-template="vertical-menu-template">
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
                                                    <i class="bx bx-edit fs-4 text-dark"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <h3 class="text-white mb-1">Edit Client</h3>
                                                <p class="text-white opacity-75 mb-0">Update client information and manage files</p>
                                            </div>
                                        </div>
                                        <nav aria-label="breadcrumb">
                                            <ol class="breadcrumb breadcrumb-light">
                                                <li class="breadcrumb-item">
                                                    <a href="index.php" class="text-white opacity-75">Dashboard</a>
                                                </li>
                                                <li class="breadcrumb-item">
                                                    <a href="client-list.php" class="text-white opacity-75">Clients</a>
                                                </li>
                                                <li class="breadcrumb-item">
                                                    <a href="view_client.php?id=<?php echo $id; ?>" class="text-white opacity-75">View Client</a>
                                                </li>
                                                <li class="breadcrumb-item active text-white">Edit Client</li>
                                            </ol>
                                        </nav>
                                    </div>
                                    <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                                        <a href="view_client.php?id=<?php echo $id; ?>" class="btn btn-light btn-hover-lift shadow-sm me-2">
                                            <i class="bx bx-show me-2"></i>View Client
                                        </a>
                                        <a href="client-list.php" class="btn btn-outline-light btn-hover-lift shadow-sm">
                                            <i class="bx bx-arrow-back me-2"></i>Back to List
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Success/Error Messages -->
                        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                            <?php if (isset($result['success'])): ?>
                                <?php if ($result['success']): ?>
                                    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-3">
                                                <div class="avatar-initial bg-success bg-opacity-25 rounded">
                                                    <i class="bx bx-check-circle text-success"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0">Success!</h6>
                                                <p class="mb-0"><?php echo htmlspecialchars($result['message']); ?></p>
                                            </div>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-3">
                                                <div class="avatar-initial bg-danger bg-opacity-25 rounded">
                                                    <i class="bx bx-error-circle text-danger"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0">Error!</h6>
                                                <p class="mb-0"><?php echo htmlspecialchars($result['message']); ?></p>
                                            </div>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php
                            // Refresh client data after update
                            $client = $clientCrud->getClientByIdSimple($id);
                            ?>
                        <?php endif; ?>
                        
                        <!-- Main Form Card -->
                        <div class="card shadow-sm border-0 overflow-hidden mb-4">
                            <div class="card-header bg-transparent border-bottom p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h5 class="mb-0">
                                        <i class="bx bx-user-edit text-warning me-2"></i>
                                        Client Information
                                    </h5>
                                    <span class="badge bg-light text-dark border">
                                        <i class="bx bx-asterisk text-danger fs-tiny"></i> Required fields
                                    </span>
                                </div>
                            </div>
                            
                            <form action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $id; ?>" method="POST" enctype="multipart/form-data" id="clientForm" class="needs-validation" novalidate>
                                <div class="card-body p-4">
                                    <div class="row g-4">
                                        <!-- Left Column - Basic Info -->
                                        <div class="col-xl-6">
                                            <div class="row g-4">
                                                
                                                <!-- Phone -->
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="tel" class="form-control shadow-sm" 
                                                               name="phone" id="mobile"
                                                               placeholder="Phone Number"
                                                               value="<?php echo isset($client['phone']) ? htmlspecialchars($client['phone']) : ''; ?>"
                                                               pattern="[\d\s\-\+\(\)]{10,20}">
                                                        <label for="mobile" class="form-label">Phone Number</label>
                                                        <div class="invalid-feedback">Please enter a valid phone number</div>
                                                        <div class="form-text ms-1">0987654321 or +1 (123) 456-7890</div>
                                                    </div>
                                                </div>
                                                <!-- Email -->
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="email" class="form-control shadow-sm" 
                                                               name="email" id="email"
                                                               placeholder="Email Address"
                                                               value="<?php echo isset($client['email']) ? htmlspecialchars($client['email']) : ''; ?>"
                                                               required>
                                                        <label for="email" class="form-label">
                                                            Email <span class="text-danger">*</span>
                                                        </label>
                                                        <div class="invalid-feedback">Please enter a valid email address</div>
                                                        <div class="form-text ms-1">example@gmail.com</div>
                                                    </div>
                                                </div>
                                                <!-- Client Name -->
                                                <div class="col-12">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control shadow-sm" 
                                                               name="name" id="clientName" 
                                                               placeholder="Client Name"
                                                               value="<?php echo isset($client['name']) ? htmlspecialchars($client['name']) : ''; ?>"
                                                               required minlength="2" maxlength="100">
                                                        <label for="clientName" class="form-label">
                                                            Client Name <span class="text-danger">*</span>
                                                        </label>
                                                        <div class="invalid-feedback">Please enter a valid client name (2-100 characters)</div>
                                                        <div class="form-text ms-1">Enter the full name of your client</div>
                                                    </div>
                                                </div>
                                                

                                                <!-- Location Name -->
                                                <div class="col-12">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control shadow-sm" 
                                                               name="location" id="location"
                                                               placeholder="Location"
                                                               value="<?php echo isset($client['location']) ? htmlspecialchars($client['location']) : ''; ?>"
                                                               maxlength="100">
                                                        <label for="location" class="form-label">Location Name</label>
                                                        <div class="form-text ms-1">City, State, or Country</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Right Column - Additional Info -->
                                        <div class="col-xl-6">
                                            <div class="row g-4">
                                                <!-- Address -->
                                                <div class="col-12">
                                                    <div class="form-floating">
                                                        <textarea class="form-control shadow-sm" 
                                                                  name="address" id="address" 
                                                                  placeholder="Full Address"
                                                                  style="height: 140px;" 
                                                                  maxlength="500"><?php echo isset($client['address']) ? htmlspecialchars($client['address']) : ''; ?></textarea>
                                                        <label for="address" class="form-label">Full Address</label>
                                                        <div class="form-text ms-1">Street address, apartment, suite, etc.</div>
                                                    </div>
                                                </div>

                                                <!-- Google Maps URL -->
                                                <div class="col-12">
                                                    <div class="form-floating">
                                                        <input type="url" class="form-control shadow-sm" 
                                                               name="url" id="location_url"
                                                               placeholder="Google Maps URL"
                                                               value="<?php echo isset($client['url']) ? htmlspecialchars($client['url']) : ''; ?>"
                                                               maxlength="500">
                                                        <label for="location_url" class="form-label">Google Maps URL</label>
                                                        <div class="form-text ms-1">Paste Google Maps embed URL</div>
                                                    </div>
                                                </div>

                                                <!-- Note -->
                                                <div class="col-12">
                                                    <div class="form-floating">
                                                        <textarea class="form-control shadow-sm" 
                                                                  name="note" id="note" 
                                                                  placeholder="Additional Notes"
                                                                  style="height: 140px;" 
                                                                  maxlength="2000"><?php echo isset($client['note']) ? htmlspecialchars($client['note']) : ''; ?></textarea>
                                                        <label for="note" class="form-label">Additional Notes</label>
                                                        <div class="form-text ms-1">Any additional information about the client</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Files Section -->
                                <div class="card-header bg-transparent border-top border-bottom p-4">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h5 class="mb-0">
                                            <i class="bx bx-paperclip text-warning me-2"></i>
                                            Files & Attachments
                                        </h5>
                                        <small class="text-muted">Existing files will be kept unless replaced</small>
                                    </div>
                                </div>
                                
                                <div class="card-body p-4">
                                    <div class="row g-4">
                                        <!-- Profile Picture -->
                                        <div class="col-md-6">
                                            <div class="card border-dashed h-100">
                                                <div class="card-body p-4">
                                                    <div class="d-flex align-items-start mb-4">
                                                        <div class="avatar avatar-xl me-3">
                                                            <div class="avatar-initial bg-light-warning rounded-circle">
                                                                <i class="bx bx-camera text-warning fs-4"></i>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1">Profile Picture</h6>
                                                            <p class="text-muted fs-tiny mb-0">JPG, PNG, GIF, WebP • Max 5MB</p>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Current Picture -->
                                                    <?php if (!empty($client['picture'])): ?>
                                                    <div class="mb-3">
                                                        <label class="form-label d-block">Current Picture:</label>
                                                        <div class="d-flex align-items-center gap-3">
                                                            <img src="<?php echo htmlspecialchars($client['picture']); ?>" 
                                                                 alt="Profile" 
                                                                 class="img-thumbnail rounded" 
                                                                 style="width: 80px; height: 80px; object-fit: cover;"
                                                                 onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHZpZXdCb3g9IjAgMCA4MCA4MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjgwIiBoZWlnaHQ9IjgwIiByeD0iOCIgZmlsbD0iI0YzRjRGNyIvPgo8cGF0aCBkPSJNNDAgNDBDNDUuNTIyOCA0MCA1MCAzNS41MjI4IDUwIDMwQzUwIDI0LjQ3NzIgNDUuNTIyOCAyMCA0MCAyMEMzNC40NzcyIDIwIDMwIDI0LjQ3NzIgMzAgMzBDMzAgMzUuNTIyOCAzNC40NzcyIDQwIDQwIDQwWiIgZmlsbD0iI0Q4REFFMSIvPgo8cGF0aCBkPSJNNDAgNDVDNTAuMTI1NCA0NSA1OC41IDM2LjYyNTQgNTguNSAyNi41QzU4LjUgMTYuMzc0NiA1MC4xMjU0IDggNDAgOEMyOS44NzQ2IDggMjEuNSAxNi4zNzQ2IDIxLjUgMjYuNUMyMS41IDM2LjYyNTQgMjkuODc0NiA0NSA0MCA0NVoiIGZpbGw9IiNDN0M5REIiLz4KPHBhdGggZD0iTTQ4Ljc1IDU1Ljc1SDU1VjY5LjI1QzU1IDcxLjMyODQgNTMuMzI4NCA3MyA1MS4yNSA3M0gyOC43NUMyNi42NzE2IDczIDI1IDcxLjMyODQgMjUgNjkuMjVWNTUuNzVIMzEuMjVDMzEuODI4NCA1NS43NSAzMi4zNTU3IDU1LjUxNjkgMzIuNzE3NSA1NS4xMDkyQzMzLjA3OTMgNTQuNzAxNiAzMy4yNSA1NC4xNjggMzMuMjUgNTMuNTkxN1Y1MS4yNUMzMy4yNSA1MC4xMTgzIDM0LjExODMgNDkuMjUgMzUuMjUgNDkuMjVINDQuNzVDNDUuODgxNyA0OS4yNSA0Ni43NSA1MC4xMTgzIDQ2Ljc1IDUxLjI1VjUzLjU5MTdDNDYuNzUgNTQuMTY4IDQ2LjkyMDcgNTQuNzAxNiA0Ny4yODI1IDU1LjEwOTJDNDcuNjQ0MyA1NS41MTY5IDQ4LjE3MTYgNTUuNzUgNDguNzUgNTUuNzVaIiBmaWxsPSIjQzdCOUQ5Ii8+Cjwvc3ZnPgo=';">
                                                            <div>
                                                                <a href="<?php echo htmlspecialchars($client['picture']); ?>" 
                                                                   target="_blank" 
                                                                   class="btn btn-sm btn-outline-primary mb-1 d-block">
                                                                    <i class="bx bx-show me-1"></i>View
                                                                </a>
                                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearPictureField()">
                                                                    <i class="bx bx-trash me-1"></i>Remove
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <!-- New Picture Upload -->
                                                    <div class="d-grid">
                                                        <input type="file" class="form-control" 
                                                               id="profilePic" name="pic" accept="image/*"
                                                               onchange="previewImage(this)">
                                                        <label for="profilePic" class="btn btn-outline-warning btn-sm mt-2">
                                                            <i class="bx bx-upload me-1"></i> Choose New Image
                                                        </label>
                                                    </div>
                                                    <div id="imagePreview" class="mt-3 d-none">
                                                        <label class="form-label d-block">New Picture Preview:</label>
                                                        <img src="" class="img-thumbnail rounded border-success" style="max-height: 100px;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Document File -->
                                        <div class="col-md-6">
                                            <div class="card border-dashed h-100">
                                                <div class="card-body p-4">
                                                    <div class="d-flex align-items-start mb-4">
                                                        <div class="avatar avatar-xl me-3">
                                                            <div class="avatar-initial bg-light-info rounded-circle">
                                                                <i class="bx bx-file text-info fs-4"></i>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1">Document File</h6>
                                                            <p class="text-muted fs-tiny mb-0">PDF, DOC, DOCX, TXT • Max 5MB</p>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Current Document -->
                                                    <?php if (!empty($client['file'])): ?>
                                                    <div class="mb-3">
                                                        <label class="form-label d-block">Current Document:</label>
                                                        <div class="d-flex align-items-center justify-content-between bg-light rounded p-3">
                                                            <div class="d-flex align-items-center">
                                                                <i class="bx bx-file text-muted fs-4 me-2"></i>
                                                                <div>
                                                                    <span class="d-block"><?php echo htmlspecialchars(basename($client['file'])); ?></span>
                                                                    <small class="text-muted">Click to download</small>
                                                                </div>
                                                            </div>
                                                            <a href="<?php echo htmlspecialchars($client['file']); ?>" 
                                                               target="_blank" 
                                                               class="btn btn-sm btn-primary">
                                                                <i class="bx bx-download me-1"></i>Download
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <!-- New Document Upload -->
                                                    <div class="d-grid">
                                                        <input type="file" class="form-control" 
                                                               id="documentFile" name="file" 
                                                               accept=".pdf,.doc,.docx,.txt,.zip,.rar,.7z">
                                                        <label for="documentFile" class="btn btn-outline-info btn-sm mt-2">
                                                            <i class="bx bx-upload me-1"></i> Choose New Document
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Multiple Attachments -->
                                        <div class="col-12">
                                            <div class="card border-dashed">
                                                <div class="card-body p-4">
                                                    <div class="d-flex align-items-start mb-4">
                                                        <div class="avatar avatar-xl me-3">
                                                            <div class="avatar-initial bg-light-secondary rounded-circle">
                                                                <i class="bx bx-cloud-upload text-secondary fs-4"></i>
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-1">Additional Attachments</h6>
                                                            <p class="text-muted fs-tiny mb-0">Maximum 10 files, 5MB each • Total max: 20MB</p>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Current Attachments -->
                                                    <?php
                                                    $attachments = $clientCrud->getAttachmentsByUserId($id);
                                                    if (!empty($attachments)):
                                                    ?>
                                                    <div class="mb-4">
                                                        <label class="form-label d-block mb-3">Current Attachments:</label>
                                                        <div class="row g-3">
                                                            <?php foreach ($attachments as $attachment): ?>
                                                            <div class="col-md-6 col-lg-4">
                                                                <div class="card border h-100">
                                                                    <div class="card-body p-3">
                                                                        <div class="d-flex align-items-center mb-2">
                                                                            <div class="avatar avatar-sm me-2">
                                                                                <div class="avatar-initial bg-light-primary rounded">
                                                                                    <i class="bx bx-file text-primary"></i>
                                                                                </div>
                                                                            </div>
                                                                            <div class="flex-grow-1">
                                                                                <h6 class="mb-0 text-truncate" title="<?php echo htmlspecialchars($attachment['original_name']); ?>">
                                                                                    <?php echo htmlspecialchars($attachment['original_name']); ?>
                                                                                </h6>
                                                                            </div>
                                                                        </div>
                                                                        <div class="d-flex justify-content-between align-items-center">
                                                                            <small class="text-muted">
                                                                                <?php echo round($attachment['file_size'] / 1024, 2); ?> KB
                                                                            </small>
                                                                            <a href="<?php echo htmlspecialchars($attachment['file_path']); ?>" 
                                                                               target="_blank" 
                                                                               class="btn btn-sm btn-outline-primary">
                                                                                <i class="bx bx-download"></i>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <!-- New Attachments Upload -->
                                                    <div>
                                                        <input type="file" class="form-control" 
                                                               id="attachments" name="attachment[]" multiple>
                                                        <div class="mt-2" id="fileList"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Actions -->
                                <div class="card-footer bg-transparent border-top p-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <a href="client-list.php" class="btn btn-label-secondary btn-hover-lift me-2">
                                                <i class="bx bx-x me-1"></i> Cancel
                                            </a>
                                            <a href="view_client.php?id=<?php echo $id; ?>" class="btn btn-outline-secondary btn-hover-lift">
                                                <i class="bx bx-show me-1"></i> View Client
                                            </a>
                                        </div>
                                        <div>
                                            <button type="reset" class="btn btn-outline-warning btn-hover-lift me-2">
                                                <i class="bx bx-reset me-1"></i> Reset Changes
                                            </button>
                                            <button type="submit" class="btn btn-warning btn-hover-lift px-4" id="submitBtn">
                                                <i class="bx bx-save me-1"></i> Update Client
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
        const form = document.getElementById('clientForm');
        const profilePicInput = document.getElementById('profilePic');
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
        
        // File validation and preview
        profilePicInput.addEventListener('change', function(e) {
            const file = this.files[0];
            if (file) {
                console.log('File selected:', file.name, 'Size:', file.size, 'Type:', file.type);
                
                // Validate size (5MB)
                const maxSize = 5 * 1024 * 1024;
                if (file.size > maxSize) {
                    showToast('Error: File size exceeds 5MB limit. Please choose a smaller image.', 'error');
                    this.value = '';
                    return;
                }
                
                // Validate type
                const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    showToast('Error: Please select a valid image file (JPG, PNG, GIF, or WebP).', 'error');
                    this.value = '';
                    return;
                }
                
                // Preview image
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagePreview');
                    const img = preview.querySelector('img');
                    img.src = e.target.result;
                    preview.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Show file list for multiple attachments
        document.getElementById('attachments').addEventListener('change', function(e) {
            const fileList = document.getElementById('fileList');
            const files = e.target.files;
            
            if (files.length > 0) {
                let html = '<div class="mt-3"><h6 class="mb-2">New Files:</h6><div class="row g-2">';
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const size = (file.size / 1024).toFixed(2);
                    const icon = getFileIcon(file.type);
                    
                    html += `
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between bg-light rounded p-3">
                                <div class="d-flex align-items-center">
                                    <i class="${icon} text-muted fs-4 me-2"></i>
                                    <div>
                                        <span class="d-block text-truncate" style="max-width: 200px;">${file.name}</span>
                                        <small class="text-muted">${size} KB</small>
                                    </div>
                                </div>
                                <span class="badge bg-success">New</span>
                            </div>
                        </div>
                    `;
                }
                html += '</div></div>';
                fileList.innerHTML = html;
            } else {
                fileList.innerHTML = '';
            }
        });
        
        // Form submission
        form.addEventListener('submit', function(e) {
            // Show loading
            submitBtn.disabled = true;
            loadingSpinner.classList.remove('d-none');
            
            // Validate required fields
            const name = document.getElementById('clientName').value.trim();
            const email = document.getElementById('email').value.trim();
            
            if (!name || !email) {
                e.preventDefault();
                showToast('Please fill in all required fields (Name and Email)', 'error');
                submitBtn.disabled = false;
                loadingSpinner.classList.add('d-none');
                return false;
            }
            
            // Validate email format
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                showToast('Please enter a valid email address', 'error');
                submitBtn.disabled = false;
                loadingSpinner.classList.add('d-none');
                return false;
            }
            
            // File size validation
            const profilePic = document.getElementById('profilePic');
            const documentFile = document.getElementById('documentFile');
            const attachments = document.getElementById('attachments');
            
            // Validate profile picture size
            if (profilePic.files.length > 0) {
                const fileSize = profilePic.files[0].size;
                if (fileSize > 5 * 1024 * 1024) {
                    e.preventDefault();
                    showToast('Profile picture size must be less than 5MB', 'error');
                    submitBtn.disabled = false;
                    loadingSpinner.classList.add('d-none');
                    return false;
                }
            }
            
            // Validate document file size
            if (documentFile.files.length > 0) {
                const fileSize = documentFile.files[0].size;
                if (fileSize > 5 * 1024 * 1024) {
                    e.preventDefault();
                    showToast('Document file size must be less than 5MB', 'error');
                    submitBtn.disabled = false;
                    loadingSpinner.classList.add('d-none');
                    return false;
                }
            }
            
            // Validate attachments
            if (attachments.files.length > 0) {
                let totalSize = 0;
                let maxFiles = 10;
                let maxSize = 20 * 1024 * 1024;
                
                if (attachments.files.length > maxFiles) {
                    e.preventDefault();
                    showToast(`Maximum ${maxFiles} files allowed for attachments`, 'error');
                    submitBtn.disabled = false;
                    loadingSpinner.classList.add('d-none');
                    return false;
                }
                
                for (let i = 0; i < attachments.files.length; i++) {
                    totalSize += attachments.files[i].size;
                    if (attachments.files[i].size > 5 * 1024 * 1024) {
                        e.preventDefault();
                        showToast('Each attachment must be less than 5MB', 'error');
                        submitBtn.disabled = false;
                        loadingSpinner.classList.add('d-none');
                        return false;
                    }
                }
                
                if (totalSize > maxSize) {
                    e.preventDefault();
                    showToast('Total attachments size must be less than 20MB', 'error');
                    submitBtn.disabled = false;
                    loadingSpinner.classList.add('d-none');
                    return false;
                }
            }
            
            return true;
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
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
    });
    
    // Function to clear picture field
    function clearPictureField() {
        const profilePicInput = document.getElementById('profilePic');
        const imagePreview = document.getElementById('imagePreview');
        
        profilePicInput.value = '';
        if (imagePreview) {
            imagePreview.classList.add('d-none');
        }
        
        showToast('Profile picture field cleared. You can upload a new image.', 'info');
    }
    
    // Function to preview image
    function previewImage(input) {
        const preview = document.getElementById('imagePreview');
        const img = preview.querySelector('img');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
                preview.classList.remove('d-none');
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.classList.add('d-none');
        }
    }
    
    // Function to get file icon based on type
    function getFileIcon(type) {
        if (type.includes('pdf')) return 'bx bx-file-pdf';
        if (type.includes('word') || type.includes('document')) return 'bx bx-file-doc';
        if (type.includes('text')) return 'bx bx-file-txt';
        if (type.includes('zip') || type.includes('rar') || type.includes('7z')) return 'bx bx-file-zip';
        return 'bx bx-file';
    }
    
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
    </script>

    <style>
        :root {
            --warning-gradient: linear-gradient(270deg, rgb(242 136 150) -10%, var(--bs-primary) 100%);
        }
        
        .bg-gradient-warning {
            background: linear-gradient(270deg, rgb(242 136 150) -10%, var(--bs-primary) 100%);
        }
        
        .form-floating > .form-control:focus,
        .form-floating > .form-control:not(:placeholder-shown) {
            padding-top: 1.625rem;
            padding-bottom: 0.625rem;
        }
        
        .form-control {
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
            border-radius: 0.5rem;
        }
        
        .form-control:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 0.25rem rgba(245, 158, 11, 0.1);
        }
        
        .form-control.shadow-sm:focus {
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
        
        .bg-light-warning {
            background-color: rgba(245, 158, 11, 0.1) !important;
        }
        
        .bg-light-info {
            background-color: rgba(23, 162, 184, 0.1) !important;
        }
        
        .bg-light-secondary {
            background-color: rgba(108, 117, 125, 0.1) !important;
        }
        
        .bg-light-primary {
            background-color: rgba(13, 110, 253, 0.1) !important;
        }
        
        .border-dashed {
            border-style: dashed !important;
        }
        
        /* Floating label focus state */
        .form-floating.focused label {
            color: #f59e0b;
            transform: scale(0.85) translateY(-0.5rem) translateX(0.15rem);
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
        
        /* Image preview styling */
        .img-thumbnail.border-success {
            border: 2px solid #28a745 !important;
        }
        
        /* File attachment cards */
        .card.border {
            border: 1px solid #e0e0e0 !important;
        }
        
        .card.border:hover {
            border-color: #f59e0b !important;
        }
        
        /* Badge styling */
        .badge.bg-success {
            background-color: #28a745 !important;
        }
        
        /* Button hover effects */
        .btn-warning {
            background-color: #f59e0b;
            border-color: #f59e0b;
            color: white;
        }
        
        .btn-warning:hover {
            background-color: #d97706;
            border-color: #d97706;
            color: white;
        }
    </style>
</body>
</html>