<?php
require_once './functions/auth.php';
require_once './config/database.php';
require_once './functions/client_crud.php';
require_once './functions/mobile_view.php';

requireLogin();

$clientCrud = new ClientCrud();

// Pagination settings
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$offset = ($page - 1) * $limit;

$message = '';

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    if ($clientCrud->deleteClientSimple($id)) {
        $message = '<div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <div class="avatar avatar-sm me-3">
                    <div class="avatar-initial bg-success bg-opacity-25 rounded">
                        <i class="bx bx-check-circle text-success"></i>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-0">Success!</h6>
                    <p class="mb-0">Client deleted successfully!</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>';
    } else {
        $message = '<div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <div class="avatar avatar-sm me-3">
                    <div class="avatar-initial bg-danger bg-opacity-25 rounded">
                        <i class="bx bx-error-circle text-danger"></i>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-0">Error!</h6>
                    <p class="mb-0">Failed to delete client!</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>';
    }
}

$totalClients = $clientCrud->countClients();
$totalPages = ceil($totalClients / $limit);


$clients = $clientCrud->getClientsPaginated($limit, $offset);


try {
    $activeCount = method_exists($clientCrud, 'countClientsByStatus') 
        ? $clientCrud->countClientsByStatus('active') 
        : $totalClients; 
} catch (Exception $e) {
    $activeCount = $totalClients; 
}

try {
    $monthCount = method_exists($clientCrud, 'countClientsThisMonth')
        ? $clientCrud->countClientsThisMonth()
        : 0; 
} catch (Exception $e) {
    $monthCount = 0; 
}


