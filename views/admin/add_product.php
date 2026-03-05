<?php
require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/config/db.php';

$category_id = "";

$products = "";

function getProducts($conn){
   if(!isset($category_id)){
    $stmt = $conn->prepare("select * from products");
    $stmt->execute();
   return $products = $stmt->get_result();
   }
}
$products = getProducts($conn);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF- 8">
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
                <button class="btn green">Create Product</button>
            </div>
        </div>

        <div class="card">
            <div class="filters">
                <input type="text" placeholder="Search for Product">
                <select>
                    <option>All Categories</option>
                    <option>Mobiles</option>
                    <option>Laptops</option>
                </select>

                <select>
                    <option>All Products</option>
                    <option>Available</option>
                    <option>Disabled</option>
                </select>

                <button class="search-btn"><i class="fa fa-search"></i></button>
            </div>

            <?php if(!empty($products)){ ?>
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
                    <?php foreach($products as $product): ?>
                    <tr>
                        <td><input type="checkbox"></td>
                        <td><img src="<?= BASE_URL. $product['image'] ?>"></td> <!-- show products img --->
                        <td><?= $product['name'] ?></td>
                        <td><?= $product['category_id'] ?></td>
                        <td><?= $product['price'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <!-- <?php exit; ?> -->
                </tbody>
            </table>
            <?php }else{
                echo "No Products Found";
            } ?>
        </div>

    </div>
</body>
</html>