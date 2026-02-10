<?php
require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/auth/session.php';

$userId = $_SESSION['id'];
$productId = (int)($_POST['product_id'] ?? 0);

$catId = $_SESSION['catId'];

if ($productId <= 0) {
    die("Invalid product");
}

// Fetch product
$sql = "SELECT id, image ,name, price FROM products WHERE id = $productId";
$result = $conn->query($sql);
$data = $result->fetch_assoc();


if (!$data) {
    die("Product not found");
}

// Init cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
if (!isset($_SESSION['cart'][$userId])) {
    $_SESSION['cart'][$userId] = [];
}

// Add or increment
if (isset($_SESSION['cart'][$userId][$productId])) {
    $_SESSION['cart'][$userId][$productId]['qty'] += 1;
} else {
    $_SESSION['cart'][$userId][$productId] = [
        'product_id'   => $data['id'],
        'productImg' => $data['image'],
        'productName'  => $data['name'],
        'productPrice' => $data['price'],
        'qty'          => 1
    ];
}

// Recalculate cart_count AFTER update
$cartCount = 0;
foreach ($_SESSION['cart'][$userId] as $item) {
    $cartCount += $item['qty'];
}
$_SESSION['cart_count'] = $cartCount;

header("Location: " . BASE_URL . "views/products/list.php?cat=" . (int)($catId ?? 0));
exit();


// print_r($_SESSION['cart'][$userId]);
