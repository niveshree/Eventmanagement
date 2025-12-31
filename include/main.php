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

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar  ">
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
                    @mycontent
                </div>
                <!-- / Layout page -->
            </div>
        </div>
        <?php include_once('./include/footer.php'); ?>
        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
        <!-- Drag Target Area To SlideIn Menu On Small Screens -->
        <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->
</body>

</html>