<?php
require "../../config/config.php";
require BASE_PATH . "/config/db.php";

/* get categories */
$stmt = $conn->prepare("SELECT id,name FROM categories ORDER BY name ASC");
$stmt->execute();
$categories = $stmt->get_result();

/* get products */
$stmt = $conn->prepare("SELECT * FROM products");
$stmt->execute();
$products = $stmt->get_result();

$category_id = $_POST['category_id'] ?? "";

if ($_SERVER['REQUEST_METHOD'] == "POST" && !empty($category_id)) {

    $stmt = $conn->prepare("
SELECT * FROM products
WHERE category_id=?
OR category_id IN (SELECT id FROM categories WHERE parent_id=?)
OR category_id IN (
SELECT id FROM categories
WHERE parent_id IN (SELECT id FROM categories WHERE parent_id=?)
)");

    $stmt->bind_param("iii", $category_id, $category_id, $category_id);
    $stmt->execute();
    $products = $stmt->get_result();
}
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

                <form method="POST">

                    <select name="category_id">

                        <option value="">All Categories</option>

                        <?php foreach ($categories as $cat) { ?>

                            <option value="<?= $cat['id'] ?>"
                                <?= $category_id == $cat['id'] ? 'selected' : '' ?>>

                                <?= htmlspecialchars($cat['name']) ?>

                            </option>

                        <?php } ?>

                    </select>

                    <button class="search-btn">
                        <i class="fa fa-search"></i>
                    </button>

                </form>

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

                        <?php foreach ($products as $product) { ?>

                            <tr>

                                <td><input type="checkbox"></td>

                                <td>
                                    <img src="<?= BASE_URL . $product['image'] ?>">
                                </td>

                                <td><?= $product['name'] ?></td>

                                <td><?= $product['category_id'] ?></td>

                                <td>₹<?= $product['price'] ?></td>
                            </tr>

                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>