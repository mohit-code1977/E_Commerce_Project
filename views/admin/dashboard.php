<?php
require "../../config/config.php";
require BASE_PATH . "/config/db.php";

$totalProducts     = $conn->query("SELECT COUNT(id) as c FROM products")->fetch_assoc()['c'];
$totalCategories   = $conn->query("SELECT COUNT(id) as c FROM categories")->fetch_assoc()['c'];
$totalOrders       = $conn->query("SELECT COUNT(id) as c FROM orders")->fetch_assoc()['c'];
$totalUsers        = $conn->query("SELECT COUNT(id) as c FROM users")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>icon.png">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/views/admin/dark-theme.css">
</head>
<body>

    <?php include "layout/sidebar.php"; ?>

    <div class="main">

        <div class="topbar">
            <h1 class="msg">
                <i class="fa fa-gauge" style="color:var(--accent2)"></i>
                Admin : <span class="greeting">Mohit Sanodiya</span>
            </h1>
           
        </div>

        <!-- Stat Cards -->
        <div class="cards">
            <div class="card" onclick="location.href='<?= BASE_URL ?>/views/admin/products.php'">
                <h3>📦 Total Products</h3>
                <p><?= $totalProducts ?></p>
            </div>
            <div class="card" onclick="location.href='<?= BASE_URL ?>/views/admin/categories.php'">
                <h3>🏷️ Total Categories</h3>
                <p><?= $totalCategories ?></p>
            </div>
            <div class="card" onclick="location.href='<?= BASE_URL ?>/views/admin/orders.php'">
                <h3>🛒 Total Orders</h3>
                <p><?= $totalOrders ?></p>
            </div>
            <div class="card">
                <h3>👤 Total Users</h3>
                <p><?= $totalUsers ?></p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="actions">
            <a href="<?= BASE_URL ?>/views/admin/add_product.php" class="btn">
                <i class="fa fa-plus"></i> <span>Add Product</span>
            </a>
            <a href="<?= BASE_URL ?>/views/admin/categories.php" class="btn">
                <i class="fa fa-tag"></i> <span>Add Category</span>
            </a>
            <a href="<?= BASE_URL ?>/views/admin/import_csv.php" class="btn">
                <i class="fa fa-upload"></i> <span>Import CSV</span>
            </a>
        </div>

    </div>
</body>
</html>