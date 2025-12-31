<?php
require_once './functions/auth.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
  header("Location: index.php");
  exit();
}
// createUsersTable();
$error = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

  // print_r('k');
  // die;
  $username = trim($_POST['username']);
  $password = $_POST['password'];

  if (empty($username) || empty($password)) {
    $error = "Please enter username and password";
  } else {
    if (login($username, $password)) {
      header("Location: index.php");
      exit();
    } else {
      $error = "Invalid username or password";
    }
  }
}
?>
<!doctype html>

<html
  lang="en"
  class=" layout-wide  customizer-hide"
  dir="ltr"
  data-skin="default"
  data-bs-theme="light"
  data-assets-path="./assets/"
  data-template="vertical-menu-template">

<head>
  <meta charset="utf-8" />
  <meta
    name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Dream Fest</title>

  <meta name="description" content="" />

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="./assets/img/logo/dream-fest-icon.png" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
    rel="stylesheet" />

  <link rel="stylesheet" href="./assets/vendor/fonts/iconify-icons.css" />

  <script src="./assets/vendor/libs/@algolia/autocomplete-js.js"></script>

  <!-- Core CSS -->
  <!-- build:css assets/vendor/css/theme.css  -->

  <link rel="stylesheet" href="./assets/vendor/libs/node-waves/node-waves.css" />

  <link rel="stylesheet" href="./assets/vendor/libs/pickr/pickr-themes.css" />
  <link rel="stylesheet" href="./assets/vendor/css/core.css" />
  <link rel="stylesheet" href="./assets/css/demo.css" />
  <!-- Vendors CSS -->
  <link rel="stylesheet" href="./assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
  <!-- endbuild -->
  <!-- Vendor -->
  <link rel="stylesheet" href="./assets/vendor/libs/@form-validation/form-validation.css" />
  <!-- Page CSS -->
  <!-- Page -->
  <link rel="stylesheet" href="./assets/vendor/css/pages/page-auth.css" />
  <!-- Helpers -->
  <script src="./assets/vendor/js/helpers.js"></script>
  <script src="./assets/vendor/js/template-customizer.js"></script>
  <script src="./assets/js/config.js"></script>
</head>

<body>
  <div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
      <div class="authentication-inner py-6">
        <!-- Login -->
        <div class="card">
          <div class="card-body">
            <!-- Logo -->
            <div class="app-brand justify-content-center mb-6">
              <a href="index.html" class="app-brand-link">
                <img src="./assets/img/logo/dream-fest-icon.png" alt="Dream Fest Logo" class="app-brand-logo" width="40" height="40">
              <span class="app-brand-text demo menu-text fw-bold ms-3">Dream Fest</span>
              </a>
            </div>
            <!-- /Logo -->
            <h4 class="mb-1">Welcome to Admin! 👋</h4>
            <p class="mb-6">Please sign-in to your account...</p>

            <form id="formAuthentication" class="mb-4" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
              <div class="mb-6 form-control-validation">
                <label for="email" class="form-label">Email or Username</label>
                <input
                  type="text"
                  class="form-control"
                  id="email"
                  name="username"
                  placeholder="Enter your email or username"
                  autofocus />
              </div>
              <div class="mb-6 form-password-toggle form-control-validation">
                <label class="form-label" for="password">Password</label>
                <div class="input-group input-group-merge">
                  <input
                    type="password"
                    id="password"
                    class="form-control"
                    name="password"
                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                    aria-describedby="password" />
                  <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
                </div>
              </div>
              <div class="my-8">
                <div class="d-flex justify-content-between">
                  <div class="form-check mb-0 ms-2">
                    <input class="form-check-input" type="checkbox" id="remember-me" />
                    <label class="form-check-label" for="remember-me"> Remember Me </label>
                  </div>
                  <a href="auth-forgot-password-basic.html">
                    <p class="mb-0">Forgot Password?</p>
                  </a>
                </div>
              </div>
              <div class="mb-6">
                <button class="btn btn-primary d-grid w-100" type="submit">Login</button>
              </div>
            </form>

         
          </div>
        </div>
        <!-- /Login -->
      </div>
    </div>
  </div>
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
  <script src="./assets/vendor/libs/@form-validation/popular.js"></script>
  <script src="./assets/vendor/libs/@form-validation/bootstrap5.js"></script>
  <script src="./assets/vendor/libs/@form-validation/auto-focus.js"></script>
  <!-- Main JS -->
  <script src="./assets/js/main.js"></script>
  <!-- Page JS -->
  <script src="./assets/js/pages-auth.js"></script>
</body>

</html>
