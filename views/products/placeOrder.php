<?php
require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/auth/session.php';

$user = $_SESSION['name'];
$email = $_SESSION['email'];
$user_id = $_SESSION['id'];

if($_SERVER['REQUEST_METHOD'] ==="POST"){
    echo "<script>alert('Form Successfully Submitted !');</script>";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Checkout – Place Order</title>
  <link rel="icon" type="image/x-icon" href="/task/icon.png">
  <link rel="stylesheet" href="placeOrder.css">
</head>
<body>
<div id="main">
    
  <div class="checkout-container">
    <div class="checkout-card">

      <h2>Checkout</h2>
      <p class="subtitle">Confirm your delivery details</p>

      <!-- Address Form -->
      <form method="POST">
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" placeholder="Enter your name" value="Mohit" />
        </div>

        <div class="form-group">
          <label>Mobile Number</label>
          <input type="text" placeholder="Enter mobile number" value="9999999999" />
        </div>

        <div class="form-group">
          <label>Delivery Address</label>
          <textarea placeholder="House no, Street, Area, City, Pincode">Bhopal, MP - 462001</textarea>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>City</label>
            <input type="text" value="Bhopal" />
          </div>

          <div class="form-group">
            <label>Pincode</label>
            <input type="text" value="462001" />
          </div>
        </div>

        <!-- Payment -->
        <div class="payment-box">
          <h3>Payment Method</h3>
          <label class="radio">
            <!-- <input type="radio" checked /> -->
            <span>Cash on Delivery</span>
          </label>
        </div>

        <!-- Summary -->
        <div class="order-summary">
          <div class="row">
            <span>Items Total</span>
            <span>₹ 52,999</span>
          </div>
          <div class="row">
            <span>Delivery</span>
            <span>₹ 0</span>
          </div>
          <div class="row total">
            <span>Total Payable</span>
            <span>₹ 52,999</span>
          </div>
        </div>

        <!-- Actions -->
        <div class="actions">
          <a href="/task/views/products/cart.php" class="btn secondary">Back to Cart</a>
          <button name="button" class="btn primary">Confirm Order</button>
        </div>

      </form>
    </div>
  </div>
</div>

</body>
</html>
