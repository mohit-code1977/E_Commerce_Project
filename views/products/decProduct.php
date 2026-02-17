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

// print("Print Mode : <br>");
// var_dump($mode);

// print("Print GET Method : <br>");
// $getItems = Storage::get('cart');
// var_dump($getItems);

// exit;


/* -------- Logged-in → DB -------- */
if ($mode === 'db') {
    $userID = (int)$_SESSION['id'];

    // decrease qty
    $stmt = $conn->prepare("UPDATE cart SET qty = qty - 1 WHERE user_id = ? AND product_id = ? AND qty > 1");
    $stmt->bind_param("ii", $userID, $productId);
    $stmt->execute();

    // remove row if qty <= 0 (safety)
    $del = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ? AND qty <= 1");
    $del->bind_param("ii", $userID, $productId);
    $del->execute();
}

/* -------- Guest → Cookies / Session -------- */
elseif ($mode === 'cookies' || $mode === 'session') {
    $cart = Storage::get('cart') ?? [];

    if (isset($cart[$productId])) {
        $cart[$productId]['qty'] -= 1;

        if ($cart[$productId]['qty'] <= 0) {
            unset($cart[$productId]);
        }

        Storage::set('cart', $cart);
    }
}

/* -------- No consent → do nothing -------- */

header("Location: " . BASE_URL . "views/products/cart.php");
exit;
