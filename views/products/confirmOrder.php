<?php
require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/auth/session.php';
require_once BASE_PATH . '/services/orderServices.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Auth
$userID = (int)($_SESSION['id'] ?? 0);
if ($userID <= 0) {
    die("Unauthorized user");
}

// 2. Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['confirm_order'])) {
    header("Location: " . BASE_URL . "views/products/cart.php");
    exit;
}

// 3. Read + sanitize inputs
$name    = trim($_POST['name'] ?? '');
$phone   = trim($_POST['phone_no'] ?? '');
$address = trim($_POST['delivery_address'] ?? '');
$city    = trim($_POST['city'] ?? '');
$pincode = trim($_POST['pincode'] ?? '');

if ($name === '' || $phone === '' || $address === '' || $city === '' || $pincode === '') {
    die("Invalid delivery details");
}

// 4. Call service
$orderService = new OrderService($conn);

try {
    $orderId = $orderService->placeOrder(
        $userID,
        $name,
        $phone,
        $address,
        $city,
        $pincode
    );

    $_SESSION['flash_success'] = "🎉 Order #$orderId placed successfully!";
    header("Location: " . BASE_URL . "views/products/success.php");
    exit;

} catch (Throwable $e) {
    // In prod, log this instead of showing raw error
    die("Order failed: " . $e->getMessage());
}
