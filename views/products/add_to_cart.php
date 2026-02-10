<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/config.php';
session_start();

if (!isset($_SESSION['id'])) {
    header("Location:" .BASE_URL."auth/login.php");
    exit();
}

// print("<br><br>");
// print_r($_POST);
// print("<br><br>");

$userId = $_SESSION['id'];
$productId = (int)($_POST['product_id'] ?? 0);

// print("<br>-------------------<br>");
// print_r($_SESSION);
// print("<br>-------------------<br>");

$sql = "select * from products where id=$productId";
$result = $conn->query($sql);
$data = $result->fetch_assoc();
print("<br><br>");
print_r($data);
print("<br><br>");
exit();

if ($productId <= 0) {
    die("Invalid product");
}

// Update cart count
$_SESSION['cart_count'] = ($_SESSION['cart_count'] ?? 0) + 1;
if(isset($productId)){

}

header("Location: " . BASE_URL. "/views/navigation/navigation.php");
exit();
