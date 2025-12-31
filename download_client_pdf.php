<?php
// download_client_pdf.php
require_once './functions/auth.php';
require_once './config/database.php';
require_once './functions/client_crud.php';

requireLogin();

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

// Get client attachments
$attachments_result = $clientCrud->getAttachmentsByUserId($client_id);
$attachments = [];

if ($attachments_result) {
    if (is_object($attachments_result) && method_exists($attachments_result, 'fetch_assoc')) {
        if ($attachments_result->num_rows > 0) {
            while ($attachment = $attachments_result->fetch_assoc()) {
                $attachments[] = $attachment;
            }
        }
    } elseif (is_array($attachments_result)) {
        $attachments = $attachments_result;
    }
}

// Function to get base64 image data
function getBase64Image($file_path) {
    if (file_exists($file_path)) {
        $image_info = @getimagesize($file_path);
        if ($image_info === false) {
            return null;
        }
        
        $mime_type = $image_info['mime'];
        $image_data = file_get_contents($file_path);
        
        if ($image_data === false) {
            return null;
        }
        
        return "data:{$mime_type};base64," . base64_encode($image_data);
    }
    return null;
}

// Get client picture
$client_picture_data = null;
if (!empty($client['picture'])) {
    $client_picture_data = getBase64Image($client['picture']);
}

// Separate image and file attachments
$image_attachments = [];
$file_attachments = [];

foreach ($attachments as $attachment) {
    if (!isset($attachment['file_path'])) continue;
    
    $file_path = $attachment['file_path'];
    $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'])) {
        $image_data = getBase64Image($file_path);
        if ($image_data) {
            $attachment['base64_data'] = $image_data;
            $image_attachments[] = $attachment;
        }
    } else {
        $file_attachments[] = $attachment;
    }
}

// Generate filename
$filename = 'client_' . preg_replace('/[^a-z0-9]/i', '_', $client['name']) . '_' . date('Y-m-d');

