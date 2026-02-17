<?php
require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/auth/session.php';

// Enable strict MySQL errors during dev
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userID = (int)($_SESSION['id'] ?? 0);
if ($userID <= 0) {
    die("Unauthorized user");
}

// Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['confirm_order'])) {
    header("Location: " . BASE_URL . "views/products/cart.php");
    exit;
}

// Sanitize inputs
$name    = trim($_POST['name'] ?? '');
$phone   = trim($_POST['phone_no'] ?? '');
$address = trim($_POST['delivery_address'] ?? '');
$city    = trim($_POST['city'] ?? '');
$pincode = trim($_POST['pincode'] ?? '');

if ($name === '' || $phone === '' || $address === '' || $city === '' || $pincode === '') {
    die("Invalid delivery details");
}

$conn->begin_transaction();

try {
    // Fetch cart with product snapshot
    $stmt = $conn->prepare("
        SELECT 
            c.product_id,
            c.qty,
            p.name  AS product_name,
            p.price AS product_price,
            p.image AS product_image
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

    $cartItems  = [];
    $totalPrice = 0.0;

    while ($row = $res->fetch_assoc()) {
        $cartItems[] = $row;
        $totalPrice += ((float)$row['product_price'] * (int)$row['qty']);
    }

    // Create order
    $paymentMethod = 'COD';
    $status = 'PLACED';

    $orderStmt = $conn->prepare("
        INSERT INTO orders 
        (user_id, name, phone_no, address, city, pincode, total_amount, payment_method, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $orderStmt->bind_param(
        "isssssdss",
        $userID,
        $name,
        $phone,
        $address,
        $city,
        $pincode,
        $totalPrice,
        $paymentMethod,
        $status
    );
    $orderStmt->execute();

    $orderId = $orderStmt->insert_id;

    // Insert order items
    $itemStmt = $conn->prepare("
        INSERT INTO order_items 
        (order_id, product_id, product_name, product_price, product_image, qty, subtotal)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($cartItems as $item) {
        $subtotal = ((float)$item['product_price'] * (int)$item['qty']);

        $itemStmt->bind_param(
            "iisssid",
            $orderId,
            $item['product_id'],
            $item['product_name'],
            $item['product_price'],
            $item['product_image'],
            $item['qty'],
            $subtotal
        );
        $itemStmt->execute();
    }

    // Clear cart
    $clearStmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $clearStmt->bind_param("i", $userID);
    $clearStmt->execute();

    // Commit
    $conn->commit();

    // Flash success + redirect
    $_SESSION['flash_success'] = "🎉 Order placed successfully! Thank you for shopping with us.";
    header("Location: " . BASE_URL . "views/products/success.php");
    exit;

} catch (Throwable $e) {
    $conn->rollback();
    die("Order failed: " . $e->getMessage());
}
