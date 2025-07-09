<?php
require_once 'connection.php';
Database::setupConn();
session_start();

$product = null;
$buy_now = false;
$cart_checkout = false;
$message = '';
$status = '';
$cart_items = [];
$user_id = $_SESSION['user_id'] ?? null;
$user_email = '';
$user_name = '';

if ($user_id) {
    $user_result = Database::search("SELECT first_name, last_name, email FROM users WHERE user_id = $user_id");
    if ($user_result && $user_row = $user_result->fetch_assoc()) {
        $user_email = $user_row['email'];
        $user_name = trim($user_row['first_name'] . ' ' . $user_row['last_name']);
    }
}

if (isset($_GET['type']) && $_GET['type'] === 'buy_now' && isset($_SESSION['buy_now_data'])) {
    $buy_now = true;
    $product = $_SESSION['buy_now_data'];
}


elseif (isset($_GET['type']) && $_GET['type'] === 'cart') {
    $cart_checkout = true;

    $cart_result = Database::search("
        SELECT ci.cart_item_id, ci.quantity,
               p.product_id, p.product_name, p.price, p.image_url, p.expiry_date, p.quantity_available,
               pc.category_name
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.product_id
        LEFT JOIN product_categories pc ON p.category_id = pc.category_id
        WHERE ci.user_id = $user_id AND p.status = 1
        ORDER BY ci.cart_item_id DESC
    ");
    while ($row = $cart_result->fetch_assoc()) {
        $cart_items[] = $row;
    }
    if (empty($cart_items)) {
        $message = "Your cart is empty.";
        $status = 'danger';
    }
} else {
    $message = "Invalid checkout process.";
    $status = 'danger';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_email = trim($_POST['customer_email'] ?? '');
    $customer_phone = trim($_POST['customer_phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    $errors = [];
    if (empty($customer_name)) {
        $errors[] = "Name is required.";
    }
    if (empty($customer_email) || !filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required.";
    }
    if (empty($customer_phone) || !preg_match('/^[0-9\-\+\s\(\)]+$/', $customer_phone)) {
        $errors[] = "Valid phone number is required.";
    }
    if (empty($address)) {
        $errors[] = "Delivery address is required.";
    }


    if ($buy_now && isset($product)) {
        $product_id = (int)$product['product_id'];
        $order_quantity = isset($_POST['order_quantity']) ? (int)$_POST['order_quantity'] : 1;

 
        $result = Database::search("SELECT quantity_available FROM products WHERE product_id = $product_id");
        if ($result && $row = $result->fetch_assoc()) {
            $current_quantity = (int)$row['quantity_available'];
            if ($order_quantity > $current_quantity) {
                $errors[] = "Selected quantity exceeds available stock.";
            }
        } else {
            $errors[] = "Product not found.";
        }

        if (!empty($errors)) {
            $message = implode('<br>', $errors);
            $status = 'danger';
        } else {
            $new_quantity = $current_quantity - $order_quantity;
            Database::iud("DELETE FROM cart_items WHERE product_id = $product_id AND user_id = $user_id");
            if ($new_quantity > 0) {
                Database::iud("UPDATE products SET quantity_available = $new_quantity WHERE product_id = $product_id");
                $message = "Thank you! Your order is confirmed for $order_quantity item(s). Your product is out for delivery.";
            } else {
                // Remove all cart items for this product before deleting the product
                Database::iud("DELETE FROM cart_items WHERE product_id = $product_id");
                Database::iud("DELETE FROM products WHERE product_id = $product_id");
                $message = "Thank you! Your order is confirmed. The product is now out of stock and removed from the store. Your product is out for delivery.";
            }
            $status = 'success';
            $product = null;
            unset($_SESSION['buy_now_data']);
        }
    }

    elseif ($cart_checkout && !empty($cart_items)) {
        $cart_errors = [];

        foreach ($cart_items as $item) {
            $product_id = (int)$item['product_id'];
            $cart_quantity = (int)$item['quantity'];
            $result = Database::search("SELECT quantity_available FROM products WHERE product_id = $product_id");
            if ($result && $row = $result->fetch_assoc()) {
                $current_quantity = (int)$row['quantity_available'];
                if ($cart_quantity > $current_quantity) {
                    $cart_errors[] = "Not enough stock for '{$item['product_name']}'. Available: $current_quantity, In cart: $cart_quantity.";
                }
            } else {
                $cart_errors[] = "Product '{$item['product_name']}' not found.";
            }
        }

        if (!empty($errors) || !empty($cart_errors)) {
            $message = implode('<br>', array_merge($errors, $cart_errors));
            $status = 'danger';
        } else {
  
            foreach ($cart_items as $item) {
                $product_id = (int)$item['product_id'];
                $cart_quantity = (int)$item['quantity'];
                $result = Database::search("SELECT quantity_available FROM products WHERE product_id = $product_id");
                $row = $result->fetch_assoc();
                $current_quantity = (int)$row['quantity_available'];
                $new_quantity = $current_quantity - $cart_quantity;

                if ($new_quantity > 0) {
                    Database::iud("UPDATE products SET quantity_available = $new_quantity WHERE product_id = $product_id");
  
                    Database::iud("DELETE FROM cart_items WHERE cart_item_id = {$item['cart_item_id']}");
                } else {
                 
                    Database::iud("DELETE FROM cart_items WHERE product_id = $product_id");
                    Database::iud("DELETE FROM products WHERE product_id = $product_id");
                }
            }
            $message = "Thank you! Your order is confirmed. Your products are out for delivery.";
            $status = 'success';
            $cart_items = [];
        }
    }
}

include 'header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow">
                <div class="card-body">
                    <h2 class="mb-4 text-center"><i class="fas fa-credit-card me-2"></i>Checkout</h2>

                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $status; ?> text-center">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($buy_now && $product): ?>
                
                        <div class="row mb-4">
                            <div class="col-md-4 text-center">
                                <?php if ($product['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="img-fluid rounded" style="max-width:120px;">
                                <?php endif; ?>
                            </div>
                            <div class="col-md-8">
                                <h5><?php echo htmlspecialchars($product['product_name']); ?></h5>
                                <p class="mb-1"><strong>Category:</strong> <?php echo htmlspecialchars($product['category_name']); ?></p>
                                <p class="mb-1"><strong>Available Quantity:</strong> <?php echo (int)$product['quantity']; ?></p>
                                <p class="mb-1"><strong>Expiry Date:</strong> <?php echo htmlspecialchars($product['expiry_date']); ?></p>
                                <p class="mb-1 fw-bold">LKR <?php echo number_format($product['original_price'], 2); ?></p>
                            </div>
                        </div>
                        <form method="POST" novalidate>
                            <div class="mb-3">
                                <label for="order_quantity" class="form-label">Select Quantity</label>
                                <input type="number" class="form-control" id="order_quantity" name="order_quantity"
                                       min="1" max="<?php echo (int)$product['quantity']; ?>" value="1" required>
                            </div>
                            <div class="mb-3">
                                <label for="customer_name" class="form-label">Your Name</label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name"
                                       value="<?php echo htmlspecialchars($user_name); ?>" readonly required>
                            </div>
                            <div class="mb-3">
                                <label for="customer_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="customer_email" name="customer_email"
                                       value="<?php echo htmlspecialchars($user_email); ?>" readonly required>
                            </div>
                            <div class="mb-3">
                                <label for="customer_phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="customer_phone" name="customer_phone" required>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Delivery Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2" required></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" name="confirm_order" class="btn btn-success btn-lg">
                                    <i class="fas fa-truck me-2"></i>Confirm Order
                                </button>
                            </div>
                        </form>
                    <?php elseif ($cart_checkout && !empty($cart_items)): ?>
     
                        <div class="mb-4">
                            <h5>Products in your cart:</h5>
                            <ul class="list-group mb-3">
                                <?php foreach ($cart_items as $item): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                            <span class="badge bg-secondary ms-2"><?php echo $item['quantity']; ?> pcs</span>
                                            <span class="badge bg-info ms-2"><?php echo htmlspecialchars($item['category_name']); ?></span>
                                        </div>
                                        <span>LKR <?php echo number_format($item['price'], 2); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <form method="POST" novalidate>
                            <div class="mb-3">
                                <label for="customer_name" class="form-label">Your Name</label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name"
                                       value="<?php echo htmlspecialchars($user_name); ?>" readonly required>
                            </div>
                            <div class="mb-3">
                                <label for="customer_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="customer_email" name="customer_email"
                                       value="<?php echo htmlspecialchars($user_email); ?>" readonly required>
                            </div>
                            <div class="mb-3">
                                <label for="customer_phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="customer_phone" name="customer_phone" required>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Delivery Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2" required></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" name="confirm_order" class="btn btn-success btn-lg">
                                    <i class="fas fa-truck me-2"></i>Confirm Order
                                </button>
                            </div>
                        </form>
                    <?php elseif ($status === 'success'): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-truck fa-4x text-success mb-3"></i>
                            <h3>Your product(s) are out for delivery!</h3>
                            <p class="lead">Thank you for shopping with No Waste.</p>
                            <a href="products.php" class="btn btn-primary mt-3"><i class="fas fa-arrow-left me-2"></i>Back to Products</a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning text-center">
                            No product selected for checkout.
                        </div>
                        <div class="text-center">
                            <a href="products.php" class="btn btn-primary mt-3"><i class="fas fa-arrow-left me-2"></i>Back to Products</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>