<aside id="layout-menu" class="layout-menu menu-vertical menu">
    <div class="app-brand demo">
        <a href="index.php" class="app-brand-link">
            <img src="./assets/img/logo/dream-fest-icon.png" alt="Dream Fest Logo" class="app-brand-logo" width="40" height="40">
            <span class="app-brand-text demo menu-text fw-bold ms-3">Dreamfest</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="icon-base ti menu-toggle-icon d-none d-xl-block"></i>
            <i class="icon-base ti tabler-x d-block d-xl-none"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <?php
        // Get current page filename
        $current_page = basename($_SERVER['PHP_SELF']);

        // Define menu items
        $menu_items = [
            'index.php' => [
                'icon' => 'tabler-smart-home',
                'text' => 'Dashboard',
                'url' => 'index.php'
            ],
            'client-list.php' => [
                'icon' => 'tabler-users',
                'text' => 'Clients',
                'url' => 'client-list.php'
            ],
            'events.php' => [
                'icon' => 'tabler-layout-kanban',
                'text' => 'Events',
                'url' => 'events.php',
                'submenu' => [
                    [
                        'url' => 'events.php',
                        'text' => 'All Events',
                        'icon' => 'tabler-list'
                    ],
                    [
                        'url' => 'events.php?status=upcoming',
                        'text' => 'Upcoming',
                        'icon' => 'tabler-calendar-plus'
                    ],
                    [
                        'url' => 'events.php?status=completed',
                        'text' => 'Completed',
                        'icon' => 'tabler-check'
                    ],
                    [
                        'url' => 'events.php?status=pending',
                        'text' => 'Pending',
                        'icon' => 'tabler-clock'
                    ],
                    [
                        'url' => 'events.php?status=cancelled',
                        'text' => 'Cancelled',
                        'icon' => 'tabler-x'
                    ]
                ]
            ],
            // 'calendar.php' => [
            //     'icon' => 'tabler-calendar',
            //     'text' => 'Calendar',
            //     'url' => 'calendar.php'
            // ]
        ];

        // Render menu items
        foreach ($menu_items as $page => $item):
            $is_active = ($current_page == $page);
            $has_submenu = isset($item['submenu']) && !empty($item['submenu']);
            
            // Check if any submenu item is active
            if ($has_submenu) {
                foreach ($item['submenu'] as $subitem) {
                    $sub_url_parts = parse_url($subitem['url']);
                    $current_url_parts = parse_url($_SERVER['REQUEST_URI']);
                    
                    if (basename($sub_url_parts['path']) == basename($current_url_parts['path'])) {
                         // Check query params if they exist in the menu item
                         if (isset($sub_url_parts['query'])) {
                             if (isset($current_url_parts['query']) && $sub_url_parts['query'] == $current_url_parts['query']) {
                                 $is_active = true;
                                 break;
                             }
                         } else {
                             // No query in menu item, just match page
                             $is_active = true;
                             break;
                         }
                    }
                }
            }
        ?>
            <li class="menu-item <?php echo $is_active ? 'active open' : ''; ?>">
                <a href="<?php echo $item['url']; ?>" class="menu-link <?php echo $has_submenu ? 'menu-toggle' : ''; ?>">
                    <i class="menu-icon icon-base ti <?php echo $item['icon']; ?>"></i>
                    <div data-i18n="<?php echo $item['text']; ?>"><?php echo $item['text']; ?></div>
                </a>
                
                <?php if ($has_submenu): ?>
                <ul class="menu-sub">
                    <?php foreach ($item['submenu'] as $subitem): 
                        // Determine active state for subitems
                        $is_sub_active = false;
                        $sub_url_parts = parse_url($subitem['url']);
                        $current_parts = parse_url($_SERVER['REQUEST_URI']);
                        
                        if (isset($sub_url_parts['query']) && isset($current_parts['query'])) {
                            $is_sub_active = ($sub_url_parts['query'] == $current_parts['query']);
                        } elseif (!isset($sub_url_parts['query']) && basename($sub_url_parts['path']) == basename($_SERVER['PHP_SELF']) && !isset($_GET['status'])) {
                             $is_sub_active = true;
                        }
                    ?>
                    <li class="menu-item <?php echo $is_sub_active ? 'active' : ''; ?>">
                        <a href="<?php echo $subitem['url']; ?>" class="menu-link">
                            <i class="menu-icon icon-base ti <?php echo $subitem['icon']; ?>"></i>
                            <div data-i18n="<?php echo $subitem['text']; ?>"><?php echo $subitem['text']; ?></div>
                        </a>
                    </li>
                    <?php endforeach; ?>
                    
                </ul>
                <li class="menu-item">
                    <a href="logout.php" class="menu-link text-danger"
                    onclick="return confirm('Are you sure you want to logout?');">
                        <i class="menu-icon icon-base ti tabler-logout"></i>
                        <div data-i18n="Logout">Logout</div>
                    </a>
                </li>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</aside>
<div class="menu-mobile-toggler d-xl-none rounded-1">
    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large text-bg-secondary p-2 rounded-1">
        <i class="ti tabler-menu icon-base"></i>
        <i class="ti tabler-chevron-right icon-base"></i>
    </a>
</div>