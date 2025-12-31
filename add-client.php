<?php
require_once './functions/auth.php';
require_once './config/database.php';
require_once './functions/client_crud.php';

requireLogin();

$csrfToken = generateCSRFToken();
$successMessage = '';
$errorMessage = '';
$result = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errorMessage = 'Security token mismatch. Please try again.';
    } else {
        try {
            $clientCrud = new ClientCrud();
            
            // Validate required fields
            $errors = [];
            if (empty(trim($_POST['name']))) {
                $errors[] = 'Client name is required';
            }
            if (empty(trim($_POST['email']))) {
                $errors[] = 'Email is required';
            } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format';
            }
            
            if (!empty($errors)) {
                $result = ['success' => false, 'message' => 'Validation failed', 'errors' => $errors];
            } else {
                $result = $clientCrud->createClientSimple($_POST, $_FILES);
                
                if ($result['success']) {
                    // Redirect to client-list.php with success message
                    header("Location: client-list.php?success=1&message=" . urlencode("Client added successfully!"));
                    exit();
                } else {
                    $errorMessage = $result['message'] ?? 'Failed to add client';
                }
            }
        } catch (Exception $e) {
            $errorMessage = 'An internal error occurred. Please try again.';
            error_log("Client creation error: " . $e->getMessage());
        }
    }
}
?>

