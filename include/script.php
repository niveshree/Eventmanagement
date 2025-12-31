<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/11.0.5/swiper-bundle.min.js" integrity="sha512-Ysw1DcK1P+uYLqprEAzNQJP+J4hTx4t/3X2nbVwszao8wD+9afLjBQYjz7Uk4ADP+Er++mJoScI42ueGtQOzEA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js" integrity="sha512-BkpSL20WETFylMrcirBahHfSnY++H2O1W+UnEEO4yNIl+jI2+zowyoGJpbtk6bx97fBXf++WJHSSK2MV4ghPcg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> --> -->
<!-- Page JS -->

<script src="./assets/vendor/libs/popper/popper.js"></script>
<script src="./assets/vendor/js/bootstrap.js"></script>
<script src="./assets/vendor/libs/node-waves/node-waves.js"></script>

<script src="./assets/vendor/libs/pickr/pickr.js"></script>

<script src="./assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

<script src="./assets/vendor/libs/hammer/hammer.js"></script>

<script src="./assets/vendor/libs/i18n/i18n.js"></script>

<script src="./assets/vendor/js/menu.js"></script>

<!-- endbuild -->

<!-- Vendors JS -->
<script src="./assets/vendor/libs/apex-charts/apexcharts.js"></script>
<script src="./assets/vendor/libs/swiper/swiper.js"></script>
<script src="./assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>

<!-- Main JS -->

<script src="./assets/js/main.js"></script>
<script src="./assets/css/alert.js"></script>

<!-- Page JS -->
<script src="./assets/js/dashboards-analytics.js"></script>
<script>
    // Ensure menu stays open on desktop
    document.addEventListener('DOMContentLoaded', function() {
        const menu = document.getElementById('layout-menu');
        const isDesktop = window.innerWidth >= 1200; // or your breakpoint

        if (isDesktop && menu) {
            // Remove any classes that might collapse the menu
            menu.classList.remove('menu-collapsed');
            menu.classList.add('menu-expanded');

            // Ensure all menu items are visible
            const menuItems = menu.querySelectorAll('.menu-item');
            menuItems.forEach(item => {
                item.style.display = 'block';
                item.style.visibility = 'visible';
                item.style.opacity = '1';
            });
        }

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1200 && menu) {
                menu.classList.remove('menu-collapsed');
                menu.classList.add('menu-expanded');
            }
        });
    });
</script>