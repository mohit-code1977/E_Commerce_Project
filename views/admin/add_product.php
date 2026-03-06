<?php
require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/config/db.php';

$products = "";

/*------------ get products id -------------*/
function getProductCategories($conn){
    $stmt = $conn->prepare("select id, name from categories order by name asc");
    $stmt->execute();
    return $categories = $stmt->get_result();
}
$categories = getProductCategories($conn);


/*------------ get products -------------*/
function getProducts($conn){
    $stmt = $conn->prepare("select * from products");
    $stmt->execute();
    return $products = $stmt->get_result();
}
$products = getProducts($conn);


/**---------- products search filter ----------**/
$category_id = $_POST['category_id'] ?? "";

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    if (!empty($category_id)) {

        $stmt = $conn->prepare("
        SELECT * FROM products 
        WHERE category_id = ?
        OR category_id IN (
            SELECT id FROM categories WHERE parent_id = ?
        )
        OR category_id IN (
            SELECT id FROM categories 
            WHERE parent_id IN (SELECT id FROM categories WHERE parent_id = ?)
        )
        ");

        $stmt->bind_param("iii", $category_id, $category_id, $category_id);
        $stmt->execute();
        $products = $stmt->get_result();
    }   
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Products Panel</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>icon.png">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/views/admin/add_products.css">
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>Products</h2>
            <div class="actions">
                <button class="btn light"><i class="fa fa-upload"></i> Import</button>
                <button class="btn light"><i class="fa fa-download"></i> Export</button>
                <a class="btn green" href="<?= BASE_URL ?>/views/admin/add_product.php">Create Product</a>
            </div>
        </div>

        <div class="card">
            <div class="filters">
                <form method="POST">
                    <select name="category_id">
                        <option value="" <?= $category_id == "" ? 'sel  ected' : '' ?>>
                            All Categories
                        </option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>"
                                <?= $category_id == $category['id'] ? 'selected' : '' ?>>

                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>



                    <button class="search-btn"><i class="fa fa-search"></i></button>
                </form>
            </div>

            <?php if (!empty($products)) { ?>
                <table>
                    <thead>
                        <tr>
                            <th></th>
                            <th></th>
                            <th>Name</th>
                            <th>Category ID</th>
                            <th>Price</th>
                        </tr>
                    </thead>

                    <!-- printing all products after fetched from db -->
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><input type="checkbox"></td>
                                <td><img src="<?= BASE_URL . $product['image'] ?>"></td> <!-- show products img --->
                                <td><?= $product['name'] ?></td>
                                <td><?= $product['category_id'] ?></td>
                                <td><?= $product['price'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <!-- <?php exit; ?> -->
                    </tbody>
                </table>
            <?php } else {
                echo "No Products Found";
            } ?>
        </div>

    </div>
</body>

</html>