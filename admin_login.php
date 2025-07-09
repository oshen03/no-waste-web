<?php
session_start();

$message = '';
$status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === 'nowaste' && $password === 'Nowaste2025') {
        $_SESSION['is_admin'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: admin.php');
        exit;
    } else {
        $message = "Invalid admin credentials.";
        $status = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NoWaste Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --primary-green: #28a745;
            --dark-green: #218838;
            --light-green: #d4edda;
            --success-green: #155724;
        }
        
        body {
            background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .admin-login-container {
            max-width: 450px;
            margin: 5vh auto;
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(40, 167, 69, 0.15);
            padding: 40px 35px;
            border: 1px solid rgba(40, 167, 69, 0.1);
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logo-container img {
            max-width: 120px;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(40, 167, 69, 0.2);
        }
        
        .admin-login-title {
            text-align: center;
            color: var(--primary-green);
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 30px;
            text-shadow: 0 2px 4px rgba(40, 167, 69, 0.1);
        }
        
        .form-label {
            font-weight: 600;
            color: var(--success-green);
            margin-bottom: 8px;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
        
        .btn-admin-login {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            padding: 12px 0;
            width: 100%;
            font-weight: 700;
            font-size: 1.1rem;
            margin-top: 20px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-admin-login:hover {
            background: linear-gradient(135deg, var(--dark-green) 0%, #1e7e34 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3);
        }
        
        .btn-admin-login:active {
            transform: translateY(0);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            font-weight: 500;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .input-group-text {
            background: var(--light-green);
            border: 2px solid #e9ecef;
            border-right: none;
            border-radius: 10px 0 0 10px;
            color: var(--primary-green);
        }
        
        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }
        
        .input-group .form-control:focus {
            border-left: none;
        }
        
        .admin-icon {
            margin-right: 10px;
            font-size: 1.3rem;
        }
        
        @media (max-width: 576px) {
            .admin-login-container {
                margin: 2vh 20px;
                padding: 30px 25px;
            }
            
            .admin-login-title {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="admin-login-container">
            <div class="logo-container">
                <img src="Images/logo.png" alt="NoWaste Logo" class="img-fluid">
            </div>
            
            <div class="admin-login-title">
                <i class="fas fa-user-shield admin-icon"></i>Admin Login
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $status; ?> mb-4" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" autocomplete="off">
                <div class="mb-3">
                    <label for="username" class="form-label">
                        <i class="fas fa-user me-2"></i>Admin Username
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-user"></i>
                        </span>
                        <input type="text" class="form-control" id="username" name="username" required autofocus placeholder="Enter admin username">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock me-2"></i>Admin Password
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" class="form-control" id="password" name="password" required placeholder="Enter admin password">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-admin-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </button>
            </form>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>