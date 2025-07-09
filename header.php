<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'connection.php';
?>
<!DOCTYPE html>
<html data-bs-theme="light" lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>No Waste</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">

</head>

<body>
    <nav class="navbar navbar-expand-md" style="background-color: #0e685c;">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="home.php">
                <span class="bs-icon-sm  d-flex justify-content-center align-items-center me-2 bs-icon">
                    <img src="Images/logo.png" width="35" class="d-inline-block align-top" alt="No Waste">
                </span>
                <span style="color: white; font-weight: bold; font-size: 1.5em;">NO WASTE</span>
            </a>
            <button class="navbar-toggler custom-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navcol-1" aria-controls="navcol-1" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navcol-1">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link text-white" href="home.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="products.php">Products</a></li>
                    <?php if (isset($_SESSION['is_seller']) && $_SESSION['is_seller']): ?>
                        <li class="nav-item"><a class="nav-link text-white" href="productsAdding.php">Add Product</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="edit_product.php">Edit My Products</a></li>
                    <?php endif; ?>
                    <li>
                        <a href="cart.php" class="nav-link">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count badge bg-danger">
                                <?php
                                $cart_count = 0;
                                if (isset($_SESSION['user_id'])) {
                                    $user_id = $_SESSION['user_id'];
                                    $count = Database::search("SELECT COUNT(*) as count FROM cart_items WHERE user_id = $user_id");
                                    if ($count) {
                                        $cart_count = $count->fetch_assoc()['count'] ?? 0;
                                    }
                                }
                                echo $cart_count;
                                ?>
                            </span>
                        </a>
                    </li>
                </ul>
                <div class="nav-search">
                    <form id="navbarSearchForm" class="d-flex" method="GET" action="products.php" style="gap: 5px;">
                        <input type="text" placeholder="Search" class="search-input form-control" id="searchInput" name="search" required>
                        <button class="search-btn btn btn-light" id="searchBtn" type="submit">🔎︎</button>
                    </form>
                </div>
                <ul class="navbar-nav ms-auto">
                    <?php if (
                        (isset($_SESSION['username']) || isset($_COOKIE['email']))
                        || (isset($_SESSION['is_seller']) && $_SESSION['is_seller'])
                    ) : ?>
                        <li class="nav-item">
                            <a class="nav-link active text-white" href="#">
                                <?php
                                if (isset($_SESSION['is_seller']) && $_SESSION['is_seller']) {
                                    echo "Welcome, " . htmlspecialchars($_SESSION['seller_name'] ?? $_SESSION['seller_email']);
                                } else {
                                    echo "Welcome, " . (isset($_SESSION['username']) ? $_SESSION['username'] : $_COOKIE['email']);
                                }
                                ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <button class="btn btn-danger text-white" type="button" id="logoutBtn">Logout</button>
                        </li>
                    <?php else : ?>
                        <li class="nav-item">
                            <a href="login.php" class="btn btn-primary text-white me-2" type="button">Login</a>
                        </li>
                        <li class="nav-item">
                            <a href="seller_login.php" class="btn btn-warning text-dark" type="button">Seller Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

    <script>
        // Logout 
        document.addEventListener('DOMContentLoaded', function() {
            const logoutBtn = document.getElementById('logoutBtn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', function() {
                    if (confirm('Are you sure you want to logout?')) {
                        fetch('logout.php', {
                                method: 'POST'
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    window.location.href = 'login.php';
                                } else {
                                    alert('Logout failed. Please try again.');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                // Fallback - redirect anyway
                                window.location.href = 'login.php';
                            });
                    }
                });
            }
        });


        document.addEventListener('DOMContentLoaded', function() {
            const searchForm = document.getElementById('navbarSearchForm');
            const searchInput = document.getElementById('searchInput');
            if (searchForm && searchInput) {
                searchForm.addEventListener('submit', function(e) {
             
                    if (!searchInput.value.trim()) {
                        e.preventDefault();
                        searchInput.focus();
                    }
                   
                });
            }
        });
    </script>
