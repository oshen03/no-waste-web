<?php
session_start();
require_once 'connection.php';
Database::setupConn();

$message = '';
$status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    $errors = [];

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email is required.";
    }
  
    if ($action === 'signup' || $action === 'signin') {
        if (empty($password) || strlen($password) < 6) {
            $errors[] = "Password is required and must be at least 6 characters.";
        }
    }

    if ($action === 'signup') {
     
        if (empty($first_name) || !preg_match('/^[a-zA-Z\s]+$/', $first_name)) {
            $errors[] = "First name is required and must contain only letters and spaces.";
        }
        if (empty($last_name) || !preg_match('/^[a-zA-Z\s]+$/', $last_name)) {
            $errors[] = "Last name is required and must contain only letters and spaces.";
        }

       
        if (empty($phone) || !preg_match('/^[0-9\-\+\s\(\)]+$/', $phone)) {
            $errors[] = "A valid phone number is required.";
        }

 
        if (empty($errors)) {
            $exists = Database::search("SELECT * FROM sellers WHERE email = '$email'");
            if ($exists && $exists->num_rows > 0) {
                $errors[] = "Seller already registered with this email.";
            }
        }

        if (empty($errors)) {
           
            Database::iud("INSERT INTO sellers (first_name, last_name, email, phone, password) VALUES ('$first_name', '$last_name', '$email', '$phone', '$password')");
            $_SESSION['seller_email'] = $email;
            $_SESSION['seller_name'] = $first_name . ' ' . $last_name;
            $_SESSION['is_seller'] = true;
            header('Location: productsAdding.php');
            exit;
        } else {
            $message = implode('<br>', $errors);
            $status = 'danger';
        }
    } elseif ($action === 'signin') {
        if (empty($errors)) {
            $seller = Database::search("SELECT * FROM sellers WHERE email = '$email'");
            if ($seller && $seller->num_rows > 0) {
                $row = $seller->fetch_assoc();
          
                if ($password === $row['password']) {
                    $_SESSION['seller_email'] = $email;
                    $_SESSION['seller_name'] = $row['first_name'] . ' ' . $row['last_name'];
                    $_SESSION['is_seller'] = true;
                    header('Location: productsAdding.php');
                    exit;
                } else {
                    $errors[] = "Incorrect password.";
                }
            } else {
                $errors[] = "Seller not found with this email.";
            }
        }
        if (!empty($errors)) {
            $message = implode('<br>', $errors);
            $status = 'danger';
        }
    }
}
include 'header.php';
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
          SELLER<br>PORTAL
        </div>
      </div>
    </div>

    <!-- Form Section -->
    <div class="col-lg-8 col-md-7 order-md-2 order-1">
      <div class="form-section">
        <div class="form-container">

          <!-- Alert Container -->
          <?php if ($message): ?>
            <div class="alert alert-<?php echo $status; ?> mb-3"><?php echo $message; ?></div>
          <?php endif; ?>

          <!-- Tabs for Sign In / Sign Up -->
          <ul class="nav nav-tabs mb-3" id="sellerTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="signin-tab" data-bs-toggle="tab" data-bs-target="#signin" type="button" role="tab">Sign In</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="signup-tab" data-bs-toggle="tab" data-bs-target="#signup" type="button" role="tab">Sign Up</button>
            </li>
          </ul>
          <div class="tab-content" id="sellerTabContent">
            <!-- Sign In Tab -->
            <div class="tab-pane fade show active" id="signin" role="tabpanel">
              <form method="POST" id="sellerSigninForm">
                <input type="hidden" name="action" value="signin">
                <h2 class="form-title">Seller Sign In</h2>
                <div class="input-group-custom">
                  <label for="seller_signin_email">Email</label>
                  <input type="email" id="seller_signin_email" name="email" required>
                </div>
                <div class="input-group-custom">
                  <label for="seller_signin_password">Password</label>
                  <input type="password" id="seller_signin_password" name="password" required minlength="6">
                </div>
                <div class="d-flex justify-content-center">
                  <button type="submit" class="btn signup-btn" id="sellerSigninBtn">
                    <span class="btn-text">SIGN IN</span>
                  </button>
                </div>
              </form>
            </div>
            <!-- Sign Up Tab -->
            <div class="tab-pane fade" id="signup" role="tabpanel">
              <form method="POST" id="sellerSignupForm">
                <input type="hidden" name="action" value="signup">
                <h2 class="form-title">Seller Sign Up</h2>
                <div class="input-group-custom">
                  <label for="seller_signup_email">Email</label>
                  <input type="email" id="seller_signup_email" name="email" required>
                </div>
                <div class="input-group-custom">
                  <label for="seller_signup_password">Password</label>
                  <input type="password" id="seller_signup_password" name="password" required minlength="6">
                </div>
                <div class="input-group-custom">
                  <label for="seller_first_name">First Name</label>
                  <input type="text" id="seller_first_name" name="first_name" required minlength="2" pattern="[a-zA-Z\s]+" title="Only letters and spaces allowed">
                </div>
                <div class="input-group-custom">
                  <label for="seller_last_name">Last Name</label>
                  <input type="text" id="seller_last_name" name="last_name" required minlength="2" pattern="[a-zA-Z\s]+" title="Only letters and spaces allowed">
                </div>
                <div class="input-group-custom">
                  <label for="seller_phone">Phone</label>
                  <input type="text" id="seller_phone" name="phone" required pattern="^[0-9\-\+\s\(\)]+$" title="Enter a valid phone number">
                </div>
                <div class="d-flex justify-content-center">
                  <button type="submit" class="signup-btn" id="sellerSignupBtn">
                    <span class="btn-text">SIGN UP</span>
                  </button>
                </div>
              </form>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<?php include 'footer.php';