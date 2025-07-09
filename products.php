<?php
require_once 'connection.php';

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


function getUrgencyLevel($expiryDate) {
    $currentDate = new DateTime();
    $expiry = new DateTime($expiryDate);
    $daysUntilExpiry = $currentDate->diff($expiry)->days;
    
    if ($daysUntilExpiry <= 1) {
        return 'urgent';
    } elseif ($daysUntilExpiry <= 3) {
        return 'high';
    } elseif ($daysUntilExpiry <= 7) {
        return 'medium';
    } elseif ($daysUntilExpiry <= 14) {
        return 'low';
    }
    
    return 'none';
}


$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'expiry_asc';
$search = isset($_GET['search']) ? $_GET['search'] : '';


$productsPerPage = 6;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $productsPerPage;


$query = "SELECT p.*, pc.category_name 
          FROM products p 
          LEFT JOIN product_categories pc ON p.category_id = pc.category_id 
          WHERE p.status = 1 AND p.expiry_date > CURDATE()";

$countQuery = "SELECT COUNT(*) AS total 
               FROM products p 
               LEFT JOIN product_categories pc ON p.category_id = pc.category_id 
               WHERE p.status = 1 AND p.expiry_date > CURDATE()";


if (!empty($search)) {
    $query .= " AND (p.product_name LIKE '%$search%' OR p.description LIKE '%$search%' OR pc.category_name LIKE '%$search%')";
    $countQuery .= " AND (p.product_name LIKE '%$search%' OR p.description LIKE '%$search%' OR pc.category_name LIKE '%$search%')";
}


if (!empty($category_filter)) {
    $query .= " AND p.category_id = '$category_filter'";
    $countQuery .= " AND p.category_id = '$category_filter'";
}

switch($sort_by) {
    case 'expiry_asc':
        $query .= " ORDER BY p.expiry_date ASC";
        break;
    case 'expiry_desc':
        $query .= " ORDER BY p.expiry_date DESC";
        break;
    case 'price_asc':
        $query .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY p.price DESC";
        break;
    case 'name_asc':
        $query .= " ORDER BY p.product_name ASC";
        break;
    default:
        $query .= " ORDER BY p.expiry_date ASC";
}


$query .= " LIMIT $productsPerPage OFFSET $offset";


$products = Database::search($query);
$totalResult = Database::search($countQuery);
$totalRow = $totalResult->fetch_assoc();
$totalProducts = $totalRow['total'];
$totalPages = ceil($totalProducts / $productsPerPage);


