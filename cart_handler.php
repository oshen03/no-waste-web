<?php
require_once 'connection.php';
session_start();

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id']) && !isset($_COOKIE['email'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to add items to cart']);
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
 
    $email = $_COOKIE['email'] ?? ''; 
    if ($email) {
        $escaped_email = Database::$conn->real_escape_string($email);
        $user_query = Database::search("SELECT user_id FROM users WHERE email = '$escaped_email'");
        if ($user_query->num_rows > 0) {
            $user_data = $user_query->fetch_assoc();
            $user_id = $user_data['user_id'];
            $_SESSION['user_id'] = $user_id; 
        }
    }
}

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid user session']);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
 
        case 'add_to_cart':
            $product_id = (int)($_POST['product_id'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 1);

            if ($product_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
                exit;
            }

 
            $product_check = Database::search("SELECT * FROM products 
                                      WHERE product_id = $product_id 
                                      AND status = 1
                                      AND expiry_date > CURDATE()");

            if ($product_check->num_rows === 0) {
                echo json_encode(['success' => false, 'message' => 'Product not available']);
                exit;
            }

            $product = $product_check->fetch_assoc();


            $existing_cart = Database::search("SELECT * FROM cart_items 
                                      WHERE product_id = $product_id 
                                      AND user_id = $user_id");

            if ($existing_cart->num_rows > 0) {
                $existing_item = $existing_cart->fetch_assoc();
                $new_quantity = $existing_item['quantity'] + $quantity;

                if ($new_quantity > $product['quantity_available']) {
                    echo json_encode(['success' => false, 'message' => 'Exceeds available stock']);
                    exit;
                }

                Database::iud("UPDATE cart_items SET quantity = $new_quantity 
                      WHERE cart_item_id = {$existing_item['cart_item_id']}");
            } else {
   
                Database::iud("INSERT INTO cart_items (product_id, user_id, quantity) 
                      VALUES ($product_id, $user_id, $quantity)");
            }


            $cart_count = Database::search("SELECT COUNT(*) as count FROM cart_items 
                                   WHERE user_id = $user_id");
            $cart_total = $cart_count->fetch_assoc()['count'] ?? 0;

            echo json_encode([
                'success' => true,
                'message' => 'Added to cart!',
                'cart_count' => $cart_total
            ]);
            break;

        case 'update_quantity':
            $cart_item_id = (int)($_POST['cart_item_id'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 0);

            if ($cart_item_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid cart item ID']);
                exit;
            }

            if ($quantity <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid quantity']);
                exit;
            }

  
            $cart_check = Database::search("SELECT ci.cart_item_id, ci.product_id, p.quantity_available 
                                         FROM cart_items ci 
                                         JOIN products p ON ci.product_id = p.product_id 
                                         WHERE ci.cart_item_id = $cart_item_id AND ci.user_id = $user_id");

            if ($cart_check->num_rows === 0) {
                echo json_encode(['success' => false, 'message' => 'Cart item not found']);
                exit;
            }

            $cart_item = $cart_check->fetch_assoc();

            if ($quantity > $cart_item['quantity_available']) {
                echo json_encode(['success' => false, 'message' => 'Insufficient quantity available']);
                exit;
            }

            Database::iud("UPDATE cart_items SET quantity = $quantity WHERE cart_item_id = $cart_item_id");

            echo json_encode(['success' => true, 'message' => 'Cart updated successfully']);
            break;

        case 'remove_item':
            $cart_item_id = (int)($_POST['cart_item_id'] ?? 0);

            if ($cart_item_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid cart item ID']);
                exit;
            }

     
            $cart_check = Database::search("SELECT cart_item_id FROM cart_items WHERE cart_item_id = $cart_item_id AND user_id = $user_id");

            if ($cart_check->num_rows === 0) {
                echo json_encode(['success' => false, 'message' => 'Cart item not found']);
                exit;
            }

            Database::iud("DELETE FROM cart_items WHERE cart_item_id = $cart_item_id");

            echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
            break;

        case 'get_cart_count':
            $cart_count = Database::search("SELECT SUM(quantity) as total FROM cart_items WHERE user_id = $user_id");
            $cart_total = $cart_count->fetch_assoc()['total'] ?? 0;

            echo json_encode(['success' => true, 'cart_count' => $cart_total]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
