<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/config.php';
session_start();

$catId = (int)($_GET['cat'] ?? 0);
if ($catId <= 0) {
    die("Invalid category");
}

$result = $conn->query("SELECT id, name, price, image FROM products WHERE category_id = '$catId'");

// Store products in array 
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
?>


<!-------- HTML Code Start HERE ------->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products</title>
     <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>icon.png">
    <link rel="stylesheet" href="<?= BASE_URL ?>views/products/products.css">
    <link rel="stylesheet" href="list.css">
</head>
<body>

<h2>Products</h2>

<div class="products">
    <?php if (empty($products)): ?>
        <p>No products found in this category.</p>
    <?php else: ?>3
        <?php foreach ($products as $p): ?>
            <div class="product-card">
                <img  src="<?= BASE_URL . htmlspecialchars($p['image']) ?>" 
                alt="<?= htmlspecialchars($p['name']) ?>" 
                class="product-img"/>
                <h4><?= htmlspecialchars($p['name']) ?></h4>
                <p>₹ <?= number_format($p['price'], 2) ?></p>

                <form method="post" action="<?= BASE_URL ?>views/products/add_to_cart.php">
                    <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                    <button type="submit">Add to Cart</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
