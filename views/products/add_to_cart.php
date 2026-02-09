<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/config.php';
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$userId = $_SESSION['id'];
$productId = (int)($_POST['product_id'] ?? 0);

if ($productId <= 0) {
    die("Invalid product");
}

$stmt = $conn->prepare("
    INSERT INTO cart (user_id, product_id, qty)
    VALUES (?, ?, 1)
    ON DUPLICATE KEY UPDATE qty = qty + 1
");
$stmt->bind_param("ii", $userId, $productId);
$stmt->execute();

// Update cart count
$_SESSION['cart_count'] = ($_SESSION['cart_count'] ?? 0) + 1;

header("Location: " . BASE_URL. "/views/navigation/navigation.php");
exit();
