<?php
require_once 'connection.php';
Database::setupConn();
session_start();

if (!isset($_SESSION['is_seller']) || !$_SESSION['is_seller'] || !isset($_SESSION['seller_email'])) {
    header('Location: seller_login.php');
    exit;
}


$errors = [];
$success = false;
$product_name = '';
$description = '';
$price = '';
$quantity_available = '';
$expiry_date = '';
$manufacturing_date = '';
$weight_grams = '';
$storage_instructions = '';
$category_id = '';


$targetDir = "uploads/";
$imagePath = null;


if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['product_image'];
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
    
        if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
            $errors[] = "Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.";
        }
   
        elseif ($file['size'] > 2097152) { // 2MB
            $errors[] = "File size exceeds maximum limit of 2MB.";
        } else {
     
            $uniqueName = uniqid('product_', true) . '.' . $fileExtension;
            $targetFile = $targetDir . $uniqueName;
            
            if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                $imagePath = $targetFile;
            } else {
                $errors[] = "Failed to upload image.";
            }
        }
    }


    $product_name = trim($_POST['product_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $quantity_available = trim($_POST['quantity_available'] ?? '');
    $expiry_date = trim($_POST['expiry_date'] ?? '');
    $manufacturing_date = trim($_POST['manufacturing_date'] ?? '');
    $weight_grams = trim($_POST['weight_grams'] ?? '');
    $storage_instructions = trim($_POST['storage_instructions'] ?? '');
    $category_id = trim($_POST['category_id'] ?? '');

  
    if (empty($product_name)) {
        $errors[] = "Product name is required.";
    }
    
    if (empty($category_id)) {
        $errors[] = "Category is required.";
    }
    
    if (empty($description)) {
        $errors[] = "Description is required.";
    }
    
    if (empty($price) || !is_numeric($price) || $price <= 0) {
        $errors[] = "Valid price is required.";
    }
    
    if (empty($quantity_available) || !is_numeric($quantity_available) || $quantity_available < 0) {
        $errors[] = "Valid quantity is required.";
    }
    
    if (empty($manufacturing_date)) {
        $errors[] = "Manufacturing date is required.";
    }
    
    if (empty($expiry_date)) {
        $errors[] = "Expiry date is required.";
    }
    
    if (!empty($manufacturing_date) && !empty($expiry_date)) {
        if (strtotime($manufacturing_date) > strtotime($expiry_date)) {
            $errors[] = "Manufacturing date must be before expiry date.";
        }
    }

 
    $seller_email = $_SESSION['seller_email'] ?? null;
    $seller_id = null;
    if ($seller_email) {
        $seller_result = Database::search("SELECT seller_id FROM sellers WHERE email = '$seller_email'");
        if ($seller_result && $row = $seller_result->fetch_assoc()) {
            $seller_id = $row['seller_id'];
        }
    }
    if (!$seller_id) {
        $errors[] = "Seller not found or not logged in.";
    }

  
    if (empty($errors)) {
        $escaped_product_name = Database::$conn->real_escape_string($product_name);
        $escaped_description = Database::$conn->real_escape_string($description);
        $escaped_storage_instructions = Database::$conn->real_escape_string($storage_instructions);
        
        $weight_clause = !empty($weight_grams) ? "'$weight_grams'" : "NULL";
        $storage_clause = !empty($storage_instructions) ? "'$escaped_storage_instructions'" : "NULL";
        $image_clause = $imagePath ? "'" . Database::$conn->real_escape_string($imagePath) . "'" : "NULL";

        $insert_query = "INSERT INTO products (
            category_id, 
            product_name, 
            description, 
            price, 
            quantity_available, 
            expiry_date, 
            manufacturing_date, 
            weight_grams, 
            storage_instructions, 
            image_url, 
            status,
            sellers_seller_id
        ) VALUES (
            '$category_id',
            '$escaped_product_name',
            '$escaped_description',
            '$price',
            '$quantity_available',
            '$expiry_date',
            '$manufacturing_date',
            $weight_clause,
            $storage_clause,
            $image_clause,
            1,
            '$seller_id'
        )";

        try {
            Database::iud($insert_query);
            $success = true;
            $product_name = '';
            $description = '';
            $price = '';
            $quantity_available = '';
            $expiry_date = '';
            $manufacturing_date = '';
            $weight_grams = '';
            $storage_instructions = '';
            $category_id = '';
        } catch (Exception $e) {
            $errors[] = "Failed to add product. Please try again.";
        }
    }
}

