<?php
require_once './functions/auth.php';
require_once './config/database.php';
require_once './functions/client_crud.php';
require_once './functions/mobile_view.php';

requireLogin();

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function sanitize($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: client-list.php');
    exit();
}

$client_id = intval($_GET['id']);
$clientCrud = new ClientCrud();

$client = $clientCrud->getClientByIdSimple($client_id);

if (!$client) {
    header('Location: client-list.php');
    exit();
}

function logClientView($client_id, $user_id) {
    try {
        if (function_exists('getDBConnection')) {
            $conn = getDBConnection();
            
            $checkTable = $conn->query("SHOW TABLES LIKE 'activity_log'");
            if ($checkTable->num_rows == 0) {
                $createTable = "CREATE TABLE IF NOT EXISTS activity_log (
                    id INT(11) AUTO_INCREMENT PRIMARY KEY,
                    user_id INT(11),
                    client_id INT(11),
                    action VARCHAR(50),
                    ip_address VARCHAR(45),
                    user_agent TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_user_id (user_id),
                    INDEX idx_client_id (client_id),
                    INDEX idx_created_at (created_at)
                )";
                $conn->query($createTable);
            }
            
            $stmt = $conn->prepare("INSERT INTO activity_log (user_id, client_id, action, ip_address, user_agent) VALUES (?, ?, 'viewed', ?, ?)");
            if ($stmt) {
                $stmt->bind_param("iiss", $user_id, $client_id, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
                $stmt->execute();
                $stmt->close();
            }
            $conn->close();
            
            return true;
        }
        
        return false;
        
    } catch (Exception $e) {
        error_log("Failed to log client view: " . $e->getMessage());
        return false;
    }
}

logClientView($client_id, $_SESSION['user_id']);

function isAllowedFileType($filename) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip', 'rar'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, $allowed);
}

function secureFilePath($file_path) {
    if (empty($file_path)) {
        return null;
    }
    
    $possible_paths = [
        realpath('./uploads/'),
        realpath('uploads/'),
        realpath('./uploads/'),
        realpath(__DIR__ . '/./uploads/'),
        realpath(__DIR__ . '/uploads/')
    ];
    
    $base_path = null;
    foreach ($possible_paths as $path) {
        if ($path && is_dir($path)) {
            $base_path = $path;
            break;
        }
    }
    
    if (!$base_path) {
        $upload_dir = __DIR__ . '/./uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $base_path = realpath($upload_dir);
    }
    
    if (!$base_path) {
        return null;
    }
    
    $full_path = realpath($file_path);
    
    if (!$full_path && strpos($file_path, '/') !== 0) {
        $full_path = realpath($base_path . '/' . $file_path);
    }
    
    if ($full_path && strpos($full_path, $base_path) === 0) {
        return $full_path;
    }
    return null;
}

function getFileIcon($ext) {
    $icons = [
        'pdf' => 'bi-file-pdf',
        'doc' => 'bi-file-word',
        'docx' => 'bi-file-word',
        'xls' => 'bi-file-excel',
        'xlsx' => 'bi-file-excel',
        'jpg' => 'bi-file-image',
        'jpeg' => 'bi-file-image',
        'png' => 'bi-file-image',
        'gif' => 'bi-file-image',
        'zip' => 'bi-file-zip',
        'rar' => 'bi-file-zip',
        'txt' => 'bi-file-text'
    ];
    return $icons[strtolower($ext)] ?? 'bi-file';
}