?>
<!doctype html>
<html
    lang="en"
    class="layout-navbar-fixed layout-menu-fixed layout-compact"
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
                            <div class="bg-gradient-info rounded-4 p-4 p-md-5 position-relative overflow-hidden">
                                <div class="position-absolute top-0 end-0 w-100 h-100 opacity-10">
                                    <div class="position-absolute end-n4 top-n4">
                                        <div class="bg-white  p-8 opacity-10"></div>
                                    </div>
                                </div>
                                <div class="row align-items-center position-relative">
                                    <div class="col-lg-8">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="avatar avatar-lg me-3">
                                                <div class="cal-icon-wrap bg-white rounded-3 text-info  shadow-sm">
                                                    <i class="bx bx-calendar-event fs-15x"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <h3 class="text-white mb-1">Client Management</h3>
                                                <p class="text-white opacity-75 mb-0">Manage all your clients in one place</p>
                                            </div>
                                        </div>
                                        <nav aria-label="breadcrumb">
                                            <ol class="breadcrumb breadcrumb-light">
                                                <li class="breadcrumb-item">
                                                    <a href="index.php" class="text-white opacity-75">Dashboard</a>
                                                </li>
                                                <li class="breadcrumb-item active text-white">Clients</li>
                                            </ol>
                                        </nav>
                                    </div>
                                    <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                                        <a href="add-client.php" class="btn btn-light btn-hover-lift shadow-sm">
                                            <i class="bx bx-plus me-2"></i>Add New Client
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Success/Error Messages -->
                        <?php echo $message; ?>

                        <!-- Stats Cards -->
                        <!-- <div class="row mb-4">
                            <div class="col-md-6 col-sm-6 mb-4">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="text-muted mb-1 d-block">Total Clients</span>
                                                <h3 class="mb-0"><?php echo $totalClients; ?></h3>
                                            </div>
                                            <div class="avatar avatar-lg">
                                                <div class="avatar-initial bg-light-primary rounded">
                                                    <i class="bx bx-user text-primary"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-6 mb-4">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="text-muted mb-1 d-block">Active Clients</span>
                                                <h3 class="mb-0"><?php echo $activeCount; ?></h3>
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
                        </div> -->

                        <!-- Clients Table/Card -->
                        <div class="card shadow-sm border-0 overflow-hidden">
                            <div class="card-header bg-transparent border-bottom p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h5 class="mb-0">
                                        <i class="bx bx-list-ul text-info me-2"></i>
                                        All Clients
                                    </h5>
                                    <div class="d-flex align-items-center">
                                       
                                        <div class="input-group input-group-sm" style="width: 250px;">
                                            <input type="text" class="form-control" placeholder="Search clients..." id="searchInput">
                                            <button class="btn btn-outline-secondary" type="button">
                                                <i class="bx bx-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <?php if (isMobileDevice()): ?>
                                    <!-- Mobile Card View -->
                                    <div class="p-4">
                                        <?php if (!empty($clients)): ?>
                                            <?php foreach ($clients as $key => $client): ?>
                                                <?php 
                                                $status_colors = [
                                                    'active' => ['bg' => 'bg-success', 'text' => 'text-white'],
                                                    'inactive' => ['bg' => 'bg-secondary', 'text' => 'text-white'],
                                                    'pending' => ['bg' => 'bg-warning', 'text' => 'text-dark'],
                                                    'blocked' => ['bg' => 'bg-danger', 'text' => 'text-white']
                                                ];
                                                $status = $client['status'] ?? 'active';
                                                $color = $status_colors[$status] ?? $status_colors['active'];
                                                ?>
                                                
                                                <div class="card mb-3 border-0 shadow-sm">
                                                    <div class="card-body">
                                                        <div class="d-flex align-items-start">
                                                            <!-- Client Avatar -->
                                                            <div class="position-relative me-3">
                                                                <?php if (!empty($client['picture'])): ?>
                                                                    <img src="<?php echo htmlspecialchars($client['picture']); ?>"
                                                                        alt="<?php echo htmlspecialchars($client['name'] ?? ''); ?>"
                                                                        class="rounded-circle" 
                                                                        width="60" 
                                                                        height="60"
                                                                        style="object-fit: cover;">
                                                                <?php else: ?>
                                                                    <div class="avatar avatar-lg">
                                                                        <div class="avatar-initial bg-light-primary rounded-circle">
                                                                            <i class="bx bx-user text-primary fs-4"></i>
                                                                        </div>
                                                                    </div>
                                                                <?php endif; ?>
                                                                <span class="position-absolute bottom-0 end-0 translate-middle p-1 <?php echo $color['bg']; ?> border border-2 border-white rounded-circle"></span>
                                                            </div>
                                                            
                                                            <!-- Client Info -->
                                                            <div class="flex-grow-1">
                                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                                    <div>
                                                                        <h6 class="mb-0"><?php echo htmlspecialchars($client['name'] ?? 'N/A'); ?></h6>
                                                                        <small class="text-muted">#<?php echo $client['id'] ?? ''; ?></small>
                                                                    </div>
                                                                    <div class="dropdown">
                                                                        <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                                                            <i class="bx bx-dots-vertical-rounded"></i>
                                                                        </button>
                                                                        <ul class="dropdown-menu">
                                                                            <li>
                                                                                <a class="dropdown-item" href="view_client.php?id=<?php echo urlencode($client['id']); ?>">
                                                                                    <i class="bx bx-show me-2"></i>View
                                                                                </a>
                                                                            </li>
                                                                            <li>
                                                                                <a class="dropdown-item" href="edit_client.php?id=<?php echo urlencode($client['id']); ?>">
                                                                                    <i class="bx bx-edit me-2"></i>Edit
                                                                                </a>
                                                                            </li>
                                                                            <li><hr class="dropdown-divider"></li>
                                                                            <li>
                                                                                <a class="dropdown-item text-danger delete-client" 
                                                                                   href="#" 
                                                                                   data-id="<?php echo htmlspecialchars($client['id']); ?>"
                                                                                   data-name="<?php echo htmlspecialchars($client['name'] ?? ''); ?>">
                                                                                    <i class="bx bx-trash me-2"></i>Delete
                                                                                </a>
                                                                            </li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                                
                                                                <!-- Contact Info -->
                                                                <div class="mb-3">
                                                                    <div class="d-flex align-items-center mb-1">
                                                                        <i class="bx bx-envelope text-primary me-2 fs-tiny"></i>
                                                                        <small><?php echo htmlspecialchars($client['email'] ?? 'N/A'); ?></small>
                                                                    </div>
                                                                    <?php if (!empty($client['phone'])): ?>
                                                                    <div class="d-flex align-items-center mb-1">
                                                                        <i class="bx bx-phone text-success me-2 fs-tiny"></i>
                                                                        <small><?php echo htmlspecialchars($client['phone']); ?></small>
                                                                    </div>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($client['location'])): ?>
                                                                    <div class="d-flex align-items-center mb-1">
                                                                        <i class="bx bx-map text-warning me-2 fs-tiny"></i>
                                                                        <small><?php echo htmlspecialchars($client['location']); ?></small>
                                                                    </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                                
                                                                <!-- Status & Actions -->
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <div>
                                                                        <span class="badge <?php echo $color['bg'] . ' ' . $color['text']; ?> px-3 py-1">
                                                                            <i class="bx bx-circle fs-tiny me-1"></i>
                                                                            <?php echo ucfirst($status); ?>
                                                                        </span>
                                                                        <?php if (!empty($client['attachment_count'])): ?>
                                                                            <span class="badge bg-light text-dark ms-2">
                                                                                <i class="bx bx-paperclip"></i>
                                                                                <?php echo $client['attachment_count']; ?>
                                                                            </span>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                    <small class="text-muted">
                                                                        <?php 
                                                                        $created_at = $client['created_at'] ?? '';
                                                                        if (!empty($created_at)) {
                                                                            echo date('M d, Y', strtotime($created_at));
                                                                        } else {
                                                                            echo 'N/A';
                                                                        }
                                                                        ?>
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="text-center py-5">
                                                <div class="avatar avatar-xl mb-3">
                                                    <div class="avatar-initial bg-light rounded">
                                                        <i class="bx bx-user-plus text-muted fs-2"></i>
                                                    </div>
                                                </div>
                                                <h5 class="text-muted mb-2">No clients found</h5>
                                                <p class="text-muted mb-4">Get started by adding your first client</p>
                                                <a href="add-client.php" class="btn btn-primary">
                                                    <i class="bx bx-plus me-1"></i>Add New Client
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <!-- Desktop Table View -->
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="ps-4">CLIENT</th>
                                                    <th>CONTACT</th>
                                                    <th>LOCATION</th>
                                                    <th>STATUS</th>
                                                    <th>CREATED</th>
                                                    <th class="text-end pe-4">ACTIONS</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($clients)): ?>
                                                    <?php foreach ($clients as $key => $client): ?>
                                                        <?php 
                                                        $status_colors = [
                                                            'active' => ['bg' => 'bg-success', 'text' => 'text-white'],
                                                            'inactive' => ['bg' => 'bg-secondary', 'text' => 'text-white'],
                                                            'pending' => ['bg' => 'bg-warning', 'text' => 'text-dark'],
                                                            'blocked' => ['bg' => 'bg-danger', 'text' => 'text-white']
                                                        ];
                                                        $status = $client['status'] ?? 'active';
                                                        $color = $status_colors[$status] ?? $status_colors['active'];
                                                        ?>
                                                        
                                                        <tr class="client-row" data-client-id="<?php echo htmlspecialchars($client['id']); ?>">
                                                            <td class="ps-4">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="position-relative me-3">
                                                                        <?php if (!empty($client['picture'])): ?>
                                                                            <img src="<?php echo htmlspecialchars($client['picture']); ?>"
                                                                                alt="<?php echo htmlspecialchars($client['name'] ?? ''); ?>"
                                                                                class="rounded-circle" 
                                                                                width="40" 
                                                                                height="40"
                                                                                style="object-fit: cover;">
                                                                        <?php else: ?>
                                                                            <div class="avatar avatar-sm">
                                                                                <div class="avatar-initial bg-light-primary rounded-circle">
                                                                                    <i class="bx bx-user text-primary"></i>
                                                                                </div>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                        <span class="position-absolute bottom-0 end-0 translate-middle p-1 <?php echo $color['bg']; ?> border border-2 border-white rounded-circle"></span>
                                                                    </div>
                                                                    <div>
                                                                        <h6 class="mb-0"><?php echo htmlspecialchars($client['name'] ?? 'N/A'); ?></h6>
                                                                        <small class="text-muted">#<?php echo $client['id'] ?? ''; ?></small>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex flex-column">
                                                                    <small class="text-primary">
                                                                        <i class="bx bx-envelope me-1"></i>
                                                                        <?php echo htmlspecialchars($client['email'] ?? 'N/A'); ?>
                                                                    </small>
                                                                    <?php if (!empty($client['phone'])): ?>
                                                                    <small class="text-success mt-1">
                                                                        <i class="bx bx-phone me-1"></i>
                                                                        <?php echo htmlspecialchars($client['phone']); ?>
                                                                    </small>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <?php if (!empty($client['location'])): ?>
                                                                    <div class="d-flex align-items-center">
                                                                        <i class="bx bx-map text-warning me-1"></i>
                                                                        <span><?php echo htmlspecialchars($client['location']); ?></span>
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
                                                            
                                                            <td>
                                                                <small class="text-muted">
                                                                    <?php 
                                                                    $created_at = $client['created_at'] ?? '';
                                                                    if (!empty($created_at)) {
                                                                        echo date('M d, Y', strtotime($created_at));
                                                                    } else {
                                                                        echo 'N/A';
                                                                    }
                                                                    ?>
                                                                </small>
                                                            </td>
                                                            <td class="text-end pe-4">
                                                                <div class="btn-group btn-group-sm">
                                                                    <a href="view_client.php?id=<?php echo urlencode($client['id']); ?>"
                                                                        class="btn btn-outline-info" 
                                                                        title="View Details">
                                                                        <i class="bx bx-show"></i>
                                                                    </a>
                                                                    <a href="edit_client.php?id=<?php echo urlencode($client['id']); ?>"
                                                                        class="btn btn-outline-warning"
                                                                        title="Edit">
                                                                        <i class="bx bx-edit"></i>
                                                                    </a>
                                                                    <button type="button"
                                                                            class="btn btn-outline-danger delete-client"
                                                                            title="Delete"
                                                                            data-id="<?php echo htmlspecialchars($client['id']); ?>"
                                                                            data-name="<?php echo htmlspecialchars($client['name'] ?? ''); ?>">
                                                                        <i class="bx bx-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="7" class="text-center py-5">
                                                            <div class="avatar avatar-xl mb-3">
                                                                <div class="avatar-initial bg-light rounded">
                                                                    <i class="bx bx-user-plus text-muted fs-2"></i>
                                                                </div>
                                                            </div>
                                                            <h5 class="text-muted mb-2">No clients found</h5>
                                                            <p class="text-muted mb-4">Get started by adding your first client</p>
                                                            <a href="add-client.php" class="btn btn-primary">
                                                                <i class="bx bx-plus me-1"></i>Add New Client
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($totalPages > 1): ?>
                                <div class="card-footer bg-transparent border-top py-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="text-muted">
                                                Showing <?php echo ($offset + 1); ?> - <?php echo min($offset + $limit, $totalClients); ?> of <?php echo $totalClients; ?> clients
                                            </span>
                                        </div>
                                        <?php if ($totalPages > 1): ?>
                            <nav aria-label="Client pagination">
                                <ul class="pagination justify-content-center mt-4">

                                    <!-- Previous -->
                                    <?php 
                                    $prevPage = (int)$page - 1;
                                    if ($prevPage < 1) $prevPage = 1;
                                    ?>
                                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $prevPage; ?>" <?php echo ($page <= 1) ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                                            Previous
                                        </a>
                                    </li>

                                    <!-- Page Numbers -->
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <!-- Next -->
                                    <?php 
                                    $nextPage = (int)$page + 1;
                                    if ($nextPage > $totalPages) $nextPage = $totalPages;
                                    ?>
                                    <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $nextPage; ?>" <?php echo ($page >= $totalPages) ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                                            Next
                                        </a>
                                    </li>

                                </ul>
                            </nav>
                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- / Content wrapper -->

                <!-- Floating Add Button -->
                <!-- <a href="add-client.php" class="floating-add-btn" style="text-decoration: none;">
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
                    <p class="mb-2">Are you sure you want to delete the client?</p>
                    <h6 class="text-danger mb-3">"<span id="clientNameDelete"></span>"</h6>
                    <div class="alert alert-warning border-warning bg-warning bg-opacity-10 mb-0">
                        <i class="bx bx-error-circle me-2"></i>
                        This action cannot be undone. All client data and attachments will be permanently deleted.
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary btn-hover-lift" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDelete" class="btn btn-danger btn-hover-lift">
                        <i class="bx bx-trash me-1"></i>Delete Client
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Attachments Modal -->
    <div class="modal fade" id="attachmentsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom-0">
                    <div class="avatar avatar-sm me-3">
                        <div class="avatar-initial bg-info bg-opacity-25 rounded">
                            <i class="bx bx-paperclip text-info"></i>
                        </div>
                    </div>
                    <h5 class="modal-title">Attachments for <span id="clientName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div id="attachmentsList">
                        <div class="text-center py-4">
                            <div class="spinner-border text-info" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary btn-hover-lift" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php include_once('./include/script.php'); ?>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle delete button clicks
        document.querySelectorAll('.delete-client').forEach(button => {
            button.addEventListener('click', function(e) {
                if (this.tagName === 'A') {
                    e.preventDefault();
                }
                
                const clientId = this.getAttribute('data-id');
                const clientName = this.getAttribute('data-name');

                // Set modal content
                document.getElementById('clientNameDelete').textContent = clientName || 'this client';
                document.getElementById('confirmDelete').href = '?action=delete&id=' + encodeURIComponent(clientId);

                // Show modal
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                deleteModal.show();
            });
        });

        // Handle attachments button clicks
        document.querySelectorAll('.view-attachments').forEach(button => {
            button.addEventListener('click', function() {
                const clientId = this.getAttribute('data-id');
                const clientName = this.getAttribute('data-name');

                // Set modal title
                document.getElementById('clientName').textContent = clientName;

                // Load attachments via AJAX
                loadAttachments(clientId);

                // Show modal
                const attachmentsModal = new bootstrap.Modal(document.getElementById('attachmentsModal'));
                attachmentsModal.show();
            });
        });

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('.client-row, .card.mb-3');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }

        // Row hover effects
        document.querySelectorAll('.client-row').forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.backgroundColor = 'rgba(0, 123, 255, 0.05)';
            });
            
            row.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });
        });

        function loadAttachments(clientId) {
            const attachmentsList = document.getElementById('attachmentsList');

            // Show loading spinner
            attachmentsList.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;

            // Fetch attachments via AJAX
            fetch(`get_attachments.php?client_id=${encodeURIComponent(clientId)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.attachments.length > 0) {
                            let html = '<div class="row g-3">';

                            data.attachments.forEach(attachment => {
                                const fileIcon = getFileIcon(attachment.file_type);
                                const fileSize = formatFileSize(attachment.file_size);
                                const fileName = escapeHtml(attachment.original_name);
                                
                                html += `
                                    <div class="col-md-6">
                                        <div class="card border-0 shadow-sm h-100">
                                            <div class="card-body">
                                                <div class="d-flex align-items-start">
                                                    <div class="flex-shrink-0">
                                                        <i class="${fileIcon} fs-3 text-primary"></i>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h6 class="mb-1 text-truncate" title="${fileName}">
                                                            ${fileName}
                                                        </h6>
                                                        <small class="text-muted d-block mb-2">
                                                            <i class="bx bx-calendar me-1"></i>
                                                            ${attachment.created_at}
                                                        </small>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span class="badge bg-light text-dark">
                                                                ${fileSize}
                                                            </span>
                                                            <div>
                                                                <a href="${escapeHtml(attachment.download_url)}" 
                                                                   class="btn btn-sm btn-outline-primary me-1" 
                                                                   target="_blank">
                                                                    <i class="bx bx-show"></i>
                                                                </a>
                                                                <a href="${escapeHtml(attachment.download_url)}" 
                                                                   class="btn btn-sm btn-primary" 
                                                                   download>
                                                                    <i class="bx bx-download"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });

                            html += '</div>';
                            attachmentsList.innerHTML = html;
                        } else {
                            attachmentsList.innerHTML = `
                                <div class="text-center py-5">
                                    <div class="avatar avatar-xl mb-3">
                                        <div class="avatar-initial bg-light rounded">
                                            <i class="bx bx-file text-muted fs-2"></i>
                                        </div>
                                    </div>
                                    <h5 class="text-muted mb-2">No attachments found</h5>
                                    <p class="text-muted">This client doesn't have any attachments yet.</p>
                                </div>
                            `;
                        }
                    } else {
                        attachmentsList.innerHTML = `
                            <div class="alert alert-danger border-0">
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-error-circle me-2"></i>
                                    <div>${escapeHtml(data.message || 'Failed to load attachments')}</div>
                                </div>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    attachmentsList.innerHTML = `
                        <div class="alert alert-danger border-0">
                            <div class="d-flex align-items-center">
                                <i class="bx bx-error-circle me-2"></i>
                                <div>Error loading attachments: ${escapeHtml(error.message)}</div>
                            </div>
                        </div>
                    `;
                });
        }

        function getFileIcon(mimeType) {
            if (mimeType.includes('image/')) return 'bx bx-image';
            if (mimeType.includes('pdf')) return 'bx bx-file-pdf';
            if (mimeType.includes('word') || mimeType.includes('document')) return 'bx bx-file-doc';
            if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return 'bx bx-file-xls';
            if (mimeType.includes('zip') || mimeType.includes('archive')) return 'bx bx-file-zip';
            return 'bx bx-file';
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    });
    </script>

    <style>
        :root {
            --info-gradient: linear-gradient(135deg, #17a2b8 0%, #1abc9c 100%);
        }
        
        .bg-gradient-info {
            background: linear-gradient(270deg, rgb(242 136 150) -10%, var(--bs-primary) 100%);
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
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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
            box-shadow: 0 6px 25px rgba(23, 162, 184, 0.6);
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
        
        .bg-light-info {
            background-color: rgba(23, 162, 184, 0.1) !important;
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
        
        .status-badge {
            min-width: 80px;
            text-align: center;
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
    </style>
</body>
</html>