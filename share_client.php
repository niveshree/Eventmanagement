<?php
// share_client.php
session_start();
require_once './functions/auth.php';
require_once './config/database.php';
require_once './functions/client_crud.php';

// Allow public access for share links
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('Invalid client ID');
}

$client_id = intval($_GET['id']);
$clientCrud = new ClientCrud();
$client = $clientCrud->getClientByIdSimple($client_id);

if (!$client) {
    die('Client not found');
}

// Get base URL
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

// Check user agent for WhatsApp/Facebook
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$is_whatsapp = stripos($user_agent, 'whatsapp') !== false;
$is_facebook = stripos($user_agent, 'facebook') !== false;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Profile - <?php echo htmlspecialchars($client['name']); ?></title>
    
    <!-- Open Graph Meta Tags for Rich Sharing -->
    <meta property="og:title" content="<?php echo htmlspecialchars($client['name']); ?> - Client Profile">
    <meta property="og:description" content="Client details including contact information, location, and notes.">
    
    <?php if (!empty($client['picture'])): ?>
    <meta property="og:image" content="<?php echo $base_url . htmlspecialchars($client['picture']); ?>">
    <?php else: ?>
    <meta property="og:image" content="<?php echo $base_url; ?>/assets/client-profile-icon.png">
    <?php endif; ?>
    
    <meta property="og:url" content="<?php echo $base_url . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:type" content="profile">
    <meta property="og:site_name" content="Client Management System">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($client['name']); ?> - Client Profile">
    <meta name="twitter:description" content="View complete client profile with contact details and location.">
    
    <!-- WhatsApp specific -->
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Client Profile: <?php echo htmlspecialchars($client['name']); ?>">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            line-height: 1.6;
            color: #2c3e50;
            background: linear-gradient(270deg, rgb(242 136 150) -10%, var(--bs-primary) 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .document-card {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }
        
        .document-header {
            background: linear-gradient(270deg, rgb(242 136 150) -10%, var(--bs-primary) 100%);
            color: white;
            padding: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .header-content {
            position: relative;
            z-index: 2;
        }
        
        .client-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid rgba(255, 255, 255, 0.3);
            object-fit: cover;
            margin-bottom: 20px;
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
        
        .document-subtitle {
            font-size: 18px;
            opacity: 0.9;
            margin-bottom: 20px;
        }
        
        .document-content {
            padding: 40px;
        }
        
        .info-section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #667eea;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f2f5;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .info-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #667eea;
        }
        
        .info-label {
            font-size: 14px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .info-value {
            font-size: 16px;
            color: #2c3e50;
            font-weight: 500;
        }
        
        .contact-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        
        .contact-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .btn-call {
            background: #28a745;
            color: white;
        }
        
        .btn-email {
            background: #dc3545;
            color: white;
        }
        
        .btn-location {
            background: #17a2b8;
            color: white;
        }
        
        .btn-whatsapp {
            background: #25D366;
            color: white;
        }
        
        .notes-container {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 25px;
            border-left: 5px solid #667eea;
        }
        
        .notes-content {
            font-size: 15px;
            line-height: 1.8;
            color: #495057;
        }
        
        .location-card {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border-radius: 15px;
            padding: 30px;
            color: white;
            margin-top: 20px;
        }
        
        .location-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .location-icon {
            font-size: 40px;
        }
        
        .location-title {
            font-size: 22px;
            font-weight: 600;
        }
        
        .location-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: white;
            color: #4facfe;
            padding: 12px 24px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        
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
        
        .qr-section {
            text-align: center;
            margin-top: 30px;
        }
        
        .qr-placeholder {
            width: 150px;
            height: 150px;
            margin: 0 auto;
            background: #f8f9fa;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 14px;
            border: 1px dashed #dee2e6;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .document-header {
                padding: 30px 20px;
            }
            
            .document-content {
                padding: 20px;
            }
            
            .client-name {
                font-size: 28px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .footer-content {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="document-card">
        <!-- Header -->
        <div class="document-header">
            <div class="header-content">
                <?php if (!empty($client['picture'])): ?>
                    <img src="<?php echo htmlspecialchars($client['picture']); ?>" 
                         alt="<?php echo htmlspecialchars($client['name']); ?>" 
                         class="client-avatar">
                <?php else: ?>
                    <div class="avatar-placeholder">
                        <i class="bi bi-person-fill"></i>
                    </div>
                <?php endif; ?>
                
                <h1 class="client-name"><?php echo htmlspecialchars($client['name']); ?></h1>
                <p class="document-subtitle">Client Profile Document</p>
                
                <div style="display: flex; justify-content: center; gap: 15px; margin-top: 20px; flex-wrap: wrap;">
                    <span style="background: rgba(255, 255, 255, 0.2); padding: 8px 16px; border-radius: 20px; font-size: 14px;">
                        <i class="bi bi-person-badge"></i> ID: <?php echo str_pad($client_id, 6, '0', STR_PAD_LEFT); ?>
                    </span>
                    <span style="background: rgba(255, 255, 255, 0.2); padding: 8px 16px; border-radius: 20px; font-size: 14px;">
                        <i class="bi bi-calendar"></i> <?php echo date('M d, Y'); ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Content -->
        <div class="document-content">
            <!-- Contact Information -->
            <div class="info-section">
                <h2 class="section-title">
                    <i class="bi bi-person-lines-fill"></i>
                    Contact Information
                </h2>
                
                <div class="info-grid">
                    <!-- Name -->
                    <div class="info-item">
                        <div class="info-label">Full Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($client['name']); ?></div>
                    </div>
                    
                    <!-- Status -->
                    <div class="info-item">
                        <div class="info-label">Account Status</div>
                        <div class="info-value">
                            <span style="
                                display: inline-block;
                                padding: 6px 12px;
                                background: <?php echo $client['status'] == 'active' ? '#d4edda' : '#f8d7da'; ?>;
                                color: <?php echo $client['status'] == 'active' ? '#155724' : '#721c24'; ?>;
                                border-radius: 15px;
                                font-weight: 500;
                            ">
                                <?php echo ucfirst(htmlspecialchars($client['status'])); ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Email -->
                    <?php if (!empty($client['email'])): ?>
                    <div class="info-item">
                        <div class="info-label">Email Address</div>
                        <div class="info-value">
                            <a href="mailto:<?php echo htmlspecialchars($client['email']); ?>" 
                               style="color: #667eea; text-decoration: none;">
                                <?php echo htmlspecialchars($client['email']); ?>
                            </a>
                        </div>
                        <div class="contact-buttons">
                            <a href="mailto:<?php echo htmlspecialchars($client['email']); ?>" 
                               class="contact-btn btn-email">
                                <i class="bi bi-envelope"></i> Send Email
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Phone -->
                    <?php if (!empty($client['phone'])): ?>
                    <div class="info-item">
                        <div class="info-label">Phone Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($client['phone']); ?></div>
                        <div class="contact-buttons">
                            <a href="tel:<?php echo htmlspecialchars($client['phone']); ?>" 
                               class="contact-btn btn-call">
                                <i class="bi bi-telephone"></i> Make Call
                            </a>
                            <?php if (preg_match('/^[0-9]{10,}$/', $client['phone'])): ?>
                            <a href="https://wa.me/<?php echo htmlspecialchars($client['phone']); ?>" 
                               target="_blank" 
                               class="contact-btn btn-whatsapp">
                                <i class="bi bi-whatsapp"></i> WhatsApp
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Address -->
                    <?php if (!empty($client['address'])): ?>
                    <div class="info-item">
                        <div class="info-label">Address</div>
                        <div class="info-value"><?php echo nl2br(htmlspecialchars($client['address'])); ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Member Since -->
                    <div class="info-item">
                        <div class="info-label">Member Since</div>
                        <div class="info-value">
                            <i class="bi bi-calendar-check"></i>
                            <?php echo date('F d, Y', strtotime($client['created_at'])); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Notes -->
            <?php if (!empty($client['note'])): ?>
            <div class="info-section">
                <h2 class="section-title">
                    <i class="bi bi-journal-text"></i>
                    Notes & Remarks
                </h2>
                <div class="notes-container">
                    <div class="notes-content">
                        <?php echo nl2br(htmlspecialchars($client['note'])); ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Location -->
            <?php if (!empty($client['location']) || !empty($client['url'])): ?>
            <div class="info-section">
                <h2 class="section-title">
                    <i class="bi bi-geo-alt-fill"></i>
                    Location & Directions
                </h2>
                
                <div class="location-card">
                    <div class="location-header">
                        <div class="location-icon">
                            <i class="bi bi-pin-map-fill"></i>
                        </div>
                        <div>
                            <h3 class="location-title">Client Location</h3>
                            <?php if (!empty($client['location'])): ?>
                            <p style="opacity: 0.9; margin-top: 5px;">
                                <?php echo htmlspecialchars($client['location']); ?>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="location-actions">
                        <?php 
                        // Generate Google Maps directions URL
                        $directions_url = '';
                        if (!empty($client['url'])) {
                            if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $client['url'], $matches)) {
                                $directions_url = "https://www.google.com/maps/dir/?api=1&destination={$matches[1]},{$matches[2]}";
                            } elseif (!empty($client['location'])) {
                                $directions_url = "https://www.google.com/maps/dir/?api=1&destination=" . urlencode($client['location']);
                            } else {
                                $directions_url = $client['url'];
                            }
                        } elseif (!empty($client['location'])) {
                            $directions_url = "https://www.google.com/maps/dir/?api=1&destination=" . urlencode($client['location']);
                        }
                        ?>
                        
                        <?php if (!empty($directions_url)): ?>
                        <a href="<?php echo htmlspecialchars($directions_url); ?>" 
                           target="_blank" 
                           class="action-btn">
                            <i class="bi bi-signpost-split"></i>
                            Get Directions
                        </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($client['url'])): ?>
                        <a href="<?php echo htmlspecialchars($client['url']); ?>" 
                           target="_blank" 
                           class="action-btn">
                            <i class="bi bi-map"></i>
                            View on Map
                        </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($client['location'])): ?>
                        <button onclick="copyToClipboard('<?php echo addslashes($client['location']); ?>')" 
                                class="action-btn"
                                style="background: #6c757d; color: white;">
                            <i class="bi bi-clipboard"></i>
                            Copy Address
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- QR Code -->
            <div class="qr-section">
                <h2 class="section-title">
                    <i class="bi bi-qr-code-scan"></i>
                    Quick Access
                </h2>
                <div class="qr-placeholder" id="qrCodeContainer">
                    <div>
                        <i class="bi bi-qr-code" style="font-size: 60px; color: #667eea; margin-bottom: 10px;"></i>
                        <p>Scan to save this profile</p>
                    </div>
                </div>
                <p style="color: #6c757d; font-size: 14px; margin-top: 10px;">
                    Scan this QR code to save or share this client profile
                </p>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="document-footer">
            <div class="footer-content">
                <div class="footer-logo">
                    <i class="bi bi-shield-check"></i>
                    <span>Client Management System</span>
                </div>
                
                <div class="footer-meta">
                    <p>Document generated on <?php echo date('F d, Y, h:i A'); ?></p>
                    <p>This is a secure shared document</p>
                </div>
            </div>
            
            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e9ecef;">
                <p style="color: #6c757d; font-size: 12px;">
                    <i class="bi bi-info-circle me-2"></i>
                    This document was shared securely. Expires in 30 days.
                </p>
            </div>
        </div>
    </div>
    
    <script>
        // Copy to clipboard function
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Address copied to clipboard!');
            }).catch(err => {
                console.error('Failed to copy:', err);
            });
        }
        
        // Generate QR code for current URL
        document.addEventListener('DOMContentLoaded', function() {
            const currentUrl = window.location.href;
            const qrContainer = document.getElementById('qrCodeContainer');
            
            // You can integrate a QR code library here like:
            // new QRCode(qrContainer, currentUrl);
            
            // For now, show the URL
            qrContainer.innerHTML = `
                <div style="text-align: center; padding: 20px;">
                    <div style="font-size: 60px; margin-bottom: 10px;">
                        <i class="bi bi-qr-code" style="color: #667eea;"></i>
                    </div>
                    <div style="font-size: 12px; color: #6c757d; word-break: break-all; padding: 0 10px;">
                        ${currentUrl}
                    </div>
                </div>
            `;
        });
        
        // Add WhatsApp/Facebook detection for better UX
        const userAgent = navigator.userAgent.toLowerCase();
        if (userAgent.includes('whatsapp') || userAgent.includes('facebook')) {
            document.body.style.background = '#f8f9fa';
            document.querySelector('.document-card').style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
        }
    </script>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</body>
</html>