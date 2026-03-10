<?php
require "../../config/config.php";
require BASE_PATH ."/config/db.php";

$totalProducts = $totalCategories = $totalOrders = $totalActivityLogs = 0;

/*----------- getting total count of products -----------*/ 
$totalProducts = $conn->query("select count(id) from products")->fetch_assoc();
$totalCategories = $conn->query("select count(id) from categories")->fetch_assoc();
$totalOrders = $conn->query("select count(id) from orders")->fetch_assoc();
$totalActivityLogs = $conn->query("select count(id) from users")->fetch_assoc();

// exit;


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>icon.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/views/admin/dashboard.css">
</head>
<body>

<!-- side panel -->
    <?php include "layout/sidebar.php"; ?>

    <div class="main">

        <div class="topbar">
            <h1 class="msg">Admin : <p class="greeting">Mohit Sanodiya</p> </h1>
            <div class="top-actions">
                <div class="icon-btn">🔍</div>
                <div class="icon-btn">🔔</div>
            </div>
        </div>

        <div class="cards">
            <div class="card">
                <h3>Total Products</h3>
                <p><?= $totalProducts['count(id)'] ?></p>
            </div>

            <div class="card">
                <h3>Total Categories</h3>
                <p><?= $totalCategories['count(id)'] ?></p>
            </div>

            <div class="card">
                <h3>Total Orders</h3>
                <p><?= $totalOrders['count(id)'] ?></p>
            </div>

            <div class="card">
                <h3>Activity Logs</h3>
                <p><?= $totalActivityLogs['count(id)'] ?></p>
            </div>
        </div>

        <div class="actions">
            <button class="btn">Add Product</button>
            <button class="btn">Add Category</button>
            <button class="btn">Import CSV</button>
            <button class="btn">Go To Store</button>
        </div>

    </div>
</body>

</html>