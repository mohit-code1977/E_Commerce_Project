<?php
require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/auth/session.php';

// IMPORTANT: use the same session key everywhere
$userId = $_SESSION['id'];
$user   = $_SESSION['name'];

$items = [];
$total = 0;
$subTotal = 0;

/*---------------Check Cart Existence------------------*/ 
if(isset($_SESSION['cart'])){
    $items = $_SESSION['cart'][$userId];

    foreach($items as $key => &$item){
        $item['subTotal'] = $item['productPrice'] * $item['qty'];
        $total += $item['subTotal'];    
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>icon.png">
    <link rel="stylesheet" href="<?= BASE_URL ?>views/products/cart.css">
</head>
<body>
<div id="main">

  <div class="nav">
    <h2>Welcome to Your Cart, <span class="username"><?= htmlspecialchars($user) ?></span></h2>
    <div class="nav-links">
      <a href="<?= BASE_URL ?>views/navigation/navigation.php">Home</a>
      <a href="<?= BASE_URL ?>auth/logout.php">Logout</a>
    </div>
  </div>

  <?php if (empty($items)): ?>
      <p class="cart-empty">Your cart is empty.</p>
  <?php else: ?>    
      <table style="text-align: center;">
          <tr>
              <!-- <th>Image</th> -->
              <th>Product</th>
              <th>Price</th>
              <th>Qty</th>
              <th>Subtotal</th>
          </tr>

          <?php foreach ($items as $item): ?>
              <tr>
                  <!-- <td>
                      <img 
                        src="<?= BASE_URL . htmlspecialchars($item['image']) ?>" 
                        alt="<?= htmlspecialchars($item['name']) ?>" 
                        class="cart-img"
                      >
                  </td> -->
                  <td><?= htmlspecialchars($item['productName']) ?></td>
                  <td class="price">₹ <?= number_format($item['productPrice'], 2) ?></td>
                  <td class="qty">
                      <a 
                        href="<?= BASE_URL ?>views/products/decProduct.php?product_id=<?= (int)$item['product_id'] ?>" 
                        class="qty-btn"
                      >−</a>

                      <span class="badge"><?= (int)$item['qty'] ?></span>

                      <a 
                        href="<?= BASE_URL ?>views/products/incProduct.php?product_id=<?= (int)$item['product_id'] ?>" 
                        class="qty-btn"
                      >+</a>
                  </td>
                  <td class="subtotal">₹ <?= number_format($item['subTotal'], 2) ?></td>
              </tr>
          <?php endforeach; ?>

      </table>

      <div class="cart-footer">
    <div class="cart-total">
        Total: ₹ <?= number_format($total, 2) ?>
    </div>
    <a href="<?= BASE_URL ?>views/products/placeOrder.php" class="place_order">
        Place Order
    </a>
</div>

  <?php endif; ?>

</div>
</body>
</html>
