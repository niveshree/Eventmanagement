 <!-- Footer -->
 <footer class="content-footer footer bg-footer-theme">
     <div class="container-xxl">
         <div
             class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
             <div class="text-body">
                 &#169;
                 <script>
                     document.write(new Date().getFullYear());
                 </script>
                 , made with ❤️ by <a href="https://Dream Fest.com/" target="_blank" class="footer-link">Dream Fest</a>
             </div>
             <div class="d-none d-lg-inline-block">

             </div>
         </div>
     </div>
 </footer>
 <!-- / Footer -->

 <!-- Mobile Bottom Navigation -->
 <nav class="mobile-bottom-nav">
     <a href="index.php" class="nav-item-mobile <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
         <i class="bx bx-home-alt"></i>
         <span>Home</span>
     </a>
     <a href="events.php" class="nav-item-mobile <?php echo basename($_SERVER['PHP_SELF']) == 'events.php' ? 'active' : ''; ?>">
         <i class="bx bx-list-ul"></i>
         <span>Events</span>
     </a>
     <div class="nav-fab-container">
         <a href="event-add.php" class="nav-fab">
             <i class="bx bx-plus"></i>
         </a>
     </div>
     <a href="calendar.php" class="nav-item-mobile <?php echo basename($_SERVER['PHP_SELF']) == 'calendar.php' ? 'active' : ''; ?>">
         <i class="bx bx-calendar"></i>
         <span>Calendar</span>
     </a>
     <a href="client-list.php" class="nav-item-mobile <?php echo basename($_SERVER['PHP_SELF']) == 'client-list.php' ? 'active' : ''; ?>">
         <i class="bx bx-user"></i>
         <span>Clients</span>
     </a>
 </nav>

 <div class="content-backdrop fade"></div>