<!doctype html>
<html lang="en" class="layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr" data-skin="default" data-bs-theme="light" data-assets-path="././assets/" data-template="vertical-menu-template">
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
                                                    <i class="bx bx-user-plus fs-4"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <h3 class="text-white mb-1">Add New Client</h3>
                                                <p class="text-white opacity-75 mb-0">Fill in the client details to add them to your system</p>
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
                                                <li class="breadcrumb-item active text-white">Add Client</li>
                                            </ol>
                                        </nav>
                                    </div>
                                    <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                                        <a href="client-list.php" class="btn btn-light btn-hover-lift shadow-sm">
                                            <i class="bx bx-arrow-back me-2"></i>Back to List
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Success/Error Messages -->
                        <?php if ($successMessage): ?>
                        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3">
                                    <div class="avatar-initial bg-success bg-opacity-25 rounded">
                                        <i class="bx bx-check-circle text-success"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">Success!</h6>
                                    <p class="mb-0"><?php echo htmlspecialchars($successMessage); ?></p>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($errorMessage): ?>
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3">
                                    <div class="avatar-initial bg-danger bg-opacity-25 rounded">
                                        <i class="bx bx-error-circle text-danger"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">Error!</h6>
                                    <p class="mb-0"><?php echo htmlspecialchars($errorMessage); ?></p>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($result && !$result['success'] && isset($result['errors'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                            <div class="d-flex align-items-center mb-2">
                                <div class="avatar avatar-sm me-3">
                                    <div class="avatar-initial bg-danger bg-opacity-25 rounded">
                                        <i class="bx bx-error-circle text-danger"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-0">Please fix the following errors:</h6>
                                </div>
                            </div>
                            <ul class="mb-0 ps-4">
                                <?php foreach ($result['errors'] as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>

                        <!-- Main Form Card -->
                        <div class="card shadow-sm border-0 overflow-hidden mb-4">
                            <div class="card-header bg-transparent border-bottom p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h5 class="mb-0">
                                        <i class="bx bx-user-circle text-primary me-2"></i>
                                        Client Information
                                    </h5>
                                    <span class="badge bg-light text-dark border">
                                        <i class="bx bx-asterisk text-danger fs-tiny"></i> Required fields
                                    </span>
                                </div>
                            </div>
                            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data" id="clientForm" class="needs-validation" novalidate>
                                <!-- CSRF Token -->
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

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
                                                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                                                               pattern="[\d\s\-\+\(\)]{10,20}">
                                                        <label for="mobile" class="form-label">Phone Number</label>
                                                        <div class="invalid-feedback">Please enter a valid phone number</div>
                                                    </div>
                                                </div>

                                                <!-- Email -->
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="email" class="form-control shadow-sm" 
                                                               name="email" id="email"
                                                               placeholder="Email Address"
                                                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
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
                                                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                                                               required minlength="2" maxlength="100">
                                                        <label for="clientName" class="form-label">
                                                            Client Name <span class="text-danger">*</span>
                                                        </label>
                                                        <div class="invalid-feedback">Please enter a valid client name (2-100 characters)</div>
                                                        <div class="form-text ms-1">Enter the full name of your client</div>
                                                    </div>
                                                </div>                                                

                                                <!-- Location -->
                                                <div class="col-12">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control shadow-sm" 
                                                               name="location" id="location"
                                                               placeholder="Location"
                                                               value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>"
                                                               maxlength="100">
                                                        <label for="location" class="form-label">Location</label>
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
                                                                  maxlength="500"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
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
                                                               value="<?php echo isset($_POST['url']) ? htmlspecialchars($_POST['url']) : ''; ?>"
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
                                                                  maxlength="2000"><?php echo isset($_POST['note']) ? htmlspecialchars($_POST['note']) : ''; ?></textarea>
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
                                    <h5 class="mb-0">
                                        <i class="bx bx-paperclip text-primary me-2"></i>
                                        Files & Attachments
                                    </h5>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <!-- Profile Picture Upload -->
                                            <div class="card border-dashed h-100">
                                                <div class="card-body text-center p-4">
                                                    <div class="avatar avatar-xl mx-auto mb-3">
                                                        <div class="avatar-initial bg-light-primary rounded-circle">
                                                            <i class="bx bx-camera text-primary fs-4"></i>
                                                        </div>
                                                    </div>
                                                    <h6 class="mb-2">Profile Picture</h6>
                                                    <p class="text-muted fs-tiny mb-3">JPG, PNG, GIF, WebP • Max 5MB</p>
                                                    <div class="d-grid">
                                                        <input type="file" class="form-control" 
                                                               id="profilePic" name="pic" accept="image/*"
                                                               onchange="previewImage(this)">
                                                        <label for="profilePic" class="btn btn-outline-primary btn-sm mt-2">
                                                            <i class="bx bx-upload me-1"></i> Choose Image
                                                        </label>
                                                    </div>
                                                    <div id="imagePreview" class="mt-3 d-none">
                                                        <img src="" class="img-thumbnail rounded" style="max-height: 100px;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <!-- Document Upload -->
                                            <div class="card border-dashed h-100">
                                                <div class="card-body text-center p-4">
                                                    <div class="avatar avatar-xl mx-auto mb-3">
                                                        <div class="avatar-initial bg-light-info rounded-circle">
                                                            <i class="bx bx-file text-info fs-4"></i>
                                                        </div>
                                                    </div>
                                                    <h6 class="mb-2">Document File</h6>
                                                    <p class="text-muted fs-tiny mb-3">PDF, DOC, DOCX, TXT • Max 5MB</p>
                                                    <div class="d-grid">
                                                        <input type="file" class="form-control" 
                                                               id="documentFile" name="file" 
                                                               accept=".pdf,.doc,.docx,.txt,.zip,.rar,.7z">
                                                        <label for="documentFile" class="btn btn-outline-info btn-sm mt-2">
                                                            <i class="bx bx-upload me-1"></i> Choose Document
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Multiple Attachments -->
                                        <div class="col-12">
                                            <div class="card border-dashed">
                                                <div class="card-body p-4">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="avatar avatar-sm me-3">
                                                            <div class="avatar-initial bg-light-warning rounded">
                                                                <i class="bx bx-cloud-upload text-warning"></i>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0">Additional Attachments</h6>
                                                            <p class="text-muted fs-tiny mb-0">Maximum 10 files, 5MB each • Total max: 20MB</p>
                                                        </div>
                                                    </div>
                                                    <input type="file" class="form-control" 
                                                           id="attachments" name="attachment[]" multiple>
                                                    <div class="mt-2" id="fileList"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Actions -->
                                <div class="card-footer bg-transparent border-top p-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="client-list.php" class="btn btn-label-secondary btn-hover-lift">
                                            <i class="bx bx-x me-1"></i> Cancel
                                        </a>
                                        <div>
                                            <button type="reset" class="btn btn-outline-secondary btn-hover-lift me-2">
                                                <i class="bx bx-reset me-1"></i> Reset Form
                                            </button>
                                            <button type="submit" class="btn btn-primary btn-hover-lift px-4" id="submitBtn">
                                                <i class="bx bx-save me-1"></i> Save Client
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
        // Form validation
        (function () {
            'use strict'
            
            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            var forms = document.querySelectorAll('.needs-validation')
            
            // Loop over them and prevent submission
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

        // File size validation
        document.getElementById('clientForm').addEventListener('submit', function(e) {
            const profilePic = document.getElementById('profilePic');
            const documentFile = document.getElementById('documentFile');
            const attachments = document.getElementById('attachments');
            
            // Validate profile picture size
            if (profilePic.files.length > 0) {
                const fileSize = profilePic.files[0].size;
                if (fileSize > 5 * 1024 * 1024) { // 5MB
                    e.preventDefault();
                    showToast('Profile picture size must be less than 5MB', 'error');
                    profilePic.focus();
                    return false;
                }
            }
            
            // Validate document file size
            if (documentFile.files.length > 0) {
                const fileSize = documentFile.files[0].size;
                if (fileSize > 5 * 1024 * 1024) { // 5MB
                    e.preventDefault();
                    showToast('Document file size must be less than 5MB', 'error');
                    documentFile.focus();
                    return false;
                }
            }
            
            // Validate attachments
            if (attachments.files.length > 0) {
                let totalSize = 0;
                let maxFiles = 10;
                let maxSize = 20 * 1024 * 1024; // 20MB total
                
                if (attachments.files.length > maxFiles) {
                    e.preventDefault();
                    showToast(`Maximum ${maxFiles} files allowed for attachments`, 'error');
                    attachments.focus();
                    return false;
                }
                
                for (let i = 0; i < attachments.files.length; i++) {
                    totalSize += attachments.files[i].size;
                    if (attachments.files[i].size > 5 * 1024 * 1024) {
                        e.preventDefault();
                        showToast('Each attachment must be less than 5MB', 'error');
                        attachments.focus();
                        return false;
                    }
                }
                
                if (totalSize > maxSize) {
                    e.preventDefault();
                    showToast('Total attachments size must be less than 20MB', 'error');
                    attachments.focus();
                    return false;
                }
            }
            
            // Show loading spinner
            const submitBtn = document.getElementById('submitBtn');
            const spinner = document.getElementById('loadingSpinner');
            submitBtn.disabled = true;
            spinner.classList.remove('d-none');
        });

        // Phone number formatting
        document.getElementById('mobile').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 10) {
                value = value.substring(0, 10);
            }
            e.target.value = value;
        });

        // Preview profile picture
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

        // Show file list for multiple attachments
        document.getElementById('attachments').addEventListener('change', function(e) {
            const fileList = document.getElementById('fileList');
            const files = e.target.files;
            
            if (files.length > 0) {
                let html = '<div class="mt-3"><h6 class="mb-2">Selected Files:</h6><div class="list-group">';
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const size = (file.size / 1024).toFixed(2);
                    html += `
                        <div class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bx bx-file me-2"></i>
                                    <span>${file.name}</span>
                                </div>
                                <span class="badge bg-light text-dark">${size} KB</span>
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

        // Toast notification function
        function showToast(message, type = 'info') {
            const toastContainer = document.createElement('div');
            toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
            toastContainer.style.zIndex = '1050';
            
            const toastId = 'toast-' + Date.now();
            const bgClass = type === 'error' ? 'bg-danger' : 'bg-info';
            
            toastContainer.innerHTML = `
                <div id="${toastId}" class="toast show" role="alert">
                    <div class="toast-header ${bgClass} text-white">
                        <i class="bx ${type === 'error' ? 'bx-error' : 'bx-info-circle'} me-2"></i>
                        <strong class="me-auto">${type === 'error' ? 'Error' : 'Info'}</strong>
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

        // Show success message if redirected from successful submission
        <?php if ($successMessage): ?>
        document.addEventListener('DOMContentLoaded', function() {
            // Scroll to top to see the success message
            window.scrollTo({ top: 0, behavior: 'smooth' });
            
            // Auto-clear form after 5 seconds
            setTimeout(function() {
                if (window.location.href.indexOf('success') === -1) {
                    window.location.href = window.location.href.split('?')[0];
                }
            }, 5000);
        });
        <?php endif; ?>

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
    </script>

    <style>
        .bx {
            color: #000;
        }
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
        
        .form-control {
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
            border-radius: 0.5rem;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.1);
        }
        
        .form-control.shadow-sm:focus {
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.1) !important;
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
        
        .bg-light-primary {
            background-color: rgba(102, 126, 234, 0.1) !important;
        }
        
        .bg-light-info {
            background-color: rgba(23, 162, 184, 0.1) !important;
        }
        
        .bg-light-warning {
            background-color: rgba(255, 193, 7, 0.1) !important;
        }
        
        .border-dashed {
            border-style: dashed !important;
        }
        
        .list-group-item {
            border-radius: 0.5rem !important;
            margin-bottom: 0.5rem;
            border: 1px solid #e0e0e0;
        }
        
        .toast {
            border-radius: 0.75rem;
            border: none;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        
        /* Floating label focus state */
        .form-floating.focused label {
            color: #667eea;
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
    </style>
</body>
</html>