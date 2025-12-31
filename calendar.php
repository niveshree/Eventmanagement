<?php
require_once './functions/auth.php';
require_once './functions/event_crud.php';
require_once './functions/client_crud.php';
requireLogin();

$eventCRUD = new EventCRUD();
$clientCRUD = new ClientCrud();
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $file_name = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $file_name = $eventCRUD->uploadAttachment($_FILES['attachment']);
    }
    $data = [
        'event_date' => $_POST['event_date'] ?? "",
        'event_name' => $_POST['event_name'] ?? "",
        'client_name' => $_POST['client_name'] ?? "",
        'mobile' => $_POST['mobile'] ?? "",
        'address' => $_POST['address'] ?? "",
        'email' => $_POST['email'] ?? "",
        'venu_address' => $_POST['venu_address'] ?? "",
        'venu_contact' => $_POST['venu_contact'] ?? "",
        'requirements' => $_POST['requirements'] ?? "",
        'description' => $_POST['description'] ?? "",
        'model' => $_POST['model'] ?? "",
        'image_url' => $_POST['image_url'] ?? "",
        'location_url' => $_POST['location_url'] ?? "",
        'price' => $_POST['price'] ?? "",
        'status' => $_POST['status'] ?? "",
    ];

    $result = $eventCRUD->createEvent($data, $file_name);
}


