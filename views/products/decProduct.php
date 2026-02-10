<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../auth/session.php';

$product_id = $_GET['product_id'];

$sql = "select qty from cart where product_id = $product_id";
$result = $conn->query($sql);
$data = $result->fetch_assoc();
$quantity = $data['qty'];

if ($quantity > 1) {
    $sql1 =  "update cart set qty = $quantity-1 where product_id = $product_id";

    if ($conn->query($sql1)) {
        echo "Database Error : " . $conn->connect_error;
    }

    header("Location:" . BASE_URL . "views/products/cart.php");
    exit();
}
 else {
    $sql1 =  "delete from cart where product_id = $product_id";
    $_SESSION['cart_count'] = 0;
    if ($conn->query($sql1)) {
        echo "Database Error : " . $conn->connect_error;
    }

    header("Location:" . BASE_URL . "views/products/cart.php");
    exit();
}
