<?php
session_start();
require_once 'connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'signin') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        exit;
    }
    
    // First check if user is a regular user
    $user = Database::search("SELECT * FROM users WHERE email = '$email'");
    if ($user && $user->num_rows > 0) {
        $user_data = $user->fetch_assoc();
        
        // Check if password matches (plain text comparison for now)
        if ($password === $user_data['password']) {
            $_SESSION['user_id'] = $user_data['user_id'];
            $_SESSION['username'] = $user_data['first_name'] . ' ' . $user_data['last_name'];
            $_SESSION['email'] = $user_data['email'];
            $_SESSION['is_seller'] = false;
            
            echo json_encode(['success' => true, 'message' => 'Login successful', 'redirect' => 'home.php']);
            exit;
        }
    }
    
    // If not a regular user, check if user is a seller
    $seller = Database::search("SELECT * FROM sellers WHERE email = '$email'");
    if ($seller && $seller->num_rows > 0) {
        $seller_data = $seller->fetch_assoc();
        
        // Check if password matches (plain text comparison for now)
        if ($password === $seller_data['password']) {
            $_SESSION['seller_id'] = $seller_data['seller_id'];
            $_SESSION['seller_email'] = $seller_data['email'];
            $_SESSION['seller_name'] = $seller_data['first_name'] . ' ' . $seller_data['last_name'];
            $_SESSION['is_seller'] = true;
            
            echo json_encode(['success' => true, 'message' => 'Seller login successful', 'redirect' => 'productsAdding.php']);
            exit;
        }
    }
    
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    
} elseif ($action === 'signup') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    
    if (empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
        exit;
    }
    
    // Check if user already exists
    $existing_user = Database::search("SELECT * FROM users WHERE email = '$email'");
    if ($existing_user && $existing_user->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'User already exists']);
        exit;
    }
    
    // Insert new user with plain text password
    $query = "INSERT INTO users (email, password, first_name, last_name) VALUES ('$email', '$password', '$first_name', '$last_name')";
    $result = Database::iud($query);
    
    if ($result) {
        // Get the newly created user
        $new_user = Database::search("SELECT * FROM users WHERE email = '$email'");
        if ($new_user && $new_user->num_rows > 0) {
            $user_data = $new_user->fetch_assoc();
            
            $_SESSION['user_id'] = $user_data['user_id'];
            $_SESSION['username'] = $user_data['first_name'] . ' ' . $user_data['last_name'];
            $_SESSION['email'] = $user_data['email'];
            $_SESSION['is_seller'] = false;
            
            echo json_encode(['success' => true, 'message' => 'Registration successful']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Registration failed']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed']);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>