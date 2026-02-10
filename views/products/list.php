<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/config.php';
session_start();

$catId = (int)($_GET['cat'] ?? $_SESSION['catId']);
if ($catId <= 0) {
    echo $catId . "<br><br>";
    die("Invalid category");
}  

$_SESSION['catId'] = $catId;


/*-----------Excute the sql query for getting products---------------*/ 
$result = $conn->query("SELECT id, name, price, image FROM products WHERE category_id = '$catId'");

// Store products in array 
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}


$cartCount = 0;

if(isset($_SESSION['cart_count'])){
    $cartCount = $_SESSION['cart_count'];
}

?>


<!-------- HTML Code Start HERE ------->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>List of Products</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>icon.png">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>views/products/list.css">
</head>

<body>
   <div id="nav">
  <div class="nav-left">
    <h1>Products</h1>
    <p>Browse and add products to your cart</p>
  </div>

  <div class="nav-right">
    <div class="nav-link"><a href="<?= BASE_URL ?>views/navigation/navigation.php">Go To Dashboard</a></div>

    <a class="nav-link cart-link" href="<?= BASE_URL ?>views/products/cart.php">
      <i class="ri-shopping-cart-2-line"></i>
      <span>Cart</span>
      <span class="cart-badge"><?= (int)($cartCount ?? 0) ?></span>
    </a>

    <a class="nav-link logout-link" href="<?= BASE_URL ?>auth/logout.php">
      <i class="ri-logout-box-r-line"></i>
      <span>Logout</span>
    </a>
  </div>
</div>


    <div class="products">
        <?php if (empty($products)): ?>
            <p>No products found in this category.</p>
        <?php else: ?>
            <?php foreach ($products as $p): ?>
                <div class="product-card">
                    <img src="<?= BASE_URL . htmlspecialchars($p['image']) ?>"
                        alt="<?= htmlspecialchars($p['name']) ?>"
                        class="product-img" />
                    <h4><?= htmlspecialchars($p['name']) ?></h4>
                    <p>₹ <?= number_format($p['price'], 2) ?></p>

                    <!-- <form method="post" action="<?= BASE_URL ?>views/products/add_to_cart.php"> -->
                    <form method="POST" action="<?= BASE_URL ?>views/products/add_to_cart.php">
                        <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                        <button type="submit">Add to Cart</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</body>

</html>