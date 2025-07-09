<?php
require_once 'connection.php';
Database::setupConn();
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) && !isset($_COOKIE['username'])) {
    header('Location: login.php');
    exit;
}


$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;

function calculateDiscount($expiryDate)
{
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


$cart_items = Database::search("
    SELECT ci.cart_item_id, ci.quantity,
           p.product_id, p.product_name, p.price, p.image_url, p.expiry_date,
           pc.category_name
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.product_id
    LEFT JOIN product_categories pc ON p.category_id = pc.category_id
    WHERE ci.user_id = $user_id AND p.status = 1
    ORDER BY ci.cart_item_id DESC  -- Changed to cart_item_id for ordering
");

// Calculate total
$total = 0;
$original_total = 0;
$total_discount = 0;
?>

<?php include 'header.php'; ?>

<div class="container my-5">
    <h2 class="mb-4 text-center">
        <i class="fas fa-shopping-cart me-2"></i>Your Cart
        <span class="badge bg-success ms-2 cart-count" id="cart-count"><?php echo $cart_items->num_rows; ?></span>
    </h2>

    <div id="alert-container"></div>

    <?php if ($cart_items->num_rows > 0): ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Cart Items</h5>
                        <div id="cart-items-container">
                            <?php while ($item = $cart_items->fetch_assoc()): ?>
                                <?php
                                $discount = calculateDiscount($item['expiry_date']);
                                $original_price = $item['price'];
                                $discounted_price = $original_price - ($original_price * $discount / 100);
                                $item_total = $discounted_price * $item['quantity'];
                                $item_original_total = $original_price * $item['quantity'];

                                $total += $item_total;
                                $original_total += $item_original_total;

                                $currentDate = new DateTime();
                                $expiry = new DateTime($item['expiry_date']);
                                $daysUntilExpiry = $currentDate->diff($expiry)->days;
                                ?>
                                <div class="cart-item border-bottom pb-3 mb-3" data-cart-id="<?php echo $item['cart_item_id']; ?>">
                                    <div class="row align-items-center">
                                        <div class="col-md-2">
                                            <?php if ($item['image_url']): ?>
                                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>"
                                                    alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                                    class="img-fluid rounded"
                                                    style="width: 80px; height: 80px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                                    style="width: 80px; height: 80px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-4">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                            <?php if ($item['category_name']): ?>
                                                <small class="text-muted"><?php echo htmlspecialchars($item['category_name']); ?></small>
                                            <?php endif; ?>
                                            <div class="mt-1">
                                                <small class="text-warning">
                                                    <i class="fas fa-clock"></i>
                                                    <?php if ($daysUntilExpiry <= 1): ?>
                                                        Expires in <?php echo $daysUntilExpiry; ?> day(s)
                                                    <?php else: ?>
                                                        Expires in <?php echo $daysUntilExpiry; ?> days
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="price-section">
                                                <?php if ($discount > 0): ?>
                                                    <span class="badge bg-danger mb-1">-<?php echo $discount; ?>%</span><br>
                                                    <small class="text-muted text-decoration-line-through">LKR <?php echo number_format($original_price, 2); ?></small><br>
                                                    <strong class="text-success">LKR <?php echo number_format($discounted_price, 2); ?></strong>
                                                <?php else: ?>
                                                    <strong>LKR <?php echo number_format($original_price, 2); ?></strong>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="quantity-controls">
                                                <div class="input-group">
                                                    <button class="btn btn-outline-secondary btn-sm" type="button"
                                                        onclick="updateQuantity(<?php echo $item['cart_item_id']; ?>, <?php echo $item['quantity'] - 1; ?>)">-</button>
                                                    <input type="number" class="form-control form-control-sm text-center quantity-input"
                                                        value="<?php echo $item['quantity']; ?>"
                                                        min="1"
                                                        onchange="updateQuantity(<?php echo $item['cart_item_id']; ?>, this.value)">
                                                    <button class="btn btn-outline-secondary btn-sm" type="button"
                                                        onclick="updateQuantity(<?php echo $item['cart_item_id']; ?>, <?php echo $item['quantity'] + 1; ?>)">+</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="text-end">
                                                <strong class="item-total">LKR <?php echo number_format($item_total, 2); ?></strong>
                                                <br>
                                                <button class="btn btn-danger btn-sm mt-1"
                                                    onclick="removeFromCart(<?php echo $item['cart_item_id']; ?>)">
                                                    <i class="fas fa-trash"></i> Remove
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Order Summary</h5>
                        <div class="d-flex justify-content-between">
                            <span>Original Total:</span>
                            <span id="original-total">LKR <?php echo number_format($original_total, 2); ?></span>
                        </div>
                        <?php if ($original_total > $total): ?>
                            <div class="d-flex justify-content-between text-success">
                                <span>Discount:</span>
                                <span id="discount-amount">-LKR <?php echo number_format($original_total - $total, 2); ?></span>
                            </div>
                        <?php endif; ?>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Total:</strong>
                            <strong id="cart-total">LKR <?php echo number_format($total, 2); ?></strong>
                        </div>
                        <div class="d-grid gap-2 mt-3">
                            <a href="checkout.php?type=cart" class="btn btn-success btn-lg">
                                <i class="fas fa-credit-card me-2"></i>Proceed to Checkout
                            </a>
                            <a href="products.php" class="btn btn-outline-primary">
                                <i class="fas fa-shopping-basket me-2"></i>Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Discount Information -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="card-title">Discount Information</h6>
                        <div class="row text-center">
                            <div class="col-6">
                                <span class="badge bg-danger p-2 mb-1">50% OFF</span>
                                <small class="d-block">Expires in 1 day</small>
                            </div>
                            <div class="col-6">
                                <span class="badge bg-warning p-2 mb-1">30% OFF</span>
                                <small class="d-block">Expires in 3 days</small>
                            </div>
                            <div class="col-6 mt-2">
                                <span class="badge bg-info p-2 mb-1">20% OFF</span>
                                <small class="d-block">Expires in 7 days</small>
                            </div>
                            <div class="col-6 mt-2">
                                <span class="badge bg-success p-2 mb-1">10% OFF</span>
                                <small class="d-block">Expires in 14 days</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <div class="text-center py-5">
            <div class="empty-cart">
                <i class="fas fa-shopping-cart fa-5x text-muted mb-3"></i>
                <h4>Your cart is empty</h4>
                <p class="text-muted">Add some products to your cart to see them here</p>
                <a href="products.php" class="btn btn-success btn-lg mt-3">
                    <i class="fas fa-shopping-basket me-2"></i>Start Shopping
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    function showAlert(message, type = 'danger') {
        const alertContainer = document.getElementById('alert-container');
        alertContainer.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

        setTimeout(() => {
            const alert = alertContainer.querySelector('.alert');
            if (alert) {
                alert.classList.remove('show');
            }
        }, 5000);
    }

    function updateQuantity(cartItemId, newQuantity) {
        if (newQuantity < 1) {
            if (confirm('Remove this item from cart?')) {
                removeFromCart(cartItemId);
            }
            return;
        }

        fetch('cart_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update_quantity&cart_item_id=${cartItemId}&quantity=${newQuantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); 
                } else {
                    showAlert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while updating quantity');
            });
    }

    function removeFromCart(cartItemId) {
        if (!confirm('Are you sure you want to remove this item from cart?')) {
            return;
        }

        fetch('cart_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=remove_item&cart_item_id=${cartItemId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
         
                    const cartItem = document.querySelector(`[data-cart-id="${cartItemId}"]`);
                    if (cartItem) {
                        cartItem.remove();
                    }

 
                    location.reload();
                } else {
                    showAlert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while removing item');
            });
    }
</script>

<style>
    .cart-item {
        transition: all 0.3s ease;
    }

    .cart-item:hover {
        background-color: #f8f9fa;
    }

    .quantity-controls .input-group {
        width: 120px;
    }

    .quantity-input {
        width: 50px;
    }

    .price-section {
        text-align: center;
    }

    .empty-cart {
        padding: 2rem;
    }

    .badge {
        font-size: 0.75em;
    }
</style>

<?php include 'footer.php'; ?>