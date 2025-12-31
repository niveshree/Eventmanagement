<?php
require_once './functions/auth.php';
require_once './functions/event_crud.php';
require_once './functions/client_crud.php';
requireLogin();

$eventCRUD = new EventCRUD();
$clientCRUD = new ClientCrud();
$message = '';

// Get event ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: events.php');
    exit();
}

$event_id = intval($_GET['id']);
$event = $eventCRUD->getEventById($event_id);

if (!$event) {
    header('Location: events.php');
    exit();
}

// Get client details
$client = null;
if (!empty($event['client_name']) && is_numeric($event['client_name'])) {
    $client = $clientCRUD->getClientByIdSimple($event['client_name']);
}
?>

<?php include_once('./include/header.php'); ?>
<style>


    .hero-section {
        background: var(--primary-gradient);
        padding: 4rem 2rem;
        color: white;
        border-radius: 20px 20px 0 0;
        position: relative;
        overflow: hidden;
        
    }

    .hero-section::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
        background-size: 50px 50px;
        animation: float 20s linear infinite;
        
    }

    @keyframes float {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }


    .stat-card {
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--primary-gradient);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .timeline {
        position: relative;
        padding-left: 2rem;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: linear-gradient(to bottom, #667eea, #764ba2);
    }

    .timeline-item {
        position: relative;
        margin-bottom: 2rem;
        padding-left: 1.5rem;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -0.5rem;
        top: 0.5rem;
        width: 1rem;
        height: 1rem;
        border-radius: 50%;
        background: #667eea;
        border: 3px solid white;
        box-shadow: 0 0 0 3px #667eea;
    }

    .avatar-circle {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        font-weight: bold;
        margin-right: 1rem;
    }

    .tag {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-size: 0.875rem;
        font-weight: 500;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .info-box {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        border-left: 4px solid;
    }

    .floating-action-btn {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        z-index: 1000;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    .map-container {
        height: 300px;
        border-radius: 15px;
        overflow: hidden;
        border: 3px solid white;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .text-dark{
        color:#000 !important;
    }
    .contact-chip {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        background: rgba(102, 126, 234, 0.1);
        border-radius: 25px;
        margin: 0.25rem;
        transition: all 0.3s ease;
    }

    .contact-chip:hover {
        background: rgba(102, 126, 234, 0.2);
        transform: translateY(-2px);
    }

    .status-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 0.5rem;
        animation: blink 2s infinite;
    }

    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    .floating-action-btn {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 1000;
}

.fab-menu {
    position: absolute;
    bottom: 70px;
    right: 0;
    display: none;
    flex-direction: column;
    gap: 15px;
}

.fab-menu.open {
    display: flex;
    animation: slideUp 0.3s ease;
}

.fab-item {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-decoration: none;
    transform: scale(0);
    transition: all 0.3s ease;
    position: relative;
}

.fab-menu.open .fab-item {
    transform: scale(1);
}

.fab-item:nth-child(1) { background: #ffc107; animation-delay: 0.1s; }
.fab-item:nth-child(2) { background: #17a2b8; animation-delay: 0.2s; }
.fab-item:nth-child(3) { background: #007bff; animation-delay: 0.3s; }
.fab-item:nth-child(4) { background: #dc3545; animation-delay: 0.4s; }

.fab-item:hover {
    transform: scale(1.1) !important;
}

.fab-item::after {
    content: attr(data-tooltip);
    position: absolute;
    right: 60px;
    background: rgba(0,0,0,0.8);
    color: white;
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 12px;
    white-space: nowrap;
    opacity: 0;
    transition: opacity 0.3s;
}

.fab-item:hover::after {
    opacity: 1;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

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
                        <!-- Hero Section -->
                        <div class="hero-section mb-4">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h1 class="text-white display-4 fw-bold mb-3"><?php echo htmlspecialchars($event['event_name']); ?></h1>
                                    <div class="d-flex flex-wrap align-items-center gap-3">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-calendar-event me-2 fs-4"></i>
                                            <span class="fs-5"><?php echo date('F d, Y', strtotime($event['event_date'])); ?></span>
                                        </div>
                                        <!-- <div class="d-flex align-items-center">
                                            <i class="bi bi-clock me-2 fs-4"></i>
                                            <span class="fs-5"><?php echo date('h:i A', strtotime($event['event_date'])); ?></span>
                                        </div> -->
                                        <?php
                                        $status_colors = [
                                            'pending' => ['bg' => 'bg-warning', 'text' => 'text-dark'],
                                            'ongoing' => ['bg' => 'bg-info', 'text' => 'text-white'],
                                            'confirmed' => ['bg' => 'bg-primary', 'text' => 'text-white'],
                                            'completed' => ['bg' => 'bg-success', 'text' => 'text-white'],
                                            'cancelled' => ['bg' => 'bg-danger', 'text' => 'text-white']
                                        ];
                                        $status = $event['status'];
                                        ?>
                                        <div class="px-3 py-2 rounded-pill <?php 
                                            $bg_class = isset($status_colors[$status]) ? $status_colors[$status]['bg'] : 'bg-secondary';
                                            $text_class = isset($status_colors[$status]) ? $status_colors[$status]['text'] : 'text-white';
                                            echo $bg_class . ' ' . $text_class; 
                                        ?>">
                                            <!-- <span class="status-indicator"></span> -->
                                            <?php echo ucfirst($status ?: 'Status Null'); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-md-end">
                                <!-- Price display removed -->

                                </div>
                            </div>
                        </div>

                        <!-- Main Content -->
                        <div class="row">
                            <!-- Left Column - Client & Details -->
                            <div class="col-lg-8">
                                <!-- Client Information Card -->
                                <div class="card glass-card mb-4">
                                    <div class="card-header bg-transparent border-0 d-flex align-items-center">
                                        <div class="avatar-circle bg-primary me-3">
                                            <i class="bi bi-person-fill"></i>
                                        </div>
                                        <div>
                                            <h4 class="mb-0">Client Information</h4>
                                            <p class="text-muted mb-0">All client details</p>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-4">
                                                <div class="info-box" style="border-left-color: #667eea;">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="bi bi-person-badge me-2 text-primary"></i>
                                                        <h6 class="mb-0">Client Name</h6>
                                                    </div>
                                                    <p class="h5 mb-0">
                                                        <?php 
                                                        if ($client && isset($client['name'])) {
                                                            echo htmlspecialchars($client['name']);
                                                        } else {
                                                            echo htmlspecialchars($event['client_name']);
                                                        }
                                                        ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-4">
                                                <div class="info-box" style="border-left-color: #28a745;">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="bi bi-telephone me-2 text-success"></i>
                                                        <h6 class="mb-0">Contact Number</h6>
                                                    </div>
                                                    <p class="h5 mb-0">
                                                        <a href="tel:<?php echo htmlspecialchars($event['mobile']); ?>" class="text-decoration-none text-dark">
                                                            <?php echo htmlspecialchars($event['mobile']); ?>
                                                        </a>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-4">
                                                <div class="info-box" style="border-left-color: #17a2b8;">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="bi bi-envelope me-2 text-info"></i>
                                                        <h6 class="mb-0">Email Address</h6>
                                                    </div>
                                                    <p class="h5 mb-0">
                                                        <a href="mailto:<?php echo htmlspecialchars($event['email']); ?>" class="text-decoration-none text-dark">
                                                            <?php echo htmlspecialchars($event['email']); ?>
                                                        </a>
                                                    </p>
                                                </div>
                                            </div>
                                            <?php if (!empty($event['address'])): ?>
                                            <div class="col-md-6 mb-4">
                                                <div class="info-box" style="border-left-color: #fd7e14;">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="bi bi-geo-alt me-2 text-warning"></i>
                                                        <h6 class="mb-0">Client Address</h6>
                                                    </div>
                                                    <p class="mb-0"><?php echo htmlspecialchars($event['address']); ?></p>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Quick Contact Chips -->
                                        <div class="mt-4">
                                            <h6 class="mb-3">Quick Actions</h6>
                                            <div class="d-flex flex-wrap">
                                                <a href="tel:<?php echo htmlspecialchars($event['mobile']); ?>" class="contact-chip">
                                                    <i class="bi bi-telephone me-2"></i> Call Client
                                                </a>
                                                <!-- <a href="mailto:<php echo htmlspecialchars($event['email']); ?>" class="contact-chip">
                                                    <i class="bi bi-envelope me-2"></i> Send Email
                                                </a> -->
                                                <!-- <a href="https://wa.me/<php echo preg_replace('/[^0-9]/', '', $event['mobile']); ?>" 
                                                   target="_blank" class="contact-chip">
                                                    <i class="bi bi-whatsapp me-2"></i> WhatsApp
                                                </a> -->
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Venue & Location -->
                                <div class="card glass-card mb-4">
                                    <div class="card-header bg-transparent border-0 d-flex align-items-center">
                                        <div class="avatar-circle bg-success me-3">
                                            <i class="bi bi-geo-alt-fill"></i>
                                        </div>
                                        <div>
                                            <h4 class="mb-0">Venue Information</h4>
                                            <p class="text-muted mb-0">Event location details</p>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-4">
                                            <div class="col-md-8">
                                                <h5 class="mb-3">Venue Address</h5>
                                                <div class="p-3 bg-light rounded">
                                                    <i class="bi bi-pin-map-fill text-success me-2"></i>
                                                    <?php echo htmlspecialchars($event['venu_address']); ?>
                                                </div>
                                                <?php if (!empty($event['venu_contact'])): ?>
                                                <div class="mt-3">
                                                    <h6 class="mb-2">Venue Contact</h6>
                                                    <p class="mb-0">
                                                        <i class="bi bi-telephone me-2"></i>
                                                        <?php echo htmlspecialchars($event['venu_contact']); ?>
                                                    </p>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-4">
                                                <?php if (!empty($event['location_url'])): ?>
                                                <?php
                                                    $map_query = urlencode($event['venu_address']);
                                                    $direction_url = "https://www.google.com/maps/dir/?api=1&destination={$map_query}";
                                                    ?>

                                                    <a href="<?php echo $direction_url; ?>"
                                                    target="_blank"
                                                    class="btn btn-outline-primary w-100 mb-2">
                                                        <i class="bi bi-map me-2"></i> Open Maps
                                                    </a>

                                                <?php endif; ?>
                                                <button class="btn btn-outline-success w-100" onclick="copyToClipboard('<?php echo htmlspecialchars($event['venu_address']); ?>')">
                                                    <i class="bi bi-clipboard me-2"></i> Copy Address
                                                </button>
                                            </div>
                                        </div>

                                        <?php if (!empty($event['location_url'])): ?>
                                        <?php
                                            $embed_query = urlencode($event['venu_address']);
                                            $embed_url = "https://www.google.com/maps?q={$embed_query}&output=embed";
                                            ?>

                                            <div class="map-container mt-4">
                                                <iframe
                                                    src="<?php echo $embed_url; ?>"
                                                    width="100%"
                                                    height="100%"
                                                    style="border:0;"
                                                    loading="lazy"
                                                    allowfullscreen>
                                                </iframe>
                                            </div>

                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column - Stats & Timeline -->
                            <div class="col-lg-4 text-dark">
                                <!-- Event Stats -->
                                <div class="stat-card glass-card mb-4" style="background: var(--primary-gradient); color: #000;">
                                    <h5 class="text-dark mb-3"><i class="bi bi-graph-up me-2"></i> Event Statistics</h5>
                                    <div class="row text-center">
                                        <div class="col-6 mb-3">
                                            <div class="p-3 rounded" style="background: rgba(255,255,255,0.2);color: #000;">
                                                <i class="bi bi-calendar-check fs-2 d-block mb-2"></i>
                                                <div class="text-dark h4 mb-0"><?php echo date('d', strtotime($event['event_date'])); ?></div>
                                                <small>Day</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Event Timeline -->
                                <div class="card glass-card mb-4">
                                    <div class="card-header bg-transparent border-0">
                                        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i> Event Timeline</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="timeline">
                                            <div class="timeline-item">
                                                <h6 class="mb-1">Event Created</h6>
                                                <p class="text-muted small mb-0">
                                                    <?php echo date('M d, Y h:i A', strtotime($event['created_at'])); ?>
                                                </p>
                                            </div>
                                            <div class="timeline-item">
                                                <h6 class="mb-1">Scheduled Date</h6>
                                                <p class="text-muted small mb-0">
                                                    <?php echo date('M d, Y', strtotime($event['event_date'])); ?>
                                                </p>
                                            </div>
                                            <?php if ($event['status'] == 'completed'): ?>
                                            <div class="timeline-item">
                                                <h6 class="mb-1">Completed</h6>
                                                <p class="text-muted small mb-0">Event successfully completed</p>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Event Tags -->
                                <div class="card glass-card mb-4">
                                    <div class="card-header bg-transparent border-0">
                                        <h5 class="mb-0"><i class="bi bi-tags me-2"></i> Event Details</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (!empty($event['requirements'])): ?>
                                        <div class="mb-3">
                                            <h6>Requirements</h6>
                                            <div class="p-3 bg-light rounded">
                                                <?php echo htmlspecialchars($event['requirements']); ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <?php if (!empty($event['model'])): ?>
                                        <div class="mb-3">
                                            <h6>Model</h6>
                                            <span class="tag bg-primary text-white">
                                                <i class="bi bi-person-badge me-1"></i>
                                                <?php echo htmlspecialchars($event['model']); ?>
                                            </span>
                                        </div>
                                        <?php endif; ?>

                                        <?php if (!empty($event['description'])): ?>
                                        <div>
                                            <h6>Description</h6>
                                            <div class="p-3 bg-light rounded">
                                                <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Media Attachments -->
                                <?php if (!empty($event['image_url']) || !empty($event['attachment'])): ?>
                                <div class="card glass-card">
                                    <div class="card-header bg-transparent border-0">
                                        <h5 class="mb-0"><i class="bi bi-paperclip me-2"></i> Media & Files</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (!empty($event['image_url'])): ?>
                                        <div class="mb-3">
                                            <a href="<?php echo htmlspecialchars($event['image_url']); ?>" 
                                               target="_blank" 
                                               class="d-block text-decoration-none">
                                                <div class="p-3 bg-light rounded text-center">
                                                    <i class="bi bi-image text-primary fs-1 mb-2"></i>
                                                    <div>View Image</div>
                                                </div>
                                            </a>
                                        </div>
                                        <?php endif; ?>

                                        <?php if (!empty($event['attachment'])): ?>
                                        <div>
                                            <?php
                                            $file_path = './uploads/attachments/' . $event['attachment'];
                                            $file_ext = pathinfo($event['attachment'], PATHINFO_EXTENSION);
                                            $icon_class = 'bi-file-earmark';
                                            
                                            if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                                $icon_class = 'bi-image';
                                            } elseif (in_array($file_ext, ['pdf'])) {
                                                $icon_class = 'bi-file-earmark-pdf';
                                            } elseif (in_array($file_ext, ['doc', 'docx'])) {
                                                $icon_class = 'bi-file-earmark-word';
                                            }
                                            ?>
                                            <a href="<?php echo $file_path; ?>" 
                                               target="_blank" 
                                               class="d-block text-decoration-none">
                                                <div class="p-3 bg-light rounded d-flex align-items-center">
                                                    <i class="bi <?php echo $icon_class; ?> text-primary fs-3 me-3"></i>
                                                    <div>
                                                        <div class="fw-bold"><?php echo htmlspecialchars($event['attachment']); ?></div>
                                                        <small class="text-muted">Click to download</small>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Animated Circular Menu -->
                    <div class="floating-action-btn">
                        <button class="btn btn-primary " style=" padding: 10px;
    border-radius: 10px;" id="fab-main">
                            <i class="bi bi-plus fs-4"></i>
                        </button>
                        
                        <div class="fab-menu">
                            <a href="edit_event.php?id=<?php echo $event['id']; ?>" 
                            class="fab-item" 
                            data-tooltip="Edit Event">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="#" 
                            onclick="printEvent(); return false;" 
                            class="fab-item" 
                            data-tooltip="Print">
                                <i class="bi bi-printer"></i>
                            </a>
                            <!-- <a href="mailto:<?php echo htmlspecialchars($event['email']); ?>?subject=Event Details: <?php echo urlencode($event['event_name']); ?>" 
                            class="fab-item" 
                            data-tooltip="Email Client">
                                <i class="bi bi-envelope"></i>
                            </a> -->
                            <a href="?action=delete&id=<?php echo $event['id']; ?>" 
                            onclick="return confirm('Are you sure you want to delete this event?')" 
                            class="fab-item fab-delete" 
                            data-tooltip="Delete">
                                <i class="bi bi-trash"></i>
                            </a>
                        </div>
                    </div>                    
                    <?php include_once('./include/footer.php'); ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php include_once('./include/script.php'); ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const fabMain = document.getElementById('fab-main');
        const fabMenu = document.querySelector('.fab-menu');
        
        fabMain.addEventListener('click', function(e) {
            e.preventDefault();
            fabMenu.classList.toggle('open');
            
            // Change icon
            const icon = this.querySelector('i');
            if (fabMenu.classList.contains('open')) {
                icon.className = 'bi bi-x';
                this.style.transform = 'rotate(45deg)';
            } else {
                icon.className = 'bi bi-plus';
                this.style.transform = 'rotate(0deg)';
            }
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!fabMain.contains(e.target) && !fabMenu.contains(e.target)) {
                fabMenu.classList.remove('open');
                const icon = fabMain.querySelector('i');
                icon.className = 'bi bi-plus';
                fabMain.style.transform = 'rotate(0deg)';
            }
        });
    });
    </script>
    <script>
        function printEvent() {
            const printContent = `
                <html>
                    <head>
                        <title>Event Details - <?php echo htmlspecialchars($event['event_name']); ?></title>
                        <style>
                            @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');
                            body { font-family: 'Poppins', sans-serif; padding: 30px; background: linear-gradient(270deg, rgb(242 136 150) -10%, var(--bs-primary) 100%); }
                            .print-container { 
                                background: white; 
                                border-radius: 20px; 
                                padding: 40px; 
                                max-width: 800px; 
                                margin: 0 auto;
                                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                            }
                            .print-header {
                                text-align: center;
                                margin-bottom: 40px;
                                padding-bottom: 20px;
                                border-bottom: 3px solid #667eea;
                            }
                            .print-section {
                                margin-bottom: 30px;
                                padding: 20px;
                                background: #f8f9fa;
                                border-radius: 15px;
                                border-left: 5px solid #667eea;
                            }
                            .print-label {
                                color: #667eea;
                                font-weight: 600;
                                margin-bottom: 5px;
                                display: block;
                            }
                            .print-value {
                                font-size: 16px;
                                color: #333;
                            }
                            .print-grid {
                                display: grid;
                                grid-template-columns: repeat(2, 1fr);
                                gap: 20px;
                            }
                            .qr-code {
                                text-align: center;
                                padding: 20px;
                                background: white;
                                border-radius: 10px;
                                margin-top: 30px;
                            }
                            @media print {
                                body { background: white !important; }
                                .print-container { box-shadow: none; }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="print-container">
                            <div class="print-header">
                                <h1 style="color: #667eea; margin-bottom: 10px;"><?php echo htmlspecialchars($event['event_name']); ?></h1>
                                <p style="color: #666; margin-bottom: 20px;">
                                    Event Date: <strong><?php echo date('F d, Y', strtotime($event['event_date'])); ?></strong> | 
                                    Status: <span style="color: #28a745;"><?php echo ucfirst($event['status']); ?></span>
                                </p>
                                <div style="background: linear-gradient(270deg, rgb(242 136 150) -10%, var(--bs-primary) 100%); padding: 15px; border-radius: 10px; color: white;">
                                    <h2 style="margin: 0; font-size: 24px;">
                                        Total: <i class="bi bi-currency-rupee"></i><?php echo number_format($event['price'], 2); ?>
                                    </h2>
                                </div>
                            </div>
                            
                            <div class="print-grid">
                                <div class="print-section">
                                    <h3 style="color: #667eea; margin-bottom: 15px;">👤 Client Information</h3>
                                    <div class="print-label">Client Name</div>
                                    <div class="print-value"><?php 
                                        if ($client && isset($client['name'])) {
                                            echo htmlspecialchars($client['name']);
                                        } else {
                                            echo htmlspecialchars($event['client_name']);
                                        }
                                    ?></div>
                                    
                                    <div class="print-label">Mobile</div>
                                    <div class="print-value"><?php echo htmlspecialchars($event['mobile']); ?></div>
                                    
                                    <div class="print-label">Email</div>
                                    <div class="print-value"><?php echo htmlspecialchars($event['email']); ?></div>
                                </div>
                                
                                <div class="print-section">
                                    <h3 style="color: #667eea; margin-bottom: 15px;">📍 Venue Information</h3>
                                    <div class="print-label">Venue Address</div>
                                    <div class="print-value"><?php echo htmlspecialchars($event['venu_address']); ?></div>
                                    
                                    <?php if (!empty($event['venu_contact'])): ?>
                                    <div class="print-label">Venue Contact</div>
                                    <div class="print-value"><?php echo htmlspecialchars($event['venu_contact']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if (!empty($event['description'])): ?>
                            <div class="print-section">
                                <h3 style="color: #667eea; margin-bottom: 15px;">📝 Description</h3>
                                <div class="print-value"><?php echo nl2br(htmlspecialchars($event['description'])); ?></div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="print-section">
                                <h3 style="color: #667eea; margin-bottom: 15px;">📋 Event Details</h3>
                                <div class="print-grid">
                                    <div>
                                        <div class="print-label">Requirements</div>
                                        <div class="print-value"><?php echo !empty($event['requirements']) ? htmlspecialchars($event['requirements']) : 'N/A'; ?></div>
                                    </div>
                                    <div>
                                        <div class="print-label">Model</div>
                                        <div class="print-value"><?php echo !empty($event['model']) ? htmlspecialchars($event['model']) : 'N/A'; ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="qr-code">
                                <p style="color: #666; margin-bottom: 10px;">Scan for digital copy</p>
                                <!-- QR Code would be generated here -->
                                <div style="background: #f8f9fa; padding: 20px; display: inline-block; border-radius: 10px;">
                                    <div style="font-size: 12px; color: #999;">QR Code Placeholder</div>
                                </div>
                            </div>
                            
                            <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd;">
                                <p style="color: #666; font-size: 12px;">
                                    Generated on <?php echo date('F d, Y h:i A'); ?> | Event Management System
                                </p>
                            </div>
                        </div>
                    </body>
                </html>
            `;
            
            const printWindow = window.open('', '_blank', 'width=900,height=600');
            printWindow.document.write(printContent);
            printWindow.document.close();
            printWindow.focus();
            setTimeout(() => {
                printWindow.print();
            }, 1000);
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                // Show toast notification
                const toast = document.createElement('div');
                toast.className = 'position-fixed bottom-0 end-0 p-3';
                toast.innerHTML = `
                    <div class="toast show" role="alert">
                        <div class="toast-header bg-success text-white">
                            <strong class="me-auto">Copied!</strong>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                        </div>
                        <div class="toast-body">
                            Address copied to clipboard
                        </div>
                    </div>
                `;
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 3000);
            });
        }

        // Initialize animations
        document.addEventListener('DOMContentLoaded', function() {
            // Add scroll animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            // Observe all stat cards
            document.querySelectorAll('.stat-card').forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'all 0.5s ease';
                observer.observe(card);
            });
        });
    </script>
</body>
</html>