$categories = Database::search("SELECT * FROM product_categories WHERE is_active = 1");
?>

    <?php require_once 'header.php'; ?>
    
    <div class="page-header">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="mb-3">
                        <i class="fas fa-shopping-basket me-2"></i>Fresh Products
                    </h1>
                    <p class="lead mb-0">Discover great deals on products nearing expiry - fresh quality at discounted prices!</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container mb-5">
 
        <div class="filters-section">
            <form method="GET" action="products.php">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search Products</label>
                        <input type="text" class="form-control search-input" id="search" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>" placeholder="Search products...">
                    </div>
                    <div class="col-md-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select category-select" id="category" name="category">
                            <option value="">All Categories</option>
                            <?php while($category = $categories->fetch_assoc()): ?>
                                <option value="<?php echo $category['category_id']; ?>" 
                                    <?php echo ($category_filter == $category['category_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="sort" class="form-label">Sort By</label>
                        <select class="form-select sort-select" id="sort" name="sort">
                            <option value="expiry_asc" <?php echo ($sort_by == 'expiry_asc') ? 'selected' : ''; ?>>Expiry Date (Urgent First)</option>
                            <option value="expiry_desc" <?php echo ($sort_by == 'expiry_desc') ? 'selected' : ''; ?>>Expiry Date (Latest First)</option>
                            <option value="price_asc" <?php echo ($sort_by == 'price_asc') ? 'selected' : ''; ?>>Price (Low to High)</option>
                            <option value="price_desc" <?php echo ($sort_by == 'price_desc') ? 'selected' : ''; ?>>Price (High to Low)</option>
                            <option value="name_asc" <?php echo ($sort_by == 'name_asc') ? 'selected' : ''; ?>>Name (A-Z)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn w-100 filter-btn">
                            <i class="fas fa-filter me-2"></i>Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>


        <div class="discount-guide mb-4">
            <h5 class="text-center mb-3">Discount Guide</h5>
            <div class="row text-center">
                <div class="col-6 col-md-3">
                    <span class="badge bg-danger p-2">50% OFF</span>
                    <small class="d-block mt-1">Expires in 1 day</small>
                </div>
                <div class="col-6 col-md-3">
                    <span class="badge bg-warning p-2">30% OFF</span>
                    <small class="d-block mt-1">Expires in 3 days</small>
                </div>
                <div class="col-6 col-md-3">
                    <span class="badge bg-info p-2">20% OFF</span>
                    <small class="d-block mt-1">Expires in 7 days</small>
                </div>
                <div class="col-6 col-md-3">
                    <span class="badge bg-success p-2">10% OFF</span>
                    <small class="d-block mt-1">Expires in 14 days</small>
                </div>
            </div>
        </div>

     
        <div class="d-flex justify-content-between align-items-center mb-3">
            <p class="mb-0">Showing <?php echo $products->num_rows; ?> of <?php echo $totalProducts; ?> products</p>
            <div>
                <span class="me-2">Sort by:</span>
                <span class="badge bg-light text-dark">
                    <?php 
                    $sortNames = [
                        'expiry_asc' => 'Urgent First',
                        'expiry_desc' => 'Latest First',
                        'price_asc' => 'Price Low to High',
                        'price_desc' => 'Price High to Low',
                        'name_asc' => 'Name A-Z'
                    ];
                    echo $sortNames[$sort_by]; 
                    ?>
                </span>
            </div>
        </div>

 
        <div class="row">
            <?php if($products->num_rows > 0): ?>
                <?php while($product = $products->fetch_assoc()): ?>
                    <?php 
                        $discount = calculateDiscount($product['expiry_date']);
                        $urgency = getUrgencyLevel($product['expiry_date']);
                        $originalPrice = $product['price'];
                        $discountedPrice = $originalPrice - ($originalPrice * $discount / 100);
                        
                        $currentDate = new DateTime();
                        $expiry = new DateTime($product['expiry_date']);
                        $daysUntilExpiry = $currentDate->diff($expiry)->days;
                    ?>
                    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                        <div class="card product-card urgency-<?php echo $urgency; ?>">
                            <div class="position-relative">
                                <?php if($product['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="product-image" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                                <?php else: ?>
                                    <div class="product-image bg-light d-flex align-items-center justify-content-center">
                                        <i class="fas fa-image fa-3x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if($discount > 0): ?>
                                    <span class="badge bg-danger discount-badge">-<?php echo $discount; ?>%</span>
                                <?php endif; ?>
                                
                                <span class="quantity-badge"><?php echo $product['quantity_available']; ?> left</span>
                            </div>
                            
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['product_name']); ?></h5>
                                <p class="card-text text-muted small"><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>
                                
                                <?php if($product['category_name']): ?>
                                    <span class="badge badge-category mb-2"><?php echo htmlspecialchars($product['category_name']); ?></span>
                                <?php endif; ?>
                                
                                <div class="price-section">
                                    <?php if($discount > 0): ?>
                                        <span class="original-price">Lkr<?php echo number_format($originalPrice, 2); ?></span>
                                        <span class="discounted-price">Lkr<?php echo number_format($discountedPrice, 2); ?></span>
                                    <?php else: ?>
                                        <span class="regular-price">Lkr<?php echo number_format($originalPrice, 2); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="expiry-info">
                                    <i class="fas fa-clock"></i>
                                    <?php if($daysUntilExpiry == 0): ?>
                                        <span class="text-danger">Expires today!</span>
                                    <?php elseif($daysUntilExpiry == 1): ?>
                                        <span class="text-danger">Expires tomorrow</span>
                                    <?php else: ?>
                                        <span class="text-warning">Expires in <?php echo $daysUntilExpiry; ?> days</span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if($product['weight_grams']): ?>
                                    <small class="text-muted d-block mt-2">
                                        <i class="fas fa-weight"></i> <?php echo $product['weight_grams']; ?>g
                                    </small>
                                <?php endif; ?>
                                
                                <div class="d-grid gap-2 mt-3">
                                    <button class="btn btnsecondary btn-add-to-cart" onclick="addToCart(<?php echo $product['product_id']; ?>)">
                                        <i class="fas fa-shopping-cart me-2"></i> Add to Cart
                                    </button>
                                    <button class="btn btn-buy-now" onclick="buyProduct(<?php echo $product['product_id']; ?>)">
                                        <i class="fas fa-money-bill-wave me-2"></i> Buy
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="no-products">
                        <i class="fas fa-shopping-basket fa-4x text-muted mb-3"></i>
                        <h4>No products found</h4>
                        <p>Try adjusting your search or filter criteria.</p>
                        <a href="products.php" class="btn btn-success mt-2">Reset Filters</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
   
        <?php if($totalPages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if($currentPage > 1): ?>
                    <li class="page-item">
                        <a class="page-link" 
                           href="?<?php echo http_build_query(array_merge($_GET, ['page' => $currentPage - 1])); ?>" 
                           aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php for($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $i == $currentPage ? 'active' : ''; ?>">
                        <a class="page-link" 
                           href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
                
                <?php if($currentPage < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" 
                           href="?<?php echo http_build_query(array_merge($_GET, ['page' => $currentPage + 1])); ?>" 
                           aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>

    <script>
function addToCart(productId) {
    const quantity = 1;
    
    fetch('cart_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add_to_cart&product_id=${productId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showCartAlert('✓ Added to cart!', 'success');
            
            
            const cartCountElements = document.querySelectorAll('.cart-count');
            cartCountElements.forEach(el => {
                el.textContent = data.cart_count;
            });
        } else {
            showCartAlert(data.message || 'Failed to add to cart', 'danger');
        }
    })
    .catch(error => {
        showCartAlert('Failed to add to cart. Please try again.', 'danger');
    });
}

    function buyProduct(productId) {
     
        const quantity = 1;

        fetch('buy_now_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=buy_now&product_id=${productId}&quantity=${quantity}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.redirect_url) {
                window.location.href = data.redirect_url;
            } else {
                showCartAlert(data.message || 'Unable to process buy now', 'danger');
            }
        })
        .catch(error => {
            showCartAlert('An error occurred while processing buy now', 'danger');
        });
    }


    function showCartAlert(message, type = 'danger') {
        let alertContainer = document.getElementById('cart-action-alert');
        if (!alertContainer) {
            alertContainer = document.createElement('div');
            alertContainer.id = 'cart-action-alert';
            document.body.prepend(alertContainer);
        }
        alertContainer.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert" style="position:fixed;top:70px;right:20px;z-index:9999;min-width:250px;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        setTimeout(() => {
            const alert = alertContainer.querySelector('.alert');
            if (alert) alert.classList.remove('show');
        }, 4000);
    }

    document.getElementById('category').addEventListener('change', function() {
        this.form.submit();
    });
    document.getElementById('sort').addEventListener('change', function() {
        this.form.submit();
    });
    </script>
    
    <?php require_once 'footer.php'; ?>
