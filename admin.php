<?php
session_start();
require_once 'connection.php';
Database::setupConn();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $resp = ['success' => false];
    if ($_POST['action'] === 'delete_user' && isset($_POST['user_id'])) {
        $user_id = intval($_POST['user_id']);
        Database::iud("DELETE FROM users WHERE user_id = $user_id");
        $resp['success'] = true;
    }
    if ($_POST['action'] === 'delete_seller' && isset($_POST['seller_id'])) {
        $seller_id = intval($_POST['seller_id']);
        Database::iud("DELETE FROM sellers WHERE seller_id = $seller_id");
        $resp['success'] = true;
    }
    if ($_POST['action'] === 'delete_product' && isset($_POST['product_id'])) {
        $product_id = intval($_POST['product_id']);
        Database::iud("DELETE FROM products WHERE product_id = $product_id");
        $resp['success'] = true;
    }
    if ($_POST['action'] === 'logout') {
        session_destroy();
        $resp['success'] = true;
    }
    header('Content-Type: application/json');
    echo json_encode($resp);
    exit;
}

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: admin_login.php');
    exit;
}

// Analytics
$total_users = Database::search("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$total_sellers = Database::search("SELECT COUNT(*) AS total FROM sellers")->fetch_assoc()['total'];
$total_products = Database::search("SELECT COUNT(*) AS total FROM products")->fetch_assoc()['total'];
$total_orders = 0; 


$buyers = Database::search("SELECT * FROM users ORDER BY created_at DESC");


$sellers = Database::search("SELECT * FROM sellers ORDER BY created_at DESC");


$products = Database::search("SELECT p.*, c.category_name, s.first_name, s.last_name 
    FROM products p 
    LEFT JOIN product_categories c ON p.category_id = c.category_id 
    LEFT JOIN sellers s ON p.sellers_seller_id = s.seller_id 
    ORDER BY p.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NoWaste Admin Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-green: #198754;
            --light-green: #d1e7dd;
            --dark-green: #0f5132;
            --accent-green: #20c997;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: -280px;
            width: 280px;
            height: 100vh;
            background: linear-gradient(135deg, var(--primary-green), var(--dark-green));
            transition: left 0.3s ease;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar.active {
            left: 0;
        }
        
        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            color: white;
        }
        
        .sidebar-header h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .sidebar-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.8;
            font-size: 0.9rem;
        }
        
        .sidebar-menu {
            padding: 1rem 0;
        }
        
        .menu-item {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: white;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .menu-item:hover {
            background: rgba(255,255,255,0.1);
            border-left-color: var(--accent-green);
        }
        
        .menu-item.active {
            background: rgba(255,255,255,0.15);
            border-left-color: white;
        }
        
        .menu-item i {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            margin-left: 0;
            min-height: 100vh;
            background: #f8f9fa;
            transition: margin-left 0.3s ease;
        }
        
        .header {
            background: white;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .content-area {
            padding: 2rem 1.5rem;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: none;
            z-index: 999;
        }
        
        .overlay.active {
            display: block;
        }
        
        .product-img-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .search-bar {
            position: relative;
        }
        
        .search-bar i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            z-index: 10;
        }
        
        .search-bar input {
            padding-left: 2.5rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid #e9ecef;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-green);
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #6c757d;
            font-weight: 500;
        }
        
        .stat-icon {
            font-size: 2rem;
            color: var(--accent-green);
            margin-bottom: 1rem;
        }
        
        @media (min-width: 768px) {
            .sidebar {
                left: 0;
            }
            
            .main-content {
                margin-left: 280px;
            }
            
            .overlay {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                                <span class="bs-icon-sm   me-2 bs-icon">
                    <img src="Images/logo.png" width="35" class="d-inline-block align-top" alt="No Waste">
                </span>
                <span style="color: white; font-weight: bold; font-size: 1.5em;">NO WASTE</span>
                <p>Welcome, Admin</p>
            </div>
            <div class="sidebar-menu">
                <div class="menu-item active" data-tab="analytics">
                    <i class="fas fa-chart-line"></i>
                    <span>Analytics</span>
                </div>
                <div class="menu-item" data-tab="buyers">
                    <i class="fas fa-users"></i>
                    <span>Buyer Accounts</span>
                </div>
                <div class="menu-item" data-tab="sellers">
                    <i class="fas fa-store"></i>
                    <span>Seller Accounts</span>
                </div>
                <div class="menu-item" data-tab="products">
                    <i class="fas fa-box"></i>
                    <span>Product Management</span>
                </div>
                <div class="menu-item" id="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <button class="btn btn-outline-success d-md-none me-3" id="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="h3 mb-0 text-success" id="page-title">Analytics</h1>
                </div>
                <div class="d-flex align-items-center">
                    <span class="badge bg-success fs-6">Admin Panel</span>
                </div>
            </div>

            <!-- Content Area -->
            <div class="content-area">
                <!-- Analytics Tab -->
                <div class="tab-content active" id="analytics-tab">
                    <div class="row mb-4">
                        <div class="col-12">
                            <h2 class="text-success mb-4">
                                <i class="fas fa-chart-line me-2"></i>Platform Analytics
                            </h2>
                        </div>
                    </div>
                    
                    <div class="row g-4">
                        <div class="col-6 col-lg-3">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stat-number"><?php echo $total_users; ?></div>
                                <div class="stat-label">Total Buyers</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-store"></i>
                                </div>
                                <div class="stat-number"><?php echo $total_sellers; ?></div>
                                <div class="stat-label">Total Sellers</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div class="stat-number"><?php echo $total_products; ?></div>
                                <div class="stat-label">Total Products</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <div class="stat-number"><?php echo $total_orders; ?></div>
                                <div class="stat-label">Total Orders</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Buyers Tab -->
                <div class="tab-content" id="buyers-tab">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h2 class="text-success mb-0">
                                <i class="fas fa-users me-2"></i>Buyer Accounts
                            </h2>
                        </div>
                        <div class="col-md-6">
                            <div class="search-bar">
                                <i class="fas fa-search"></i>
                                <input type="text" class="form-control" placeholder="Search buyers..." onkeyup="filterTable('buyers-table', this.value)">
                            </div>
                        </div>
                    </div>
                    
                    <div class="card border-0 shadow-sm">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="buyers-table">
                                <thead class="table-success">
                                    <tr>
                                        <th class="fw-semibold">Name</th>
                                        <th class="fw-semibold">Email</th>
                                        <th class="fw-semibold">Joined</th>
                                        <th class="fw-semibold">Status</th>
                                        <th class="fw-semibold">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($buyer = $buyers->fetch_assoc()): ?>
                                    <tr data-id="<?php echo $buyer['user_id']; ?>">
                                        <td class="fw-medium"><?php echo htmlspecialchars($buyer['first_name'] . ' ' . $buyer['last_name']); ?></td>
                                        <td class="text-muted"><?php echo htmlspecialchars($buyer['email']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($buyer['created_at'])); ?></td>
                                        <td>
                                            <span class="badge <?php echo ($buyer['is_active'] ? 'bg-success' : 'bg-secondary'); ?>">
                                                <?php echo $buyer['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-outline-danger btn-sm btn-delete-user" title="Delete">
                                                <i class="fas fa-trash me-1"></i>Remove
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Sellers Tab -->
                <div class="tab-content" id="sellers-tab">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h2 class="text-success mb-0">
                                <i class="fas fa-store me-2"></i>Seller Accounts
                            </h2>
                        </div>
                        <div class="col-md-6">
                            <div class="search-bar">
                                <i class="fas fa-search"></i>
                                <input type="text" class="form-control" placeholder="Search sellers..." onkeyup="filterTable('sellers-table', this.value)">
                            </div>
                        </div>
                    </div>
                    
                    <div class="card border-0 shadow-sm">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="sellers-table">
                                <thead class="table-success">
                                    <tr>
                                        <th class="fw-semibold">Name</th>
                                        <th class="fw-semibold">Email</th>
                                        <th class="fw-semibold">Phone</th>
                                        <th class="fw-semibold">Joined</th>
                                        <th class="fw-semibold">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($seller = $sellers->fetch_assoc()): ?>
                                    <tr data-id="<?php echo $seller['seller_id']; ?>">
                                        <td class="fw-medium"><?php echo htmlspecialchars($seller['first_name'] . ' ' . $seller['last_name']); ?></td>
                                        <td class="text-muted"><?php echo htmlspecialchars($seller['email']); ?></td>
                                        <td><?php echo htmlspecialchars($seller['phone']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($seller['created_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-outline-danger btn-sm btn-delete-seller" title="Delete">
                                                <i class="fas fa-trash me-1"></i>Remove
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Products Tab -->
                <div class="tab-content" id="products-tab">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h2 class="text-success mb-0">
                                <i class="fas fa-box me-2"></i>Product Management
                            </h2>
                        </div>
                        <div class="col-md-6">
                            <div class="search-bar">
                                <i class="fas fa-search"></i>
                                <input type="text" class="form-control" placeholder="Search products..." onkeyup="filterTable('products-table', this.value)">
                            </div>
                        </div>
                    </div>
                    
                    <div class="card border-0 shadow-sm">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="products-table">
                                <thead class="table-success">
                                    <tr>
                                        <th class="fw-semibold">Image</th>
                                        <th class="fw-semibold">Name</th>
                                        <th class="fw-semibold">Category</th>
                                        <th class="fw-semibold">Seller</th>
                                        <th class="fw-semibold">Price</th>
                                        <th class="fw-semibold">Quantity</th>
                                        <th class="fw-semibold">Expiry</th>
                                        <th class="fw-semibold">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($product = $products->fetch_assoc()): ?>
                                    <tr data-id="<?php echo $product['product_id']; ?>">
                                        <td>
                                            <?php if (!empty($product['image_url'])): ?>
                                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="product-img-thumb" alt="Product">
                                            <?php else: ?>
                                                <div class="bg-light d-flex align-items-center justify-content-center product-img-thumb">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-medium"><?php echo htmlspecialchars($product['product_name']); ?></td>
                                        <td>
                                            <span class="badge bg-success bg-opacity-25 text-success">
                                                <?php echo htmlspecialchars($product['category_name']); ?>
                                            </span>
                                        </td>
                                        <td class="text-muted"><?php echo htmlspecialchars($product['first_name'] . ' ' . $product['last_name']); ?></td>
                                        <td class="fw-medium text-success">$<?php echo htmlspecialchars($product['price']); ?></td>
                                        <td><?php echo htmlspecialchars($product['quantity_available']); ?></td>
                                        <td><?php echo htmlspecialchars($product['expiry_date']); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="edit_product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-outline-success btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-outline-danger btn-sm btn-delete-product" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Overlay for mobile -->
        <div class="overlay" id="overlay"></div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar tab switching
        document.querySelectorAll('.menu-item[data-tab]').forEach(function(item) {
            item.addEventListener('click', function() {
                document.querySelectorAll('.menu-item').forEach(i => i.classList.remove('active'));
                this.classList.add('active');
                document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
                document.getElementById(this.dataset.tab + '-tab').classList.add('active');
                document.getElementById('page-title').textContent = this.textContent.trim();
                
                // Close sidebar on mobile after selection
                if (window.innerWidth < 768) {
                    document.getElementById('sidebar').classList.remove('active');
                    document.getElementById('overlay').classList.remove('active');
                }
            });
        });

        // Mobile menu toggle
        document.getElementById('menu-toggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('overlay').classList.toggle('active');
        });
        
        document.getElementById('overlay').addEventListener('click', function() {
            document.getElementById('sidebar').classList.remove('active');
            this.classList.remove('active');
        });

        // Logout
        document.getElementById('logout-btn').addEventListener('click', function() {
            if (confirm('Are you sure you want to logout?')) {
                fetch('admin.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=logout'
                }).then(() => {
                    window.location.href = 'admin_login.php';
                });
            }
        });

 
        function filterTable(tableId, value) {
            value = value.toLowerCase();
            const rows = document.querySelectorAll(`#${tableId} tbody tr`);
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(value) ? '' : 'none';
            });
        }


        document.querySelectorAll('.btn-delete-user').forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('tr');
                const userId = row.getAttribute('data-id');
                if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                    fetch('admin.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: 'action=delete_user&user_id=' + encodeURIComponent(userId)
                    }).then(res => res.json()).then(data => {
                        if (data.success) {
                            row.remove();
                            // Update analytics if on analytics tab
                            updateAnalytics();
                        }
                    });
                }
            });
        });


        document.querySelectorAll('.btn-delete-seller').forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('tr');
                const sellerId = row.getAttribute('data-id');
                if (confirm('Are you sure you want to delete this seller? All their products will also be deleted.')) {
                    fetch('admin.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: 'action=delete_seller&seller_id=' + encodeURIComponent(sellerId)
                    }).then(res => res.json()).then(data => {
                        if (data.success) {
                            row.remove();
                            updateAnalytics();
                        }
                    });
                }
            });
        });

  
        document.querySelectorAll('.btn-delete-product').forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('tr');
                const productId = row.getAttribute('data-id');
                if (confirm('Are you sure you want to delete this product?')) {
                    fetch('admin.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: 'action=delete_product&product_id=' + encodeURIComponent(productId)
                    }).then(res => res.json()).then(data => {
                        if (data.success) {
                            row.remove();
                            updateAnalytics();
                        }
                    });
                }
            });
        });


        function updateAnalytics() {
            // Updatingthe stat numbers after deletions
            const buyersCount = document.querySelectorAll('#buyers-table tbody tr').length;
            const sellersCount = document.querySelectorAll('#sellers-table tbody tr').length;
            const productsCount = document.querySelectorAll('#products-table tbody tr').length;
            
            // Update the display numbers...
            document.querySelectorAll('.stat-number')[0].textContent = buyersCount;
            document.querySelectorAll('.stat-number')[1].textContent = sellersCount;
            document.querySelectorAll('.stat-number')[2].textContent = productsCount;
        }
    </script>
</body>
</html>