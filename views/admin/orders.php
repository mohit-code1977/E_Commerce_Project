<?php
require "../../config/config.php";
require BASE_PATH . "/config/db.php";

$orders = $conn->query("select * from orders");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Products</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/views/admin/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/views/admin/products.css">
</head>

<body>
    <div class="layout">

        <!-- sidebar -->
        <?php include "layout/sidebar.php"; ?>

        <!-- main content -->
        <div class="main">

            <div class="header">

                <h2>Products</h2>

                <a class="btn" href="<?= BASE_URL ?>/views/admin/add_product.php">
                    <i class="fa fa-plus"></i> Add Product
                </a>

            </div>

            <div class="filters">
                    <button class="search-btn">
                        <i class="fa fa-search"></i>
                    </button>
            </div>

            <div class="table-box">

                <table>

                    <thead>

                        <tr>
                            <th></th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php foreach ($orders as $order) { ?>

                            <tr>

                                <td><input type="checkbox"></td>

                                <td>
                                    <img src="<?= BASE_URL . $order['image'] ?>">
                                </td>

                                <td><?= $order['name'] ?></td>

                                <!-- <td><?= $order['category_id'] ?></td>

                                <td>₹<?= $order['price'] ?></td> -->
                            </tr>

                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>