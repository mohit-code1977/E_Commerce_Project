<?php
require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/helpers/storage.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$productId = (int)($_GET['id'] ?? 0);
if ($productId <= 0) {
    header("Location: " . BASE_URL . "views/products/cart.php");
    exit;
}

$mode = Consent::mode();

/* -------- Logged-in → DB -------- */
if ($mode === 'db') {
    $userID = (int)$_SESSION['id'];

    $stmt = $conn->prepare("UPDATE cart SET qty = qty + 1 WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $userID, $productId);
    $stmt->execute();
}

/* -------- Guest → Cookies / Session -------- */
elseif ($mode === 'cookies' || $mode === 'session') {
    $cart = Storage::get('cart') ?? [];

    if (isset($cart[$productId])) {
        $cart[$productId]['qty'] += 1;
        Storage::set('cart', $cart);
    }
}

/* -------- No consent → do nothing -------- */

header("Location: " . BASE_URL . "views/products/cart.php");
exit;