// Set correct headers for HTML file download
header('Content-Type: text/html');
header('Content-Disposition: attachment; filename="' . $filename . '.html"');
header('Content-Transfer-Encoding: binary');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Get base URL for CSS resources
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Profile - <?php echo htmlspecialchars($client['name']); ?></title>
    <!-- Embed Bootstrap Icons directly as base64 -->
    <link rel="stylesheet" href="data:text/css;base64,<?php echo base64_encode('
        /* Bootstrap Icons - Embedded Version */
        @font-face {
            font-display: block;
            font-family: "bootstrap-icons";
            src: url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/fonts/bootstrap-icons.woff2") format("woff2"),
                 url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/fonts/bootstrap-icons.woff") format("woff");
        }
        
        .bi::before,
        [class^="bi-"]::before,
        [class*=" bi-"]::before {
            display: inline-block;
            font-family: bootstrap-icons !important;
            font-style: normal;
            font-weight: normal !important;
            font-variant: normal;
            text-transform: none;
            line-height: 1;
            vertical-align: -.125em;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        .bi-person::before { content: "\f4e7"; }
        .bi-person-fill::before { content: "\f4da"; }
        .bi-telephone::before { content: "\f5e9"; }
        .bi-envelope::before { content: "\f32f"; }
        .bi-house::before { content: "\f46a"; }
        .bi-geo-alt::before { content: "\f3e8"; }
        .bi-calendar::before { content: "\f1e6"; }
        .bi-calendar-check::before { content: "\f1e8"; }
        .bi-file-text::before { content: "\f359"; }
        .bi-circle-fill::before { content: "\f287"; }
        .bi-arrow-clockwise::before { content: "\f131"; }
        .bi-phone::before { content: "\f4f9"; }
        .bi-images::before { content: "\f42f"; }
        .bi-image::before { content: "\f42e"; }
        .bi-paperclip::before { content: "\f4ca"; }
        .bi-journal-text::before { content: "\f456"; }
        .bi-map::before { content: "\f57e"; }
        .bi-geo-alt-fill::before { content: "\f3e7"; }
        .bi-signpost-split::before { content: "\f5a6"; }
        .bi-info-circle::before { content: "\f431"; }
        .bi-person-circle::before { content: "\f4de"; }
        .bi-printer::before { content: "\f52a"; }
        .bi-check-circle::before { content: "\f26a"; }
        .bi-file-pdf::before { content: "\f640"; }
        .bi-file-word::before { content: "\f697"; }
        .bi-file-excel::before { content: "\f64e"; }
        .bi-file-image::before { content: "\f641"; }
        .bi-file-zip::before { content: "\f697"; }
        .bi-file-text::before { content: "\f359"; }
        .bi-file-earmark::before { content: "\f347"; }
    '); ?>">
    <style>
        /* EMBED ALL CSS STYLES DIRECTLY - NO EXTERNAL DEPENDENCIES */
        /* Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #2c3e50;
            background: white;
            min-height: 100vh;
            padding: 30px;
        }
        
        .document-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            position: relative;
        }
        
        /* Header with Gradient */
        .document-header {
            background: linear-gradient(270deg, rgb(242 136 150) 0%, #783c4a 100%);
            color: white;
            padding: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .document-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: rgba(255, 255, 255, 0.1);
            transform: rotate(30deg);
        }
        
        .client-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid rgba(255, 255, 255, 0.3);
            object-fit: cover;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .avatar-placeholder {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 40px;
        }
        
        .client-name {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .client-title {
            font-size: 18px;
            opacity: 0.9;
            margin-bottom: 15px;
        }
        
        .client-meta {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .meta-item {
            background: rgba(255, 255, 255, 0.15);
            padding: 8px 16px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        /* Content Sections */
        .document-content {
            padding: 40px;
        }
        
        .section {
            margin-bottom: 40px;
            position: relative;
        }
        
        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f2f5;
        }
        
        .section-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: linear-gradient(270deg, rgb(242 136 150) 0%, #783c4a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: white;
            font-size: 20px;
        }
        
        .section-title {
            font-size: 22px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        /* Info Cards */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .info-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }
        
        .info-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6c757d;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .info-value {
            font-size: 16px;
            color: #2c3e50;
            font-weight: 500;
        }
        
        /* Contact Information */
        .contact-list {
            display: grid;
            gap: 15px;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .contact-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(270deg, rgb(242 136 150) 0%, #783c4a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: white;
            font-size: 18px;
        }
        
        .contact-details {
            flex: 1;
        }
        
        .contact-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .contact-subtitle {
            font-size: 14px;
            color: #6c757d;
        }
        
        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            gap: 8px;
        }
        
        .status-active {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: #155724;
        }
        
        .status-inactive {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: #721c24;
        }
        
        .status-pending {
            background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
            color: #856404;
        }
        
        /* Notes Section */
        .notes-container {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 25px;
            border-left: 5px solid #667eea;
            position: relative;
        }
        
        .notes-container::before {
            content: '"';
            position: absolute;
            top: 15px;
            left: 15px;
            font-size: 80px;
            color: #667eea;
            opacity: 0.1;
            font-family: Georgia, serif;
            line-height: 1;
        }
        
        .notes-content {
            font-size: 15px;
            line-height: 1.8;
            color: #495057;
            position: relative;
            z-index: 1;
        }
        
        /* Location Section */
        .location-card {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border-radius: 15px;
            padding: 30px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .location-content {
            position: relative;
            z-index: 1;
            text-align: center;
        }
        
        .location-icon {
            font-size: 50px;
            margin-bottom: 20px;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .location-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .location-desc {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 25px;
        }
        
        .directions-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: white;
            color: #4facfe;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        /* Gallery Styles */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .gallery-item {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .gallery-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }
        
        /* Broken Image Placeholder */
        .broken-image {
            width: 100%;
            height: 200px;
            background: #f8f9fa;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            border-radius: 8px;
        }
        
        /* Footer */
        .document-footer {
            background: #f8f9fa;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        
        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .footer-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            color: #667eea;
            font-size: 18px;
        }
        
        .footer-meta {
            color: #6c757d;
            font-size: 14px;
        }
        
        /* Print button */
        .print-btn {
            background: linear-gradient(270deg, rgb(242 136 150) 0%, #783c4a 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            margin-top: 20px;
        }
        
        /* Print Styles */
        @media print {
            body {
                background: none;
                padding: 0;
            }
            
            .document-container {
                box-shadow: none;
                border-radius: 0;
            }
            
            .no-print {
                display: none !important;
            }
            
            @page {
                margin: 0.5in;
            }
            
            a {
                color: #000 !important;
                text-decoration: none;
            }
            
            .directions-btn {
                color: #000 !important;
                background: #fff !important;
                border: 1px solid #000 !important;
                box-shadow: none !important;
            }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .document-content {
                padding: 20px;
            }
            
            .client-name {
                font-size: 28px;
            }
            
            .section-title {
                font-size: 20px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .footer-content {
                flex-direction: column;
                text-align: center;
            }
            
            .client-avatar, .avatar-placeholder {
                width: 120px;
                height: 120px;
            }
            
            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
        }
    </style>
</head>
<body>
    <div class="document-container">
        <!-- Header -->
        <div class="document-header">
            <!-- Logo in top right corner of header -->
            <div style="position: absolute; top: 20px; left: 20px;">
                <?php
                $logo_path = './assets/img/logo/dream-fest-icon.png';
                $logo_data = null;
                
                if (file_exists($logo_path)) {
                    $logo_info = @getimagesize($logo_path);
                    if ($logo_info !== false) {
                        $logo_content = file_get_contents($logo_path);
                        if ($logo_content !== false) {
                            $logo_data = 'data:' . $logo_info['mime'] . ';base64,' . base64_encode($logo_content);
                        }
                    }
                }
                
                if ($logo_data): 
                ?>
                    <img src="<?php echo $logo_data; ?>" 
                        alt="Dream Fest Logo" 
                        width="80" 
                        height="80" 
                        style="border-radius: 10px; border: 3px solid rgba(255,255,255,0.3); box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
                <?php else: ?>
                    <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); 
                                border-radius: 10px; border: 3px solid rgba(255,255,255,0.3);
                                display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 18px;">
                        DF
                    </div>
                <?php endif; ?>
            </div>
            <div class="client-avatar-container">
                <?php if ($client_picture_data): ?>
                    <img src="<?php echo $client_picture_data; ?>" 
                         alt="<?php echo htmlspecialchars($client['name']); ?>" 
                         class="client-avatar">
                <?php else: ?>
                    <div class="avatar-placeholder">
                        <i class="bi bi-person-fill"></i>
                    </div>
                <?php endif; ?>
            </div>
            
            <h1 class="client-name"><?php echo htmlspecialchars($client['name']); ?></h1>
            <p class="client-title">Client Profile Report</p>
            
            <div class="client-meta">
                <div class="meta-item">
                    <i class="bi bi-calendar"></i>
                    <span>Generated: <?php echo date('M d, Y'); ?></span>
                </div>
                <div class="meta-item">
                    <i class="bi bi-file-text"></i>
                    <span>ID: CLIENT-<?php echo str_pad($client_id, 6, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="meta-item status-badge status-<?php echo htmlspecialchars($client['status'] ?? 'active'); ?>">
                    <i class="bi bi-circle-fill"></i>
                    <span><?php echo ucfirst(htmlspecialchars($client['status'] ?? 'active')); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Content -->
        <div class="document-content">
            <!-- Personal Information -->
            <div class="section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="bi bi-person"></i>
                    </div>
                    <h2 class="section-title">Personal Information</h2>
                </div>
                
                <div class="info-grid">
                    <div class="info-card">
                        <div class="info-label">Full Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($client['name']); ?></div>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            <span class="status-badge status-<?php echo htmlspecialchars($client['status'] ?? 'active'); ?>">
                                <i class="bi bi-circle-fill"></i>
                                <?php echo ucfirst(htmlspecialchars($client['status'] ?? 'active')); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-label">Member Since</div>
                        <div class="info-value">
                            <i class="bi bi-calendar me-2"></i>
                            <?php echo date('F d, Y', strtotime($client['created_at'])); ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($client['updated_at'])): ?>
                    <div class="info-card">
                        <div class="info-label">Last Updated</div>
                        <div class="info-value">
                            <i class="bi bi-arrow-clockwise me-2"></i>
                            <?php echo date('F d, Y', strtotime($client['updated_at'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Contact Information -->
            <div class="section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="bi bi-telephone"></i>
                    </div>
                    <h2 class="section-title">Contact Information</h2>
                </div>
                
                <div class="contact-list">
                    <?php if (!empty($client['email'])): ?>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="bi bi-envelope"></i>
                        </div>
                        <div class="contact-details">
                            <div class="contact-title">Email Address</div>
                            <div class="contact-subtitle"><?php echo htmlspecialchars($client['email']); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($client['phone'])): ?>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="bi bi-phone"></i>
                        </div>
                        <div class="contact-details">
                            <div class="contact-title">Phone Number</div>
                            <div class="contact-subtitle"><?php echo htmlspecialchars($client['phone']); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($client['address'])): ?>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="bi bi-house"></i>
                        </div>
                        <div class="contact-details">
                            <div class="contact-title">Address</div>
                            <div class="contact-subtitle"><?php echo nl2br(htmlspecialchars($client['address'])); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($client['location'])): ?>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        <div class="contact-details">
                            <div class="contact-title">Location</div>
                            <div class="contact-subtitle"><?php echo htmlspecialchars($client['location']); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Image Gallery -->
            <?php if (!empty($image_attachments)): ?>
            <div class="section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="bi bi-images"></i>
                    </div>
                    <h2 class="section-title">Client Images (<?php echo count($image_attachments); ?>)</h2>
                </div>
                
                <p style="color: #6c757d; margin-bottom: 20px;">
                    <i class="bi bi-info-circle me-2"></i>
                    Gallery of images uploaded by/for the client
                </p>
                
                <div class="gallery-grid">
                    <?php foreach ($image_attachments as $attachment): ?>
                    <div class="gallery-item">
                        <?php if (!empty($attachment['base64_data'])): ?>
                            <img src="<?php echo $attachment['base64_data']; ?>" 
                                alt="<?php echo htmlspecialchars($attachment['original_name'] ?? 'Client Image'); ?>"
                                class="gallery-img">
                        <?php else: ?>
                            <div class="broken-image">
                                <i class="bi bi-image"></i>
                                <span>Image not available</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- File Attachments -->
            <?php if (!empty($file_attachments)): ?>
            <div class="section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="bi bi-paperclip"></i>
                    </div>
                    <h2 class="section-title">File Attachments (<?php echo count($file_attachments); ?>)</h2>
                </div>
                
                <div style="display: grid; gap: 10px; margin-top: 20px;">
                    <?php foreach ($file_attachments as $attachment): ?>
                    <div style="display: flex; align-items: center; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #667eea;">
                        <i class="bi bi-file-earmark" style="font-size: 24px; color: #667eea; margin-right: 15px;"></i>
                        <div style="flex: 1;">
                            <div style="font-weight: 600; color: #2c3e50;">
                                <?php echo htmlspecialchars($attachment['original_name'] ?? basename($attachment['file_path'] ?? '')); ?>
                            </div>
                            <div style="font-size: 12px; color: #6c757d; margin-top: 5px;">
                                File: <?php echo basename($attachment['file_path'] ?? ''); ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Notes -->
            <?php if (!empty($client['note'])): ?>
            <div class="section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="bi bi-journal-text"></i>
                    </div>
                    <h2 class="section-title">Notes & Remarks</h2>
                </div>
                
                <div class="notes-container">
                    <div class="notes-content">
                        <?php echo nl2br(htmlspecialchars($client['note'])); ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Location & Directions -->
            <?php if (!empty($client['url'])): ?>
            <div class="section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="bi bi-map"></i>
                    </div>
                    <h2 class="section-title">Location & Directions</h2>
                </div>
                
                <div class="location-card">
                    <div class="location-content">
                        <div class="location-icon">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <h3 class="location-title">Navigate to Client Location</h3>
                        <p class="location-desc">
                            Click below to get directions in Google Maps
                        </p>
                        
                        <?php
                        function getDirectionsUrl($url, $address = '') {
                            if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
                                return "https://www.google.com/maps/dir/?api=1&destination={$matches[1]},{$matches[2]}";
                            }
                            if (!empty($address)) {
                                return "https://www.google.com/maps/dir/?api=1&destination=" . urlencode($address);
                            }
                            return str_replace(['/place/', '/search/'], '/dir/', $url);
                        }
                        
                        $directions_url = getDirectionsUrl($client['url'], $client['location'] ?? '');
                        ?>
                        
                        <a href="<?php echo htmlspecialchars($directions_url); ?>" 
                           target="_blank" 
                           class="directions-btn">
                            <i class="bi bi-signpost-split"></i>
                            Get Directions
                        </a>
                        
                        <?php if (!empty($client['location'])): ?>
                        <div style="margin-top: 20px; padding: 15px; background: rgba(255, 255, 255, 0.2); border-radius: 10px;">
                            <p style="margin: 0; font-size: 14px; opacity: 0.9;">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Address:</strong> <?php echo htmlspecialchars($client['location']); ?>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Footer -->
        <div class="document-footer">
            <div class="footer-content">
                <div class="footer-logo">
                    <i class="bi bi-person-circle"></i>
                    <span>Client Management System</span>
                </div>
                
                <div class="footer-meta">
                    <p>Document generated on <?php echo date('F d, Y, h:i A'); ?></p>
                    <p>© <?php echo date('Y'); ?> All rights reserved</p>
                </div>
            </div>
            
            <!-- Print button -->
            <div class="no-print">
                <button onclick="window.print()" class="print-btn">
                    <i class="bi bi-printer"></i>
                    Print / Save as PDF
                </button>
                <p style="color: #6c757d; font-size: 14px; margin-top: 10px;">
                    <i class="bi bi-info-circle me-2"></i>
                    Use "Save as PDF" option in print dialog for digital copy
                </p>
            </div>
        </div>
    </div>
    
    <script>
        // Simple print functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Add print button functionality
            const printBtn = document.querySelector('.print-btn');
            if (printBtn) {
                printBtn.addEventListener('click', function() {
                    window.print();
                });
            }
        });
    </script>
</body>
</html>
<?php exit(); ?>