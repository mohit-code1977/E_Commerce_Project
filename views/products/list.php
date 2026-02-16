<?php
require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/helpers/storage.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



/* -------- Fetch Category Products -------- */
$catId = (int)($_GET['cat'] ?? 0);

$_SESSION['catId'] = $catId;

$products = [];

if ($catId > 0) {
    $stmt = $conn->prepare("SELECT id, name, price, image FROM products WHERE category_id = ?");
    $stmt->bind_param("i", $catId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $products[] = $row;
    }
}

/* -------- Cart Count (DB + Cookies + Session) -------- */
$cartCount = 0;
$mode = Consent::mode();

if ($mode === 'db') {
    $stmt = $conn->prepare("SELECT SUM(qty) as total FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['id']);
    $stmt->execute();
    $cartCount = (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0);
}
elseif ($mode === 'cookies' || $mode === 'session') {
    $cart = Storage::get('cart') ?? [];
    foreach ($cart as $item) {
        $cartCount += (int)($item['qty'] ?? 0);
    }
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

   <?php if(isset($_COOKIE['loginID'])){?> 
    <a class="nav-link logout-link" href="<?= BASE_URL ?>auth/logout.php">
      <i class="ri-logout-box-r-line"></i>
      <span>Logout</span>
    </a>
   <?php } else{?>
   <a class="nav-link logout-link" href="<?= BASE_URL ?>auth/login.php">
    <i class="ri-login-box-line"></i>
      <span>Login</span>
   </a>
   <?php }?>
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

    <?= include_once(BASE_PATH. "/views/partials/cookie_banner.php"); ?>
</body>

</html>