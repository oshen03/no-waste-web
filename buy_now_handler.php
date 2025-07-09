<?php
require_once 'connection.php';
session_start();

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id']) && !isset($_COOKIE['username'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to purchase items']);
    exit;
}


$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'buy_now':
            $product_id = (int)($_POST['product_id'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 1);
            
            if ($product_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
                exit;
            }
            
            if ($quantity <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid quantity']);
                exit;
            }
            
            // product details
            $product_query = Database::search("
                SELECT p.*, pc.category_name 
                FROM products p 
                LEFT JOIN product_categories pc ON p.category_id = pc.category_id 
                WHERE p.product_id = $product_id AND p.status = 1 AND p.expiry_date > CURDATE()
            ");
            
            if ($product_query->num_rows === 0) {
                echo json_encode(['success' => false, 'message' => 'Product not found or unavailable']);
                exit;
            }
            
            $product = $product_query->fetch_assoc();
            
            if ($product['quantity_available'] < $quantity) {
                echo json_encode(['success' => false, 'message' => 'Insufficient quantity available. Only ' . $product['quantity_available'] . ' items left.']);
                exit;
            }
            
            // Calculate discount
            $discount = calculateDiscount($product['expiry_date']);
            $original_price = $product['price'];
            $discounted_price = $original_price - ($original_price * $discount / 100);
            $total_amount = $discounted_price * $quantity;
            
            // Store purchase details in session for checkout
            $_SESSION['buy_now_data'] = [
                'product_id' => $product_id,
                'product_name' => $product['product_name'],
                'quantity' => $quantity,
                'original_price' => $original_price,
                'discounted_price' => $discounted_price,
                'discount' => $discount,
                'total_amount' => $total_amount,
                'image_url' => $product['image_url'],
                'category_name' => $product['category_name'],
                'expiry_date' => $product['expiry_date']
            ];
            
            echo json_encode([
                'success' => true,
                'message' => 'Redirecting to checkout...',
                'redirect_url' => 'checkout.php?type=buy_now'
            ]);
            break;
            
        case 'process_order':
    
            $order_data = $_POST;
            
            // Validate required fields
            $required_fields = ['product_id', 'quantity', 'customer_name', 'customer_email', 'customer_phone', 'address'];
            foreach ($required_fields as $field) {
                if (empty($order_data[$field])) {
                    echo json_encode(['success' => false, 'message' => 'Missing required field: ' . $field]);
                    exit;
                }
            }
            
            $product_id = (int)$order_data['product_id'];
            $quantity = (int)$order_data['quantity'];
            $customer_name = Database::$conn->real_escape_string($order_data['customer_name']);
            $customer_email = Database::$conn->real_escape_string($order_data['customer_email']);
            $customer_phone = Database::$conn->real_escape_string($order_data['customer_phone']);
            $address = Database::$conn->real_escape_string($order_data['address']);
            $total_amount = (float)$order_data['total_amount'];
            
            $product_check = Database::search("SELECT quantity_available FROM products WHERE product_id = $product_id AND status = 1");
            if ($product_check->num_rows === 0) {
                echo json_encode(['success' => false, 'message' => 'Product not found']);
                exit;
            }
            
            $product = $product_check->fetch_assoc();
            if ($product['quantity_available'] < $quantity) {
                echo json_encode(['success' => false, 'message' => 'Insufficient quantity available']);
                exit;
            }
            
            $order_id = 'ORD' . date('YmdHis') . $user_id;
            
 
            $new_quantity = $product['quantity_available'] - $quantity;
            Database::iud("UPDATE products SET quantity_available = $new_quantity WHERE product_id = $product_id");
            

            unset($_SESSION['buy_now_data']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Order placed successfully!',
                'order_id' => $order_id,
                'redirect_url' => 'order_success.php?order_id=' . $order_id
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

function calculateDiscount($expiryDate) {
    $currentDate = new DateTime();
    $expiry = new DateTime($expiryDate);
    $daysUntilExpiry = $currentDate->diff($expiry)->days;
    
    if ($expiry < $currentDate) {
        return 0;
    }
    
    if ($daysUntilExpiry <= 1) {
        return 50;
    } elseif ($daysUntilExpiry <= 3) {
        return 30; 
    } elseif ($daysUntilExpiry <= 7) {
        return 20; 
    } elseif ($daysUntilExpiry <= 14) {
        return 10; 
    }
    
    return 0; 
}
?>