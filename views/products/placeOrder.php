<?php
require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/auth/session.php';

$user = $_SESSION['name'];
$email = $_SESSION['email'];
$user_id = $_SESSION['id'];
$items = $_SESSION['cart'][$user_id];
// print_r($_GET);
// print("<br><br>");
$totalPrice = $_SESSION['totalPrice'];
// echo $totalPrice. "<br><br>";
// print_r($_SESSION);
// exit;

// echo count($items);    


if($_SERVER['REQUEST_METHOD'] ==="POST" && (isset($_POST['confirm_order']))){

/*----------Saving User Delivery Info-----------*/
 function saveUserDeliveryDetails($items, $user_id){
   foreach($items as $item){
    $product_id = $item['product_id'];
    $productQty = $item['qty'];

    $sql = "insert into cart (user_id, product_id, qty) values ('$user_id', '$product_id' , '$productQty' )";
    echo "<br>" .$sql . "<br><br>";
    print_r($item);
    print("<br><br>");
  }
  
 }
  // print("<br><br>");
   print_r($_POST);
   exit();
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
      <form accept="" method="POST">
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" name="name" placeholder="Enter your name" value="<?= $user ?>" />
        </div>

        <div class="form-group">
          <label>Mobile Number</label>
          <input type="text" name="phone_no" placeholder="Enter mobile number" value="9999999999" />
        </div>

        <div class="form-group">
          <label>Delivery Address</label>
          <textarea name="delivery_address" placeholder="House no, Street, Area, City, Pincode">Bhopal, MP - 462001</textarea>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>City</label>
            <input type="text" name="city" value="Bhopal" />
          </div>

          <div class="form-group">
            <label>Pincode</label>
            <input type="text" name="pincode" value="462001" />
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
            <span><?= $totalPrice?></span>
          </div>
          <div class="row">
            <span>Delivery Charge</span>
            <span>₹ 0</span>
          </div>
          <div class="row total">
            <span>Total Payable</span>
            <span><?= $totalPrice?></span>
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
