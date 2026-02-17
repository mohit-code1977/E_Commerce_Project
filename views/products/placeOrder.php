<?php
require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/auth/session.php';

$user = $_SESSION['name'] ?? "";
$email = $_SESSION['email'] ?? "";
$user_id = $_COOKIE['loginID'] ?? "";
  

/* --------------Find Total Price--------------- */ 
function calculateTotalPrice(int $user_id, mysqli $conn): float {
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(c.qty * p.price), 0) AS total_price
        FROM cart c
        JOIN products p ON p.id = c.product_id
        WHERE c.user_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return (float)($row['total_price'] ?? 0);
}

$totalPrice = calculateTotalPrice((int)$user_id, $conn);


?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
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
      <form action="<?= BASE_URL ?>views/products/confirmOrder.php" method="POST">
          <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" placeholder="Enter your name" value="<?= $user ?>" />
          </div>

          <div class="form-group">
            <label>Mobile Number</label>
            <input type="text" name="phone_no" placeholder="Enter mobile number" value="1234567890" />
          </div>

          <div class="form-group">
            <label>Delivery Address</label>
            <textarea name="delivery_address" placeholder="House no, Street, Area, City, Pincode">Navranpura, Near River Front</textarea>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>City</label>
              <input type="text" name="city" value="Ahmedabad" />
            </div>

            <div class="form-group">
              <label>Pincode</label>
              <input type="text" name="pincode" value="3800001" />
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
              <span>Items Total Price</span>
              <span><?= $totalPrice ?></span>
            </div>
            <div class="row">
              <span>Delivery Charge</span>
              <span>₹ 0</span>
            </div>
            <div class="row total">
              <span>Total Payable</span>
              <span><?= $totalPrice ?></span>
            </div>
          </div>

          <!-- Actions -->
          <div class="actions">
            <a href="/task/views/products/cart.php" class="btn secondary">Back to Cart</a>
            <button type="submit" name="confirm_order" value="1" class="btn primary">Confirm Order</button>
          </div>

        </form>
      </div>
    </div>
  </div>

</body>

</html>