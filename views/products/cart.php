<?php
require_once __DIR__. '/../../config/config.php';
require_once BASE_PATH. '/config/db.php';
// require_once BASE_PATH. '/auth/session.php';


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart Page</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>icon.png">
    <link rel="stylesheet" href="<?= BASE_URL ?>views/products/cart.css">   
</head>
<body>
    <div id="page">
        <nav id="nav">
            <h1>Shopping Card</h1>
            <h3>Review your selected items</h3>
        </nav>

        <main id="main">
  <div class="cart-card">
    <table>
      <thead>
        <tr>
          <th>Product</th>
          <th>Price</th>
          <th>Quantity</th>
          <th>Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="product-col">
            <img class="thumb" src="<?= BASE_URL ?>uploads/products/iPhone 14 Pro.webp" alt="iPhone 15 Pro">
            <span>iPhone 15 Pro</span>
          </td>
          <td class="price">$899.00</td>
          <td> <a href="
          "></a>1 <a href=""></a></td>
          <td class="subtotal">$899.00</td>
        </tr>
      </tbody>
    </table>

    <div class="cart-footer">
      <div class="total">Total: <span>$899.00</span></div>
      <div class="actions">
        <button class="btn">Continue Shopping</button>
        <button class="btn">Update Cart</button>
        <button class="btn primary">Checkout</button>
      </div>
    </div>
  </div>
</main>
    </div>
</body>
</html>