$categories = Database::search("SELECT * FROM product_categories WHERE is_active = 1");
?>

<?php require_once 'header.php'; ?>
<div class="container mt-4">
    <div class="form-container">
        <div class="card form-card">
            <div class="form-header">
                <h2 class="mb-0">
                    <i class="fas fa-plus-circle"></i> Add New Product
                </h2>
                <p class="mb-0 mt-2">Fill in the details to add a new product to the inventory</p>
            </div>
            
            <div class="form-body">
                <!-- Success Alert -->
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i>
                        <strong>Success!</strong> Product has been added successfully.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Error Alert -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="productsAdding.php" id="productForm" enctype="multipart/form-data">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" 
                                       class="form-control <?php echo !empty($errors) && empty($product_name) ? 'is-invalid' : ''; ?>" 
                                       id="product_name" 
                                       name="product_name" 
                                       placeholder="Product Name"
                                       value="<?php echo htmlspecialchars($product_name); ?>"
                                       required>
                                <label for="product_name">Product Name <span class="required">*</span></label>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select class="form-select <?php echo !empty($errors) && empty($category_id) ? 'is-invalid' : ''; ?>" 
                                        id="category_id" 
                                        name="category_id" 
                                        required>
                                    <option value="">Choose Category</option>
                                    <?php while($category = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo $category['category_id']; ?>" 
                                                <?php echo ($category_id == $category['category_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['category_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <label for="category_id">Category <span class="required">*</span></label>
                            </div>
                        </div>
                    </div>

                    <div class="form-floating">
                        <textarea class="form-control <?php echo !empty($errors) && empty($description) ? 'is-invalid' : ''; ?>" 
                                  id="description" 
                                  name="description" 
                                  placeholder="Product Description"
                                  style="height: 100px;"
                                  required><?php echo htmlspecialchars($description); ?></textarea>
                        <label for="description">Description <span class="required">*</span></label>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="number" 
                                       step="0.01" 
                                       min="0.01" 
                                       max="999999.99"
                                       class="form-control <?php echo !empty($errors) && empty($price) ? 'is-invalid' : ''; ?>" 
                                       id="price" 
                                       name="price" 
                                       placeholder="Price"
                                       value="<?php echo htmlspecialchars($price); ?>"
                                       required>
                                <label for="price">Price (Lkr) <span class="required">*</span></label>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="number" 
                                       min="0" 
                                       max="999999"
                                       class="form-control <?php echo !empty($errors) && empty($quantity_available) ? 'is-invalid' : ''; ?>" 
                                       id="quantity_available" 
                                       name="quantity_available" 
                                       placeholder="Quantity Available"
                                       value="<?php echo htmlspecialchars($quantity_available); ?>"
                                       required>
                                <label for="quantity_available">Quantity Available <span class="required">*</span></label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="date" 
                                       class="form-control <?php echo !empty($errors) && empty($manufacturing_date) ? 'is-invalid' : ''; ?>" 
                                       id="manufacturing_date" 
                                       name="manufacturing_date" 
                                       placeholder="Manufacturing Date"
                                       value="<?php echo htmlspecialchars($manufacturing_date); ?>"
                                       max="<?php echo date('Y-m-d'); ?>"
                                       required>
                                <label for="manufacturing_date">Manufacturing Date <span class="required">*</span></label>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="date" 
                                       class="form-control <?php echo !empty($errors) && empty($expiry_date) ? 'is-invalid' : ''; ?>" 
                                       id="expiry_date" 
                                       name="expiry_date" 
                                       placeholder="Expiry Date"
                                       value="<?php echo htmlspecialchars($expiry_date); ?>"
                                       min="<?php echo date('Y-m-d'); ?>"
                                       required>
                                <label for="expiry_date">Expiry Date <span class="required">*</span></label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="number" 
                                       min="1" 
                                       max="999999"
                                       class="form-control" 
                                       id="weight_grams" 
                                       name="weight_grams" 
                                       placeholder="Weight (grams)"
                                       value="<?php echo htmlspecialchars($weight_grams); ?>">
                                <label for="weight_grams">Weight (grams)</label>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="file" 
                                       class="form-control" 
                                       id="product_image" 
                                       name="product_image" 
                                       accept="image/*">
                                <label for="product_image">Product Image</label>
                                <small class="text-muted">Max 2MB (JPG, PNG, GIF, WEBP)</small>
                            </div>
                            <!-- Image preview container -->
                            <div class="mt-2" id="image-preview-container" style="display: none;">
                                <img id="image-preview" class="img-thumbnail" style="max-height: 200px;">
                            </div>
                        </div>
                    </div>

                    <div class="form-floating">
                        <textarea class="form-control" 
                                  id="storage_instructions" 
                                  name="storage_instructions" 
                                  placeholder="Storage Instructions"
                                  style="height: 80px;"><?php echo htmlspecialchars($storage_instructions); ?></textarea>
                        <label for="storage_instructions">Storage Instructions</label>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="reset" class="btn btn-reset me-md-2">
                            <i class="fas fa-undo"></i> Reset Form
                        </button>
                        <button type="submit" class="btn btn-submit">
                            <i class="fas fa-plus"></i> Add Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('productForm');
    const manufacturingDate = document.getElementById('manufacturing_date');
    const expiryDate = document.getElementById('expiry_date');
    
    // Image preview for product image upload
    const productImageInput = document.getElementById('product_image');
    const previewContainer = document.getElementById('image-preview-container');
    const previewImage = document.getElementById('image-preview');
    
    productImageInput.addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                previewContainer.style.display = 'block';
            }
            reader.readAsDataURL(this.files[0]);
        } else {
            previewContainer.style.display = 'none';
        }
    });
    
    // Update expiry date minimum when manufacturing date changes
    manufacturingDate.addEventListener('change', function() {
        if (this.value) {
            const nextDay = new Date(this.value);
            nextDay.setDate(nextDay.getDate() + 1);
            expiryDate.min = nextDay.toISOString().split('T')[0];
            
            // Clear expiry date if it's now invalid
            if (expiryDate.value && expiryDate.value <= this.value) {
                expiryDate.value = '';
            }
        }
    });
    
    // Update manufacturing date maximum when expiry date changes
    expiryDate.addEventListener('change', function() {
        if (this.value) {
            const prevDay = new Date(this.value);
            prevDay.setDate(prevDay.getDate() - 1);
            manufacturingDate.max = prevDay.toISOString().split('T')[0];
            
            // Clear manufacturing date if it's now invalid
            if (manufacturingDate.value && manufacturingDate.value >= this.value) {
                manufacturingDate.value = '';
            }
        }
    });
    
  
    form.addEventListener('submit', function(e) {
        let isValid = true;
        const requiredFields = ['product_name', 'category_id', 'description', 'price', 'quantity_available', 'manufacturing_date', 'expiry_date'];
        
        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        

        if (manufacturingDate.value && expiryDate.value) {
            if (new Date(manufacturingDate.value) >= new Date(expiryDate.value)) {
                manufacturingDate.classList.add('is-invalid');
                expiryDate.classList.add('is-invalid');
                isValid = false;
            }
        }
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields correctly.');
        }
    });
    

    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            if (alert.classList.contains('alert-success')) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        });
    }, 5000);
});
</script>

<?php require_once 'footer.php'; ?>