?>
<?php include_once('./include/header.php'); ?>
<style>
        .flatpickr-wrapper {
            left: 15px;
        }

        .template-customizer-open-btn {
            display: none !important;
        }
    </style>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar  ">
        <div class="layout-container">
            <!-- Menu -->
            <?php include_once('./include/menu.php'); ?>
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- <php include_once('./include/navbar.php'); ?> -->
             

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="card app-calendar-wrapper">
                            <div class="row g-0">
                                <!-- Calendar Sidebar -->
                                <div class="col-12 col-md-4 app-calendar-sidebar border-end" id="app-calendar-sidebar">
                                    <div class="border-bottom p-6 my-sm-0 mb-4">
                                        <button style="background: linear-gradient(270deg, rgb(242 136 150) -10%, var(--bs-primary) 100%);"
                                            class="btn btn-primary btn-toggle-sidebar w-100"
                                            data-bs-toggle="offcanvas"
                                            data-bs-target="#addEventSidebar"
                                            aria-controls="addEventSidebar">
                                            <i class="icon-base ti tabler-plus icon-16px me-2"></i>
                                            <span class="align-middle" >Add Event</span>
                                        </button>
                                    </div>
                                    <div class="px-3 pt-2">
                                        <!-- inline calendar (flatpicker) -->
                                        <div class="inline-calendar"></div>
                                    </div>
                                    <hr class="mb-6 mx-n4 mt-3" />
                                    <div class="px-6 pb-2">
                                        <!-- Filter -->
                                        <div>
                                            <h5>Event Filters</h5>
                                        </div>

                                        <div class="form-check form-check-secondary mb-5 ms-2">
                                            <input
                                                class="form-check-input select-all"
                                                type="checkbox"
                                                id="selectAll"
                                                data-value="all"
                                                checked />
                                            <label class="form-check-label" for="selectAll">All</label>
                                        </div>

                                        <div class="app-calendar-events-filter text-heading">
                                            <div class="form-check form-check-secondary mb-5 ms-2">
                                                <input
                                                    class="form-check-input select-all"
                                                    type="checkbox"
                                                    id="select-completed"
                                                    data-value="completed"
                                                    checked />
                                                <label class="form-check-label" for="selectAll">Completed</label>
                                            </div>
                                            <div class="form-check form-check-danger mb-5 ms-2">
                                                <input
                                                    class="form-check-input input-filter"
                                                    type="checkbox"
                                                    id="select-personal"
                                                    data-value="personal"
                                                    checked />
                                                <label class="form-check-label" for="select-personal">Cancel</label>
                                            </div>
                                            <div class="form-check mb-5 ms-2">
                                                <input
                                                    class="form-check-input input-filter"
                                                    type="checkbox"
                                                    id="select-business"
                                                    data-value="business"
                                                    checked />
                                                <label class="form-check-label" for="select-business">Pending</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- /Calendar Sidebar -->
                                <!-- Calendar & Modal -->
                                <div class="col-12 col-md-8 app-calendar-content">
                                    <div class="card shadow-none border-0">
                                        <div class="card-body pb-0">
                                                <!-- Mobile menu button (shows only on small screens) -->
                                                <div class="d-md-none mb-2">
                                                    <button id="mobile-open-sidebar" class="btn btn-outline-secondary">
                                                        <i class="bi bi-list"></i> Menu
                                                    </button>
                                                </div>
                                            <!-- FullCalendar -->
                                            <div id="calendar"></div>
                                        </div>
                                    </div>
                                    <div class="app-overlay"></div>

                                    <!-- Mobile Calendar Sidebar Offcanvas (will host sidebar on small screens) -->
                                    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileCalendarSidebar" aria-labelledby="mobileCalendarSidebarLabel">
                                        <div class="offcanvas-header">
                                            <h5 class="offcanvas-title" id="mobileCalendarSidebarLabel">Menu</h5>
                                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                        </div>
                                        <div class="offcanvas-body"></div>
                                    </div>
                                    <!-- FullCalendar Offcanvas -->
                                    <div
                                        class="offcanvas offcanvas-end event-sidebar"
                                        tabindex="-1"
                                        id="addEventSidebar"
                                        aria-labelledby="addEventSidebarLabel">
                                        <div class="offcanvas-header border-bottom">
                                            <h5 class="offcanvas-title" id="addEventSidebarLabel">Add Event</h5>
                                            <button
                                                type="button"
                                                class="btn-close text-reset"
                                                data-bs-dismiss="offcanvas"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="offcanvas-body">
                                            <form class="event-form pt-0" id="event-form" action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
                                                <div class="mb-4">
                                                    <label for="exampleFormControlInput1" class="form-label">Event Date</label>
                                                    <input
                                                        type="date"
                                                        name="event_date"
                                                        class="form-control"
                                                        placeholder="Event Name"
                                                        id="exampleFormControlInput1"
                                                        placeholder="name@example.com" />
                                                </div>
                                                <div class="mb-4">
                                                    <label for="exampleFormControlInput1" class="form-label">Event Name</label>
                                                    <input
                                                        type="text"
                                                        name="event_name"
                                                        class="form-control"
                                                        placeholder="Event Name"
                                                        id="exampleFormControlInput1"
                                                        placeholder="name@example.com" />
                                                </div>
                                                <div class="mb-4">
                                                    <label for="exampleFormControlReadOnlyInput1" class="form-label">Client name</label>
                                                    <?php $client = $clientCRUD->getAllClientsSimple() ?>
                                                    <select
                                                        class="form-control"
                                                        type="text"
                                                        name="client_name"
                                                        id="js-example-basic-single"
                                                        required>
                                                        <option value="">Select CLient</option>
                                                        <?php foreach ($client as $clie): ?>
                                                            <option value="<?= $clie['id'] ?>"><?= $clie['name'] ?></option>
                                                        <?php endforeach;  ?>
                                                    </select>
                                                </div>
                                                <div class="mb-4">
                                                    <label for="exampleFormControlReadOnlyInputPlain1" class="form-label">Mobile</label>
                                                    <input
                                                        class="form-control"
                                                        type="text"
                                                        name="mobile"
                                                        placeholder="0987654321"
                                                        id="exampleFormControlReadOnlyInput1"
                                                        value=""
                                                        aria-label="readonly input example" />
                                                </div>
                                                <div class="mb-4">
                                                    <label for="exampleFormControlSelect1" class="form-label">Address</label>
                                                    <textarea class="form-control" id="exampleFormControlTextarea1" name="address" rows="3"></textarea>
                                                </div>
                                                <div class="mb-4">
                                                    <label for="exampleDataList" class="form-label">Mail</label>
                                                    <input
                                                        class="form-control"
                                                        type="email"
                                                        name="email"
                                                        id="exampleFormControlReadOnlyInput1"
                                                        value=""
                                                        placeholder="Enter Email"
                                                        aria-label="readonly input example" />
                                                </div>
                                                <div class="mb-4">
                                                    <label for="exampleFormControlSelect2" class="form-label">Venue (Address)</label>
                                                    <textarea class="form-control" id="exampleFormControlTextarea1" name="venu_address" rows="3"></textarea>
                                                </div>
                                                <div class="mb-4">
                                                    <label for="exampleFormControlSelect3" name="venu_contact" class="form-label">Venue (Contact)</label>
                                                    <input
                                                        class="form-control"
                                                        type="text"
                                                        name="venu_cntact"
                                                        placeholder="Enter Contact"
                                                        id="exampleFormControlReadOnlyInput1"
                                                        value=""
                                                        aria-label="readonly input example" />
                                                </div>
                                                <div class="mb-4">
                                                    <label for="exampleFormControlSelect3" class="form-label">Requirements</label>
                                                    <input
                                                        class="form-control"
                                                        type="text"
                                                        name="requirements"
                                                        placeholder="Enter Contact"
                                                        id="exampleFormControlReadOnlyInput1"
                                                        value=""
                                                        aria-label="readonly input example" />
                                                </div>
                                                <div class="mb-4">
                                                    <label for="exampleFormControlSelect2" class="form-label">Descriptin</label>
                                                    <textarea class="form-control" id="exampleFormControlTextarea1" name="description" rows="3"></textarea>
                                                </div>
                                                <div class="mb-4">
                                                    <label for="exampleFormControlSelect3" class="form-label">Model</label>
                                                    <input
                                                        class="form-control"
                                                        type="text"
                                                        name="model"
                                                        placeholder="Enter Model"
                                                        id="exampleFormControlReadOnlyInput1"
                                                        value=""
                                                        aria-label="readonly input example" />
                                                </div>
                                                <div class="mb-4">
                                                    <label for="exampleFormControlSelect3" class="form-label">Image Url</label>
                                                    <input
                                                        class="form-control"
                                                        type="text"
                                                        name="image_url"
                                                        placeholder="https://test.jpg/"
                                                        id="exampleFormControlReadOnlyInput1"
                                                        value=""
                                                        aria-label="readonly input example" />
                                                </div>
                                                <!-- <iframe src="" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe> -->
                                                <div class="mb-4">
                                                    <label for="exampleFormControlSelect3" class="form-label">Location Url</label>
                                                    <input
                                                        class="form-control"
                                                        type="text"
                                                        name="location_url"
                                                        placeholder="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d31103.08645920711!2d80.19917221600669!3d12.979154909221833!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3a525d9ff2065a3b%3A0x66435015604038cc!2sVelachery%2C%20Chennai%2C%20Tamil%20Nadu!5e0!3m2!1sen!2sin!4v1765302308967!5m2!1sen!2sin"
                                                        id="exampleFormControlReadOnlyInput1"
                                                        value=""
                                                        aria-label="readonly input example" />
                                                </div>
                                                <div class="mb-4">
                                                    <label for="exampleFormControlSelect3" class="form-label">Price</label>
                                                    <input
                                                        class="form-control"
                                                        type="text"
                                                        name="price"
                                                        placeholder="Enter Price (Ex 10000)"
                                                        id="exampleFormControlReadOnlyInput1"
                                                        value=""
                                                        aria-label="readonly input example" />
                                                </div>
                                                <div class="mb-4">
                                                    <label for="exampleFormControlSelect3" class="form-label">Status</label>
                                                    <select id="largeSelect" name="status" class="form-select form-select-lg">
                                                        <option>Large select</option>
                                                        <option value="1">Pending</option>
                                                        <option value="2">On Going</option>
                                                        <option value="3">Confirmed</option>
                                                        <option value="4">Completed</option>
                                                        <option value="5">Cancelled</option>
                                                    </select>
                                                </div>
                                                <div class="mb-4">
                                                    <label for="formFile" class="form-label">Image Attachement</label>
                                                    <input class="form-control" type="file" id="formFile" name="attachment" />
                                                </div>
                                                <div class="d-flex justify-content-sm-between justify-content-start mt-6 gap-2">
                                                    <div class="d-flex">
                                                        <button type="submit" id="addEventBtn" class="btn btn-primary btn-add-event me-4">
                                                            Add
                                                        </button>
                                                        <button
                                                            type="reset"
                                                            class="btn btn-label-secondary btn-cancel me-sm-0 me-1"
                                                            data-bs-dismiss="offcanvas">
                                                            Cancel
                                                        </button>
                                                    </div>
                                                    <button class="btn btn-label-danger btn-delete-event d-none">Delete</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <!-- /Calendar & Modal -->
                            </div>
                        </div>
                    </div>
                    <!-- / Content -->

                    <!-- Footer -->

                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>

        <!-- Drag Target Area To SlideIn Menu On Small Screens -->
        <div class="drag-target"></div>
    </div>

    <!-- Date Events Modal -->
    <div class="modal fade" id="dateEventsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dateEventsModalLabel">
                        <i class="bi bi-calendar-event me-2"></i>
                        Events for <span id="selectedDate"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="eventsContainer" class="events-container">
                        <!-- Events will be loaded here -->
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading events...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/theme.js  -->

    <script src="./assets/vendor/libs/jquery/jquery.js"></script>

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
    <script src="./assets/vendor/libs/fullcalendar/fullcalendar.js"></script>
    <script src="./assets/vendor/libs/@form-validation/popular.js"></script>
    <script src="./assets/vendor/libs/@form-validation/bootstrap5.js"></script>
    <script src="./assets/vendor/libs/@form-validation/auto-focus.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="./assets/vendor/libs/moment/moment.js"></script>
    <script src="./assets/vendor/libs/flatpickr/flatpickr.js"></script>
    <!-- Main JS -->
    <script src="./assets/js/main.js"></script>
    <!-- Page JS -->
    <script src="./assets/js/app-calendar-events.js"></script>
    <script src="./assets/js/app-calendar.js"></script>

    <script>
        var resul = "<?php echo isset($result) && $result ? true : false ?>";
        console.log(resul);
        if (resul) {
            $(document).ready(function() {
                $(".custom-toast.success-toast").click();
            });
        }
    </script>
    <?php include_once('./include/footer.php'); ?>
</body>
</html>