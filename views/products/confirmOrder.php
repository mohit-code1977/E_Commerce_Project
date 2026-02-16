<?php
require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/auth/session.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$userID = (int)($_SESSION['id'] ?? 0);
if ($userID <= 0) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['confirm_order'])) {
    header("Location: " . BASE_URL . "views/products/cart.php");
    exit;
}

// ---- sanitize inputs ----
$name = trim($_POST['name'] ?? '');
$phone_no = trim($_POST['phone_no'] ?? '');
$address = trim($_POST['delivery_address'] ?? '');
$city = trim($_POST['city'] ?? '');
$pincode = trim($_POST['pincode'] ?? '');

// ---- basic validation ----
if ($name === '' || $phone_no === '' || $address === '' || $city === '' || $pincode === '') {
    die("Invalid delivery details");
}

// ---- start transaction ----
$conn->begin_transaction();

try {

    // Get cart items with price (DB is source of truth)
    $stmt = $conn->prepare("
        SELECT c.product_id, c.qty, p.price
        FROM cart c
        JOIN products p ON p.id = c.product_id
        WHERE c.user_id = ?
    ");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        throw new Exception("Cart is empty");
    }

    $cartItems = [];
    $totalPrice = 0;

    while ($row = $res->fetch_assoc()) {
        $cartItems[] = $row;
        $totalPrice += $row['qty'] * $row['price'];
    }

    //  Create order
    $orderStmt = $conn->prepare("
        INSERT INTO orders (user_id, name, phone_no, address, city, pincode, total_amount)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $orderStmt->bind_param(
        "isssssd",
        $userID,
        $name,
        $phone_no,
        $address,
        $city,
        $pincode,
        $totalPrice
    );
    $orderStmt->execute();

    $orderId = $orderStmt->insert_id;

    // Insert order items
    $itemStmt = $conn->prepare("
        INSERT INTO order_items (order_id, product_id, price, qty)
        VALUES (?, ?, ?, ?)
    ");

    foreach ($cartItems as $item) {
        $itemStmt->bind_param(
            "iidi",
            $orderId,
            $item['product_id'],
            $item['price'],
            $item['qty']
        );
        $itemStmt->execute();
    }

    //  Clear cart
    $clearStmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $clearStmt->bind_param("i", $userID);
    $clearStmt->execute();

    //  Commit transaction
    $conn->commit();

    // Redirect to success page
    header("Location: " . BASE_URL . "views/orders/success.php?order_id=" . $orderId);
    exit;

} catch (Throwable $e) {
    $conn->rollback();
    die("Order failed: " . $e->getMessage());
}
