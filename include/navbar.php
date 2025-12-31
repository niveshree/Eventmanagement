
<nav
    class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme"
    id="layout-navbar">
    
   <div class="navbar-nav-right d-flex align-items-center justify-content-between w-100" id="navbar-collapse">
    <!-- Logo/Title for mobile -->
    <div class="d-flex d-xl-none align-items-center">
        <img src="./assets/img/logo/dream-fest-icon.png" alt="Logo" width="30" height="30" class="me-2">
        <span class="fw-bold" style="color: var(--bs-primary);">Event Management</span>
    </div>
    
    <!-- Centered Title for desktop -->
    <div class="d-none d-xl-flex flex-grow-1 justify-content-center">
        <h3 class="mb-0" style="color: var(--bs-primary); font-weight: 600; font-size: 1.5rem;">Event Management</h3>
    </div>
    
    <ul class="navbar-nav flex-row align-items-center ms-auto">
        <!-- User -->
        <li class="nav-item navbar-dropdown dropdown-user dropdown">
            <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                <div class="avatar avatar-online">
                    <?php
                    $firstName = isset($_SESSION['user_first_name']) ? substr($_SESSION['user_first_name'], 0, 1) : 'U';
                    echo '<div class="avatar-initial rounded-circle d-flex align-items-center justify-content-center" 
                            style="width: 38px; height: 38px; background: linear-gradient(270deg, rgb(242 136 150) -10%, var(--bs-primary) 100%);">
                            <span class="text-white fw-bold">' . strtoupper($firstName) . '</span>
                          </div>';
                    ?>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item mt-0" href="pages-profile-user.html">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-2">
                                <?php
                                $displayText = isset($_SESSION['full_name']) && !empty($_SESSION['full_name']) ? 
                                    strtoupper(substr($_SESSION['full_name'], 0, 1)) : 
                                    (isset($_SESSION['username']) ? strtoupper(substr($_SESSION['username'], 0, 1)) : 'U');
                                echo '<div class="avatar-initial rounded-circle d-flex align-items-center justify-content-center" 
                                        style="width: 36px; height: 36px; background: linear-gradient(270deg, rgb(242 136 150) -10%, var(--bs-primary) 100%);">
                                        <span class="text-white fw-bold">' . $displayText . '</span>
                                      </div>';
                                ?>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">
                                    <?php
                                    echo isset($_SESSION['full_name']) && !empty($_SESSION['full_name']) ? 
                                        htmlspecialchars($_SESSION['full_name']) : 
                                        (isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User');
                                    ?>
                                </h6>
                                <small class="text-body-secondary">
                                    <?php
                                    echo isset($_SESSION['role']) ? htmlspecialchars(ucfirst($_SESSION['role'])) : 'User';
                                    ?>
                                </small>
                            </div>
                        </div>
                    </a>
                </li>
                <li><div class="dropdown-divider my-1 mx-n2"></div></li>
                <!-- <li>
                    <a class="dropdown-item" href="pages-profile-user.html">
                        <i class="icon-base ti tabler-user me-3"></i>
                        <span class="align-middle">My Profile</span>
                    </a>
                </li> -->
                <li><div class="dropdown-divider my-1 mx-n2"></div></li>
                <li>
                    <div class="px-2 pt-2 pb-1">
                        <a class="btn btn-sm btn-danger w-100 d-flex justify-content-center align-items-center" 
                           href="logout.php">
                            <span class="me-2">Logout</span>
                            <i class="icon-base ti tabler-logout"></i>
                        </a>
                    </div>
                </li>
            </ul>
        </li>
    </ul>
</div>
</nav>