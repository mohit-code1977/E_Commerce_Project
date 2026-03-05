<?php
require "../../config/db.php";

$productCount = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'];
$categoryCount = $conn->query("SELECT COUNT(*) as total FROM categories")->fetch_assoc()['total'];
$orderCount = $conn->query("SELECT COUNT(*) as total FROM orders")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>icon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f6f9;
        }

        .sidebar {
            height: 100vh;
            background: #1e293b;
            color: white;
            padding-top: 20px;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 12px 20px;
        }

        .sidebar a:hover {
            background: #334155;
        }

        .card-box {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .card-box h3 {
            font-weight: 700;
        }

        .topbar {
            background: white;
            padding: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- SIDEBAR -->
            <div class="col-md-2 sidebar">
                <h4 class="text-center mb-4">Admin Panel</h4>
                <a href="dashboard.php">Dashboard</a>
                <a href="products.php">Products</a>
                <a href="categories.php">Categories</a>
                <a href="upload_csv.php">Upload CSV</a>
                <a href="../../index.php">View Storefront</a>
            </div>
            <!-- MAIN CONTENT -->
            <div class="col-md-10">
                <div class="topbar mb-4">
                    <h4>Dashboard</h4>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card card-box p-4">
                            <h6>Total Products</h6>
                            <h3><?php echo $productCount ?></h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-box p-4">
                            <h6>Total Categories</h6>
                            <h3><?php echo $categoryCount ?></h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-box p-4">
                            <h6>Total Orders</h6>
                            <h3><?php echo $orderCount ?></h3>
                        </div>
                    </div>
                </div>
                <div class="mt-5">
                    <h5>Quick Actions</h5>
                    <a class="btn btn-primary me-2" href="add_product.php">Add Product</a>
                    <a class="btn btn-success me-2" href="categories.php">Manage Categories</a>
                    <a class="btn btn-warning me-2" href="upload_csv.php">Upload CSV</a>
                    <a class="btn btn-dark" href="../../index.php">Go To Storefront</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>