function formatFileSize($bytes) {
    if ($bytes == 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return number_format($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

$attachments_result = $clientCrud->getAttachmentsByUserId($client_id);
$attachments = [];

if ($attachments_result) {
    if (is_object($attachments_result) && method_exists($attachments_result, 'fetch_assoc')) {
        if ($attachments_result->num_rows > 0) {
            while ($attachment = $attachments_result->fetch_assoc()) {
                $secure_path = secureFilePath($attachment['file_path']);
                if ($secure_path && file_exists($secure_path) && isAllowedFileType($attachment['file_path'])) {
                    $attachment['secure_path'] = $secure_path;
                    $attachments[] = $attachment;
                }
            }
        }
    } elseif (is_array($attachments_result)) {
        foreach ($attachments_result as $attachment) {
            $secure_path = secureFilePath($attachment['file_path']);
            if ($secure_path && file_exists($secure_path) && isAllowedFileType($attachment['file_path'])) {
                $attachment['secure_path'] = $secure_path;
                $attachments[] = $attachment;
            }
        }
    }
}

if (!empty($client['file'])) {
    $secure_path = secureFilePath($client['file']);
    if ($secure_path && file_exists($secure_path) && isAllowedFileType($client['file'])) {
        $file_name = basename($secure_path);
        $file_size = filesize($secure_path);
        
        $has_main_file = false;
        foreach ($attachments as $attachment) {
            if (isset($attachment['file_path']) && $attachment['file_path'] == $client['file']) {
                $has_main_file = true;
                break;
            }
        }
        
        if (!$has_main_file) {
            $attachments[] = [
                'file_path' => $client['file'],
                'secure_path' => $secure_path,
                'original_name' => $file_name,
                'file_size' => $file_size,
                'created_at' => $client['created_at'] ?? date('Y-m-d H:i:s')
            ];
        }
    }
}

// Generate share link for PHP
function generateShareLink($client_id) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['PHP_SELF']);
    return $protocol . "://" . $host . $path . "/share_client.php?id=" . $client_id;
}

$share_link = generateShareLink($client_id);
?>

<?php include_once('./include/header.php'); ?>
<!-- Add Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    :root {
        --primary-gradient: linear-gradient(270deg, rgb(242 136 150) -10%, var(--bs-primary) 100%);
        --success-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .client-profile-header {
        background: var(--primary-gradient);
        color: white;
        padding: 3rem 2rem;
        border-radius: 15px 15px 0 0;
        position: relative;
        overflow: hidden;
    }

    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 5px solid white;
        object-fit: cover;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }

    .info-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        margin-bottom: 1.5rem;
    }

    .info-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 30px rgba(0,0,0,0.12);
    }

    .detail-item {
        padding: 12px 0;
        border-bottom: 1px solid #f5f5f5;
        display: flex;
        align-items: center;
    }

    .detail-item:last-child {
        border-bottom: none;
    }

    .detail-label {
        min-width: 140px;
        color: #6c757d;
        font-weight: 500;
    }

    .detail-value {
        flex: 1;
        color: #343a40;
        font-weight: 500;
    }

    .contact-chip {
        display: inline-flex;
        align-items: center;
        padding: 8px 16px;
        background: rgba(102, 126, 234, 0.1);
        border-radius: 25px;
        margin: 5px;
        text-decoration: none;
        color: #fff;
        transition: all 0.3s ease;
    }

    .contact-chip:hover {
        background: rgba(102, 126, 234, 0.2);
        transform: translateY(-2px);
        color: #4a5fcf;
    }

    .attachment-card {
        border: 1px solid #e9ecef;
        border-radius: 10px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .file-icon {
        font-size: 2.5rem;
        color: #667eea;
    }

    .action-buttons {
        position: fixed;
        bottom: 100px;
        right: 30px;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .action-btn {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
        position: relative;
    }

    .action-btn:hover {
        transform: scale(1.1);
    }

    .btn-edit { background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); }
    .btn-share { background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); }
    .btn-download { background: linear-gradient(135deg, #28a745 0%, #218838 100%); }
    .btn-delete { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); }

    .social-share-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 20px;
    }

    .share-btn {
        flex: 1;
        min-width: 120px;
        border-radius: 8px;
        padding: 10px 15px;
        border: none;
        color: white;
        font-weight: 500;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .share-btn:hover {
        transform: translateY(-2px);
    }

    .btn-whatsapp { background: #25D366; }
    .btn-email { background: #EA4335; }
    .btn-copy { background: #6c757d; }
    .btn-print { background: #17a2b8; }
    .btn-facebook { background: #1877F2; }
    .btn-twitter { background: #1DA1F2; }
    .btn-linkedin { background: #0077B5; }
    
    .btn-download { 
        background: linear-gradient(135deg, #28a745 0%, #218838 100%); 
    }

    .badge-status {
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 500;
        font-size: 0.85rem;
    }
    
    .status-active { background-color: #28a745; color: white; }
    .status-inactive { background-color: #6c757d; color: white; }
    .status-pending { background-color: #ffc107; color: #000; }
    .status-blocked { background-color: #dc3545; color: white; }
</style>

    <script>
    // CSRF token
    const csrfToken = '<?php echo $_SESSION['csrf_token']; ?>';
    const clientId = <?php echo $client_id; ?>;
    const shareLink = '<?php echo $share_link; ?>';
    
    // Client data for JavaScript
    const clientData = <?php echo json_encode($client); ?>;
    const clientName = '<?php echo addslashes($client['name']); ?>';
    const clientEmail = '<?php echo addslashes($client['email'] ?? 'N/A'); ?>';
    const clientPhone = '<?php echo addslashes($client['phone'] ?? 'N/A'); ?>';
    const clientAddress = '<?php echo addslashes($client['address'] ?? 'N/A'); ?>';
    const clientLocation = '<?php echo addslashes($client['location'] ?? 'N/A'); ?>';
    const clientStatus = '<?php echo addslashes($client['status'] ?? 'Active'); ?>';
    const clientCreatedAt = '<?php echo isset($client['created_at']) ? date('F d, Y', strtotime($client['created_at'])) : ''; ?>';
    const clientNote = '<?php echo isset($client['note']) ? addslashes(substr($client['note'], 0, 200)) : ''; ?>';

    // Toast notification function
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'info'} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        
        const container = document.createElement('div');
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        container.appendChild(toast);
        document.body.appendChild(container);
        
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        toast.addEventListener('hidden.bs.toast', function () {
            container.remove();
        });
    }

    // Generate shareable link function
    function generateShareLink() {
        return shareLink;
    }

    // Share client via WhatsApp
    function shareClientViaWhatsApp() {
        try {
            // Format location for directions
            let locationText = '';
            let directionsUrl = '';
            
            if (clientData.url) {
                if (clientData.url.includes('@')) {
                    // Extract coordinates from Google Maps URL
                    const coordMatch = clientData.url.match(/@(-?\d+\.\d+),(-?\d+\.\d+)/);
                    if (coordMatch) {
                        directionsUrl = `https://www.google.com/maps/dir/?api=1&destination=${coordMatch[1]},${coordMatch[2]}`;
                    }
                } else if (clientData.location) {
                    directionsUrl = `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(clientData.location)}`;
                }
            } else if (clientData.location) {
                directionsUrl = `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(clientData.location)}`;
            }
            
            if (clientData.location) {
                locationText = `*Location:* ${clientData.location}\n`;
                if (directionsUrl) {
                    locationText += `*Directions:* ${directionsUrl}\n`;
                }
            }
            
            // Create rich WhatsApp message with document preview
            const shareText = `*CLIENT PROFILE DOCUMENT*\n\n` +
                            `*Name:* ${clientData.name}\n` +
                            `*Email:* ${clientData.email || 'Not provided'}\n` +
                            `*Phone:* ${clientData.phone || 'Not provided'}\n` +
                            (clientData.address ? `*Address:* ${clientData.address}\n` : '') +
                            locationText +
                            `*Status:* ${clientData.status || 'Active'}\n` +
                            `*Member Since:* ${new Date(clientData.created_at).toLocaleDateString()}\n\n` +
                            (clientData.note ? `*Notes:*\n${clientData.note.substring(0, 150)}...\n\n` : '');
            
            const encodedText = encodeURIComponent(shareText);
            window.open(`https://wa.me/?text=${encodedText}`, '_blank', 'noopener,noreferrer');
            
            // Show toast message
            showToast('Opening WhatsApp with document preview...', 'info');
            
        } catch (error) {
            console.error('WhatsApp share error:', error);
            showToast('Error sharing via WhatsApp', 'error');
            
            // Fallback to simple share
            const fallbackText = `Client Profile: ${clientData.name}\n\nView complete details: ${shareLink}`;
            const encodedText = encodeURIComponent(fallbackText);
            window.open(`https://wa.me/?text=${encodedText}`, '_blank', 'noopener,noreferrer');
        }
    }

    // Copy shareable link to clipboard
    function copyShareableLink() {
        navigator.clipboard.writeText(shareLink)
            .then(() => {
                showToast('Share link copied to clipboard!', 'success');
            })
            .catch(err => {
                console.error('Failed to copy:', err);
                // Fallback method
                const input = document.createElement('input');
                input.value = shareLink;
                document.body.appendChild(input);
                input.select();
                input.setSelectionRange(0, 99999);
                document.execCommand('copy');
                document.body.removeChild(input);
                showToast('Share link copied to clipboard!', 'success');
            });
    }

    // Share client via Email
    function shareClientViaEmail() {
        try {
            // Format location for email
            let locationSection = '';
            if (clientData.location) {
                locationSection = `Location: ${clientData.location}\n`;
                if (clientData.url) {
                    locationSection += `Map Link: ${clientData.url}\n`;
                }
            }
            
            // Create email content
            const subject = encodeURIComponent(`Client Profile: ${clientData.name}`);
            const body = encodeURIComponent(
                `CLIENT PROFILE DOCUMENT\n\n` +
                `Name: ${clientData.name}\n` +
                `Email: ${clientData.email || 'Not provided'}\n` +
                `Phone: ${clientData.phone || 'Not provided'}\n` +
                `Address: ${clientData.address || 'Not provided'}\n` +
                locationSection +
                `Status: ${clientData.status || 'Active'}\n` +
                `Member Since: ${new Date(clientData.created_at).toLocaleDateString()}\n\n` +
                (clientData.note ? `Notes:\n${clientData.note}\n\n` : '')
            );
            
            window.location.href = `mailto:?subject=${subject}&body=${body}`;
            
        } catch (error) {
            console.error('Email share error:', error);
            showToast('Error preparing email', 'error');
        }
    }
    // Share as a document card (rich preview)
    function shareAsDocumentCard() {
        try {
            // Create a more professional share message
            const shareText = `*Client Profile Document*\n\n` +
                            `• Name: ${clientData.name}\n` +
                            `• Contact: ${clientData.phone || clientData.email || 'Available in document'}\n` +
                            `• Location: ${clientData.location || 'Included in document'}\n\n` +
                            `🔗 *Open Document:* ${shareLink}\n\n`;
            
            const encodedText = encodeURIComponent(shareText);
            
            // Ask user which platform to share on
            if (confirm('Share this client profile as a document?\n\nWhatsApp: Rich preview with Open Graph\nOther: Copy link to share anywhere')) {
                window.open(`https://wa.me/?text=${encodedText}`, '_blank', 'noopener,noreferrer');
            } else {
                copyShareableLink();
            }
            
        } catch (error) {
            console.error('Document share error:', error);
            showToast('Error sharing document', 'error');
        }
    }
    // Share via multiple platforms
    function shareClientOnPlatform(platform) {
        const text = encodeURIComponent(`Check out this client: ${clientName}\n\n${shareLink}`);
        
        let url;
        switch(platform) {
            case 'facebook':
                url = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareLink)}`;
                break;
            case 'twitter':
                url = `https://twitter.com/intent/tweet?text=${text}`;
                break;
            case 'linkedin':
                url = `https://www.linkedin.com/shareArticle?mini=true&url=${encodeURIComponent(shareLink)}&title=${encodeURIComponent('Client Details: ' + clientName)}`;
                break;
            case 'telegram':
                url = `https://t.me/share/url?url=${encodeURIComponent(shareLink)}&text=${text}`;
                break;
            default:
                return;
        }
        
        window.open(url, '_blank', 'width=600,height=400,noopener,noreferrer');
    }

    // Open share modal with options
    function openShareModal() {
        const modal = new bootstrap.Modal(document.getElementById('shareModal'));
        const input = document.getElementById('shareLinkInput');
        if (input) {
            input.value = shareLink;
        }
        modal.show();
    }

    // Download PDF function
    function downloadClientDetails() {
        showToast('Generating PDF... Please wait', 'info');
        
        const timestamp = new Date().getTime();
        const downloadUrl = `download_client_pdf.php?id=${clientId}&_=${timestamp}`;
        
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.target = '_blank';
        link.download = `client_${clientData.name.replace(/[^a-z0-9]/gi, '_')}_details.pdf`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        setTimeout(() => {
            showToast('PDF download started! Check your downloads folder.', 'success');
        }, 1000);
    }


    // File preview function
    // Enhanced preview function
function previewFile(filePath, fileName, fileExt) {
    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
    const content = document.getElementById('previewContent');
    const title = document.getElementById('previewTitle');
    
    // Show loading
    content.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading preview...</span>
            </div>
            <p class="mt-2 text-muted">Loading preview...</p>
        </div>
    `;
    
    title.textContent = 'Preview: ' + fileName;
    
    // Check if it's an image
    const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'].includes(fileExt.toLowerCase());
    const isPDF = fileExt.toLowerCase() === 'pdf';
    
    if (isImage) {
        // Preload image to check if it exists
        const img = new Image();
        img.onload = function() {
            content.innerHTML = `
                <div class="text-center">
                    <img src="${filePath}" 
                         class="img-fluid rounded shadow" 
                         alt="${fileName}" 
                         style="max-height: 70vh; max-width: 100%;">
                    <div class="mt-3">
                        <a href="${filePath}" 
                           class="btn btn-primary" 
                           download="${fileName}">
                            <i class="bi bi-download me-2"></i>Download Image
                        </a>
                        <button class="btn btn-outline-secondary ms-2" onclick="window.open('${filePath}', '_blank')">
                            <i class="bi bi-box-arrow-up-right me-2"></i>Open in New Tab
                        </button>
                    </div>
                </div>
            `;
        };
        
        img.onerror = function() {
            content.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Unable to load image preview. The file may not exist or may not be accessible.
                    <div class="mt-3">
                        <a href="${filePath}" 
                           class="btn btn-primary" 
                           download="${fileName}">
                            <i class="bi bi-download me-2"></i>Try Downloading File
                        </a>
                    </div>
                </div>
            `;
        };
        
        img.src = filePath;
    } 
    else if (isPDF) {
        content.innerHTML = `
            <div class="alert alert-info">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <i class="bi bi-file-pdf text-danger fs-4"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="alert-heading">PDF Document</h5>
                        <p>This is a PDF file. You can download it or open in a new tab.</p>
                        <hr>
                        <div class="d-flex gap-2">
                            <a href="${filePath}" 
                               class="btn btn-danger" 
                               download="${fileName}">
                                <i class="bi bi-download me-2"></i>Download PDF
                            </a>
                            <button class="btn btn-outline-danger" onclick="window.open('${filePath}', '_blank')">
                                <i class="bi bi-box-arrow-up-right me-2"></i>Open in New Tab
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    else {
        // For other file types
        content.innerHTML = `
            <div class="alert alert-warning">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <i class="bi bi-file-earmark fs-4"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="alert-heading">${fileName}</h5>
                        <p>Preview not available for .${fileExt} files. You can download the file.</p>
                        <hr>
                        <div class="d-flex gap-2">
                            <a href="${filePath}" 
                               class="btn btn-primary" 
                               download="${fileName}">
                                <i class="bi bi-download me-2"></i>Download File
                            </a>
                            <button class="btn btn-outline-primary" onclick="window.open('${filePath}', '_blank')">
                                <i class="bi bi-box-arrow-up-right me-2"></i>Open in New Tab
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    modal.show();
}

// Add event listeners for preview buttons
document.addEventListener('DOMContentLoaded', function() {
    // Add click handlers to preview buttons
    document.querySelectorAll('.preview-btn').forEach(button => {
        button.addEventListener('click', function() {
            const filePath = this.getAttribute('data-file-path');
            const fileName = this.getAttribute('data-file-name');
            const fileExt = this.getAttribute('data-file-ext');
            previewFile(filePath, fileName, fileExt);
        });
    });
    
    // Add direct download click handlers with feedback
    document.querySelectorAll('a[download]').forEach(link => {
        link.addEventListener('click', function(e) {
            const fileName = this.getAttribute('download');
            console.log('Downloading:', fileName);
            // You can add toast notification here if needed
        });
    });
});
    </script>
<body>
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
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <!-- Client Profile Header -->
                        <div class="card mb-4 overflow-hidden">
                            <div class="client-profile-header text-center">
                                <div class="row align-items-center">
                                    <div class="col-md-2">
                                        <div class="position-relative d-inline-block">
                                            <?php if (!empty($client['picture'])): ?>
                                                <img src="<?php echo sanitize($client['picture']); ?>" 
                                                     alt="<?php echo sanitize($client['name']); ?>" 
                                                     class="profile-avatar">
                                            <?php else: ?>
                                                <div class="profile-avatar bg-white d-flex align-items-center justify-content-center">
                                                    <i class="bi bi-person-fill text-primary" style="font-size: 3rem;"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-7 text-md-start">
                                        <h1 class="display-6 fw-bold mb-2 text-white"><?php echo sanitize($client['name']); ?></h1>
                                        <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-md-start mb-3">
                                            <span class="badge-status status-<?php echo $client['status'] ?? 'active'; ?>">
                                                <i class="bi bi-check-circle me-1"></i>
                                                <?php echo ucfirst($client['status'] ?? 'active'); ?>
                                            </span>
                                            <span class="text-light">
                                                <i class="bi bi-calendar me-1"></i>
                                                Member since <?php echo date('M d, Y', strtotime($client['created_at'])); ?>
                                            </span>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-start">
                                            <?php if (!empty($client['phone'])): ?>
                                                <a href="tel:<?php echo sanitize($client['phone']); ?>" class="contact-chip">
                                                    <i class="bi bi-telephone me-2"></i> Call
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Main Content -->
                            <div class="card-body">
                                <div class="row">
                                    <!-- Left Column - Basic Information -->
                                    <div class="col-lg-8">
                                        <!-- Contact Information -->
                                        <div class="info-card">
                                            <div class="card-header d-flex align-items-center">
                                                <i class="bi bi-info-circle me-2"></i>
                                                Contact Information
                                            </div>
                                            <div class="card-body">
                                                <div class="detail-item">
                                                    <div class="detail-label"><i class="bi bi-person me-2"></i>Full Name</div>
                                                    <div class="detail-value"><?php echo sanitize($client['name']); ?></div>
                                                </div>
                                                <?php if (!empty($client['email'])): ?>
                                                <div class="detail-item">
                                                    <div class="detail-label"><i class="bi bi-envelope me-2"></i>Email Address</div>
                                                    <div class="detail-value">
                                                        <a href="mailto:<?php echo sanitize($client['email']); ?>" class="text-decoration-none">
                                                            <?php echo sanitize($client['email']); ?>
                                                        </a>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                                <?php if (!empty($client['phone'])): ?>
                                                <div class="detail-item">
                                                    <div class="detail-label"><i class="bi bi-telephone me-2"></i>Phone Number</div>
                                                    <div class="detail-value">
                                                        <a href="tel:<?php echo sanitize($client['phone']); ?>" class="text-decoration-none">
                                                            <?php echo sanitize($client['phone']); ?>
                                                        </a>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                                <?php if (!empty($client['address'])): ?>
                                                <div class="detail-item">
                                                    <div class="detail-label"><i class="bi bi-geo-alt me-2"></i>Address</div>
                                                    <div class="detail-value"><?php echo nl2br(sanitize($client['address'])); ?></div>
                                                </div>
                                                <?php endif; ?>
                                                <?php if (!empty($client['location'])): ?>
                                                <div class="detail-item">
                                                    <div class="detail-label"><i class="bi bi-pin-map me-2"></i>Location</div>
                                                    <div class="detail-value"><?php echo sanitize($client['location']); ?></div>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Notes & Additional Information -->
                                        <?php if (!empty($client['note'])): ?>
                                        <div class="info-card">
                                            <div class="card-header d-flex align-items-center">
                                                <i class="bi bi-journal-text me-2"></i>
                                                Notes & Remarks
                                            </div>
                                            <div class="card-body">
                                                <div class="p-3 bg-light rounded">
                                                    <?php echo nl2br(sanitize($client['note'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <!-- Location Map -->
                                        <?php if (!empty($client['url'])): ?>
                                        <div class="info-card">
                                            <div class="card-header d-flex align-items-center">
                                                <i class="bi bi-map me-2"></i>
                                                Location Map
                                            </div>
                                            <div class="card-body">
                                                <?php 
                                                $location_url = trim($client['url']);
                                                
                                                if (strpos($location_url, 'maps/embed') !== false): ?>
                                                    <div class="map-container mt-3">
                                                        <iframe 
                                                            src="<?php echo sanitize($location_url); ?>"
                                                            width="100%" 
                                                            height="400" 
                                                            style="border:0;" 
                                                            allowfullscreen="" 
                                                            loading="lazy"
                                                            referrerpolicy="no-referrer-when-downgrade">
                                                        </iframe>
                                                    </div>
                                                <?php elseif (strpos($location_url, 'goo.gl') !== false || 
                                                            strpos($location_url, 'google.com/maps') !== false || 
                                                            strpos($location_url, 'maps.app') !== false): ?>
                                                    <div class="alert alert-info">
                                                        <div class="d-flex align-items-start">
                                                            <i class="bi bi-info-circle me-2 mt-1"></i>
                                                            <div>
                                                                <p class="mb-2">This is a Google Maps share link. To display the map here, please:</p>
                                                                <ol class="mb-2">
                                                                    <li>Open the link below</li>
                                                                    <li>Click "Share" in Google Maps</li>
                                                                    <li>Select "Embed a map"</li>
                                                                    <li>Copy the embed URL (starts with <code>https://www.google.com/maps/embed</code>)</li>
                                                                    <li>Edit the client and paste the embed URL in the Location URL field</li>
                                                                </ol>
                                                                <a href="<?php echo sanitize($location_url); ?>" 
                                                                target="_blank" 
                                                                class="btn btn-primary btn-sm">
                                                                    <i class="bi bi-arrow-up-right-square me-1"></i>
                                                                    Open in Google Maps
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <p>
                                                        <a href="<?php echo sanitize($location_url); ?>" target="_blank" class="btn btn-outline-primary">
                                                            <i class="bi bi-geo-alt me-1"></i>
                                                            Open Location Link
                                                        </a>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        
                                    </div>

                                    <!-- Right Column - Attachments & Files -->
                                    <div class="col-lg-4">
                                        <!-- Files & Attachments -->
                                        <div class="info-card">
                                            <div class="card-header d-flex align-items-center justify-content-between">
                                                <div>
                                                    <i class="bi bi-paperclip me-2"></i>
                                                    Files & Attachments
                                                </div>
                                                <span class="badge bg-primary">
                                                    <?php echo count($attachments); ?> files
                                                </span>
                                            </div>
                                            <div class="card-body">
                                                <?php if (!empty($attachments)): ?>
                                                    <div class="row g-3">
                                                        <?php foreach ($attachments as $attachment): ?>
                                                            <?php
                                                            $file_ext = pathinfo($attachment['file_path'], PATHINFO_EXTENSION);
                                                            $icon_class = getFileIcon($file_ext);
                                                            $secure_path = $attachment['secure_path'] ?? $attachment['file_path'];
                                                            
                                                            // Get file name
                                                            $file_name = isset($attachment['original_name']) ? $attachment['original_name'] : basename($secure_path);
                                                            
                                                            // Get file size
                                                            $file_size = isset($attachment['file_size']) ? $attachment['file_size'] : (file_exists($secure_path) ? filesize($secure_path) : 0);
                                                            
                                                            // Convert absolute path to web-accessible URL
                                                            // Assuming files are in 'uploads' folder relative to the script
                                                            $web_path = '';
                                                            $is_downloadable = false;
                                                            
                                                            if (file_exists($secure_path)) {
                                                                // Try to create web-accessible path
                                                                $base_dir = realpath(__DIR__ . '/./');
                                                                $file_rel_path = str_replace($base_dir, '', $secure_path);
                                                                $file_rel_path = ltrim($file_rel_path, '/\\');
                                                                
                                                                // Create URL path
                                                                $web_path = './' . $file_rel_path;
                                                                
                                                                // Clean up path
                                                                $web_path = str_replace('\\', '/', $web_path);
                                                                $web_path = preg_replace('/\/+/', '/', $web_path);
                                                                
                                                                $is_downloadable = true;
                                                            }
                                                            
                                                            // Check if it's an image for preview
                                                            $is_image = in_array(strtolower($file_ext), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
                                                            $can_preview = $is_image && file_exists($secure_path);
                                                            ?>
                                                            <div class="col-12">
                                                                <div class="attachment-card p-3">
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="flex-shrink-0">
                                                                            <i class="bi <?php echo $icon_class; ?> file-icon"></i>
                                                                        </div>
                                                                        <div class="flex-grow-1 ms-3">
                                                                            <h6 class="mb-1 text-truncate" title="<?php echo sanitize($file_name); ?>">
                                                                                <?php echo sanitize($file_name); ?>
                                                                            </h6>
                                                                            <small class="text-muted d-block">
                                                                                <span class="badge bg-secondary"><?php echo strtoupper($file_ext); ?></span>
                                                                                <?php echo formatFileSize($file_size); ?>
                                                                                <?php if (isset($attachment['created_at'])): ?>
                                                                                • Uploaded on <?php echo date('M d, Y', strtotime($attachment['created_at'])); ?>
                                                                                <?php endif; ?>
                                                                            </small>
                                                                            <div class="mt-2">
                                                                                <?php if ($is_downloadable): ?>
                                                                                    <a href="<?php echo htmlspecialchars($web_path); ?>" 
                                                                                    class="btn btn-sm btn-outline-primary me-2"
                                                                                    target="_blank"
                                                                                    download="<?php echo htmlspecialchars($file_name); ?>">
                                                                                        <i class="bi bi-download me-1"></i> Download
                                                                                    </a>
                                                                                <?php else: ?>
                                                                                    <button class="btn btn-sm btn-outline-secondary me-2" disabled>
                                                                                        <i class="bi bi-download me-1"></i> File Not Found
                                                                                    </button>
                                                                                <?php endif; ?>
                                                                                
                                                                                <?php if ($can_preview && $is_downloadable): ?>
                                                                                    <button type="button" 
                                                                                            class="btn btn-sm btn-outline-secondary preview-btn"
                                                                                            data-file-path="<?php echo htmlspecialchars($web_path); ?>"
                                                                                            data-file-name="<?php echo htmlspecialchars($file_name); ?>"
                                                                                            data-file-ext="<?php echo htmlspecialchars($file_ext); ?>">
                                                                                        <i class="bi bi-eye me-1"></i> Preview
                                                                                    </button>
                                                                                <?php elseif ($is_image && !$is_downloadable): ?>
                                                                                    <button class="btn btn-sm btn-outline-secondary" disabled>
                                                                                        <i class="bi bi-eye me-1"></i> Preview Not Available
                                                                                    </button>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                            <?php if (!$is_downloadable): ?>
                                                                                <div class="mt-1">
                                                                                    <small class="text-danger">
                                                                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                                                                        File path: <?php echo htmlspecialchars($secure_path); ?>
                                                                                    </small>
                                                                                </div>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="text-center py-4">
                                                        <i class="bi bi-inbox fs-1 text-muted mb-3"></i>
                                                        <p class="text-muted mb-0">No attachments found</p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Client Statistics -->
                                        <div class="info-card">
                                            <div class="card-header d-flex align-items-center">
                                                <i class="bi bi-graph-up me-2"></i>
                                                Client Statistics
                                            </div>
                                            <div class="card-body">
                                                <div class="list-group list-group-flush">
                                                    <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-2">
                                                        <span>Account Status</span>
                                                        <span class="badge-status status-<?php echo $client['status'] ?? 'active'; ?>">
                                                            <?php echo ucfirst($client['status'] ?? 'active'); ?>
                                                        </span>
                                                    </div>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-2">
                                                        <span>Member Since</span>
                                                        <span><?php echo date('M d, Y', strtotime($client['created_at'])); ?></span>
                                                    </div>
                                                    <?php if (!empty($client['updated_at'])): ?>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-2">
                                                        <span>Last Updated</span>
                                                        <span><?php echo date('M d, Y', strtotime($client['updated_at'])); ?></span>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Share Modal -->
                        <div class="modal fade" id="shareModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            <i class="bi bi-share me-2"></i>
                                            Share Client Details
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-4">
                                            <label class="form-label">Shareable Link:</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="shareLinkInput" 
                                                       value="<?php echo $share_link; ?>" readonly>
                                                <button class="btn btn-outline-secondary" type="button" onclick="copyShareableLink()">
                                                    <i class="bi bi-clipboard"></i>
                                                </button>
                                            </div>
                                            <small class="text-muted mt-1 d-block">
                                                Anyone with this link can view the client details
                                            </small>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label class="form-label">Share via:</label>
                                            <div class="social-share-buttons">
                                                <button class="share-btn btn-whatsapp" onclick="shareClientViaWhatsApp()">
                                                    <i class="bi bi-whatsapp me-2"></i> WhatsApp
                                                </button>
                                                <!-- <button class="share-btn btn-email" onclick="shareClientViaEmail()">
                                                    <i class="bi bi-envelope me-2"></i> Email
                                                </button>
                                                <button class="share-btn btn-facebook" onclick="shareClientOnPlatform('facebook')">
                                                    <i class="bi bi-facebook me-2"></i> Facebook
                                                </button>
                                                <button class="share-btn btn-twitter" onclick="shareClientOnPlatform('twitter')">
                                                    <i class="bi bi-twitter me-2"></i> Twitter
                                                </button>
                                                <button class="share-btn btn-linkedin" onclick="shareClientOnPlatform('linkedin')">
                                                    <i class="bi bi-linkedin me-2"></i> LinkedIn
                                                </button> -->
                                            </div>
                                        </div>
                                        
                                        <div class="alert alert-info">
                                            <div class="d-flex align-items-start">
                                                <i class="bi bi-info-circle me-2 mt-1"></i>
                                                <div>
                                                    <strong>Tip:</strong> The share link includes all client details in a 
                                                    nicely formatted page that can be viewed on any device.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Floating Action Buttons -->
                    <div class="action-buttons">
                        <a href="edit_client.php?id=<?php echo $client_id; ?>" 
                           class="action-btn btn-edit" 
                           title="Edit Client">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <button class="action-btn btn-share" onclick="openShareModal()" title="Share Client">
                            <i class="bi bi-share"></i>
                        </button>
                        <button class="action-btn btn-download" onclick="downloadClientDetails()" title="Download PDF">
                            <i class="bi bi-download"></i>
                        </button>
                    </div>

                    <!-- Back Button -->
                    <div class="container-xxl">
                        <a href="client-list.php" class="btn btn-outline-secondary mb-4">
                            <i class="bi bi-arrow-left me-2"></i> Back to Clients
                        </a>
                    </div>
                    
                    <?php include_once('./include/footer.php'); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="previewTitle">File Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="previewContent" class="text-center">
                        <!-- Preview content will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php include_once('./include/script.php'); ?>
    
</body>
</html>