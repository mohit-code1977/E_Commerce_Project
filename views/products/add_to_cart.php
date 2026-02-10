<?php
require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/auth/session.php';

/*-----------Fetching UserID and ProductID-----------*/ 
$userId = $_SESSION['id'];
$productId = (int)($_POST['product_id'] ?? 0);

//----> Getting Data From DB for This Product
$sql = "select * from products where id=$productId";
$result = $conn->query($sql);
$data = $result->fetch_assoc();

/*-----------Check Product Id:(Empty or not)-----------*/ 
if ($productId <= 0) {
    die("Invalid product");
}

/*-----------Calculating Card Counting-----------*/ 
$cartCount = 0;
if (isset($_SESSION['cart'][$userId])) {
    foreach ($_SESSION['cart'][$userId] as $item) {
        $cartCount += $item['qty'];
    }
}
$_SESSION['cart_count'] = $cartCount;

/*-----------Creting Empty Array For Cart in Session-----------*/ 
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (!isset($_SESSION['cart'][$userId])) {
    $_SESSION['cart'][$userId] = [];
}

/*-----------Apply Logic For Storing Data Into SESSION-----------*/ 
if(isset($_SESSION['cart'][$userId][$productId])){
    $_SESSION['cart'][$userId][$productId]['qty'] += 1;
}
else{
     $_SESSION['cart'][$userId][$productId] = [
        'product_id' => $data['id'],
        'productName' => $data['name'],
        'productPrice' => $data['price'],
        'qty' => 1
    ];
}

/*-----------Move Into Cart Page-----------*/ 
header("Location: " . BASE_URL. "/views/products/cart.php");
exit();
