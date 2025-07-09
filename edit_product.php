<?php
require_once 'connection.php';
Database::setupConn();
session_start();

if (!isset($_SESSION['is_seller']) || !$_SESSION['is_seller']) {
    header('Location: seller_login.php');
    exit;
}

$seller_email = $_SESSION['seller_email'];
$seller_result = Database::search("SELECT seller_id FROM sellers WHERE email = '$seller_email'");
$seller_id = $seller_result->fetch_assoc()['seller_id'] ?? 0;

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];
    $product_name = trim($_POST['product_name']);
    $price = (float)$_POST['price'];
    $quantity = (int)$_POST['quantity_available'];
    $description = trim($_POST['description']);
    $expiry_date = $_POST['expiry_date'];
    $status = isset($_POST['status']) ? 1 : 0;

    Database::iud("UPDATE products SET product_name='$product_name', price=$price, quantity_available=$quantity, description='$description', expiry_date='$expiry_date', status=$status WHERE product_id=$product_id AND sellers_seller_id=$seller_id");
    $msg = "Product updated!";
}


$products = Database::search("SELECT * FROM products WHERE sellers_seller_id = $seller_id");

include 'header.php';
?>
<div class="container my-5">
    <h2>Edit My Products</h2>
    <?php if (isset($msg)): ?>
        <div class="alert alert-success"><?php echo $msg; ?></div>
    <?php endif; ?>
    <?php while ($row = $products->fetch_assoc()): ?>
        <form method="POST" class="mb-4 border p-3 rounded">
            <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
            <div class="mb-2">
                <label>Product Name</label>
                <input type="text" name="product_name" class="form-control" value="<?php echo htmlspecialchars($row['product_name']); ?>" required>
            </div>
            <div class="mb-2">
                <label>Price</label>
                <input type="number" name="price" class="form-control" value="<?php echo $row['price']; ?>" step="0.01" required>
            </div>
            <div class="mb-2">
                <label>Quantity</label>
                <input type="number" name="quantity_available" class="form-control" value="<?php echo $row['quantity_available']; ?>" required>
            </div>
            <div class="mb-2">
                <label>Description</label>
                <textarea name="description" class="form-control"><?php echo htmlspecialchars($row['description']); ?></textarea>
            </div>
            <div class="mb-2">
                <label>Expiry Date</label>
                <input type="date" name="expiry_date" class="form-control" value="<?php echo $row['expiry_date']; ?>" required>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="status" value="1" id="status_<?php echo $row['product_id']; ?>" <?php echo $row['status'] ? 'checked' : ''; ?>>
                <label class="form-check-label" for="status_<?php echo $row['product_id']; ?>">Active</label>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    <?php endwhile; ?>
</div>
<?php include 'footer.php'; ?>