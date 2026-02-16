<?php
require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/helpers/storage.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$productId = (int)($_POST['product_id'] ?? 0);
// print("Print Product ID : $productId <br><br>  ");
// print("Print SESSION : <br> ");
// print_r($_SESSION);
// print("<br><br>");

// print("Print COOKIES : <br> ");
// print_r($_COOKIE);
// print("<br><br>");

// print("Print POST : <br> ");
// print_r($_POST);

// print("<br><br>Print GET : <br> ");
// print_r($_GET);
// print("<br><br>");
// EXIT;


$catId = $_SESSION['catId'] ?? 0;

if ($productId <= 0) {
    die("Invalid product");
}

/*----------Fetch product info-----------*/
$stmt = $conn->prepare("SELECT id, image, name, price FROM products WHERE id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    die("Product not found");
}

/* ===================== CART WRITE LOGIC ===================== */

$mode = Consent::mode();

// Logged-in user → DB cart
if ($mode === 'db') {
    $userId = $_SESSION['id'];

    $stmt = $conn->prepare("
        INSERT INTO cart (user_id, product_id, qty)
        VALUES (?, ?, 1)
        ON DUPLICATE KEY UPDATE qty = qty + 1
    ");
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
}

// Guest with cookies/session consent
elseif ($mode === 'cookies' || $mode === 'session') {
    $cart = Storage::get('cart') ?? [];

    if (isset($cart[$productId])) {
        $cart[$productId]['qty'] += 1;
    } else {
        $cart[$productId] = [
            'product_id'   => $data['id'],
            'productImg'   => $data['image'],
            'productName'  => $data['name'],
            'productPrice' => (float)$data['price'],
            'qty'          => 1
        ];
    }

    Storage::set('cart', $cart);
}


/*----------Redirect-----------*/
header("Location: " . BASE_URL . "views/products/list.php?cat=" . (int)$_SESSION['catId']);
exit;
