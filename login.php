<?php
session_start();
if (isset($_SESSION['username']) || isset($_COOKIE['email'])) {
  header('Location: home.php');
  exit();
}
include('header.php');
?>

<div class="container-fluid p-0">
  <div class="row g-0">
    <!-- Sidebar -->
    <div class="col-lg-4 col-md-5 order-md-1 order-2">
      <div class="sidebar">
        <div class="logo">
          <img src="Images/logo.png" alt="Logo" class="img-fluid">
        </div>
        <div class="sidebar-text">
          SIGN UP TO<br>NO WASTE
        </div>
      </div>
    </div>

    <!-- Form Section -->
    <div class="col-lg-8 col-md-7 order-md-2 order-1">
      <div class="form-section">
        <div class="form-container">

          <!-- Alert Container -->
          <div id="alertContainer" class="mb-3"></div>

          <!-- Sign In Form -->
          <form id="signinForm">
            <h2 class="form-title">Sign In</h2>

            <div class="input-group-custom">
              <label for="signin_email">Email</label>
              <input type="email" id="signin_email" name="email" required>
            </div>

            <div class="input-group-custom">
              <label for="signin_password">Password</label>
              <input type="password" id="signin_password" name="password" required>
            </div>

            <div class="d-flex justify-content-center">
              <button type="submit" class="btn signup-btn" id="signinBtn">
                <span class="btn-text">SIGN IN</span>
                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
              </button>
            </div>

            <div class="buy-row">
              <a id="switchToSignup"> New User? &nbsp;<span style="color: #0e685c; font-weight: bold; cursor: pointer;">Sign up here</span></a>
            </div>
          </form>

          <!-- Sign Up Form -->
          <form id="signupForm">
            <h2 class="form-title">Sign Up</h2>

            <div class="input-group-custom">
              <label for="signup_email">Email</label>
              <input type="email" id="signup_email" name="email" required>
            </div>

            <div class="input-group-custom">
              <label for="signup_password">Password</label>
              <input type="password" id="signup_password" name="password" required minlength="6">
            </div>

            <div class="input-group-custom">
              <label for="first_name">First Name</label>
              <input type="text" id="first_name" name="first_name" required minlength="2" pattern="[a-zA-Z\s]+" title="Only letters and spaces allowed">
            </div>

            <div class="input-group-custom">
              <label for="last_name">Last Name</label>
              <input type="text" id="last_name" name="last_name" required minlength="2" pattern="[a-zA-Z\s]+" title="Only letters and spaces allowed">
            </div>

            <div class="input-group-custom">
              <label for="password2">Confirm Password</label>
              <input type="password" id="password2" name="password2" required>
            </div>

            <div class="checkbox-group">
              <label>
                <input type="checkbox" id="privacy" required>
                <a href="privacy.html" target="_blank">Privacy & Policy</a>
              </label>
              <label>
                <input type="checkbox" id="terms" required>
                <a href="Terms.html" target="_blank">Terms & Condition</a>
              </label>
            </div>

            <div class="d-flex justify-content-center">
              <button type="submit" class="signup-btn" id="signupBtn">
                <span class="btn-text">SIGN UP</span>
                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
              </button>
            </div>

            <div class="buy-row">
              <a id="switchToSignin">Already have an account? <span style="color: #0e685c; font-weight: bold; cursor: pointer;">Sign in here</span></a>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
<?php include('footer.php'); ?>




