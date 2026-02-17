<?php
require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/auth/session.php';

$userID = (int)($_SESSION['id'] ?? $_COOKIE['loginID']);
$orderId = (int)($_GET['id'] ?? 0);

if ($userID <= 0 || $orderId <= 0) {
    die("Unauthorized access");
}

/* -------- Fetch Order -------- */
$stmt = $conn->prepare("
    SELECT id, total_amount, status, created_at, payment_method, address, city, pincode
    FROM orders
    WHERE id = ? AND user_id = ?
");
$stmt->bind_param("ii", $orderId, $userID);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Order not found");
}

/* -------- Fetch Items -------- */
$items = [];
$stmtItems = $conn->prepare("
    SELECT product_name, product_image, product_price, qty
    FROM order_items
    WHERE order_id = ?
");
$stmtItems->bind_param("i", $orderId);
$stmtItems->execute();
$resItems = $stmtItems->get_result();

while ($row = $resItems->fetch_assoc()) {
    $items[] = $row;
}

$status = strtolower($order['status']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Order #<?= $orderId ?> Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?= BASE_URL ?>icon.png">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background: radial-gradient(circle at top, #1e293b 0%, #020617 70%);
            color: #e5e7eb;
            padding: 40px 16px;
            font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
        }

        .container {
            max-width: 1100px;
            margin: auto;
        }

        /* Card */
        .card {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.04));
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.45);
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
        }

        .badge {
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        .badge.placed {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
        }

        .badge.shipped {
            background: rgba(56, 189, 248, 0.15);
            color: #38bdf8;
        }

        .badge.delivered {
            background: rgba(45, 212, 191, 0.15);
            color: #2dd4bf;
        }

        .badge.cancelled {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
        }

        /* Tracker */
        .tracker {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
        }

        .step {
            flex: 1;
            text-align: center;
            font-size: 12px;
            position: relative;
            color: #9ca3af;
        }

        .step::after {
            content: "";
            position: absolute;
            top: 50%;
            right: -50%;
            width: 100%;
            height: 2px;
            background: rgba(255, 255, 255, 0.15);
        }

        .step:last-child::after {
            display: none;
        }

        .step.active {
            color: #22c55e;
            font-weight: 700;
        }

        /* Items */
        .item {
            display: flex;
            gap: 12px;
            margin-bottom: 12px;
        }

        .item img {
            width: 64px;
            height: 64px;
            border-radius: 10px;
            object-fit: cover;
        }

        .item h4 {
            margin: 0;
            font-size: 15px;
        }

        .item p {
            margin: 0;
            font-size: 13px;
            color: #9ca3af;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            padding: 10px 16px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 13px;
        }
    </style>
</head>

<body>
    <div class="container">

        <div class="card header">
            <h1>Order #<?= $orderId ?></h1>
            <span class="badge <?= $status ?>"><?= strtoupper($order['status']) ?></span>
        </div>

        <!-- Tracker -->
        <div class="card">
            <div class="tracker">
                <div class="step <?= in_array($status, ['placed', 'shipped', 'delivered']) ? 'active' : '' ?>">Placed</div>
                <div class="step <?= in_array($status, ['shipped', 'delivered']) ? 'active' : '' ?>">Shipped</div>
                <div class="step <?= $status === 'delivered' ? 'active' : '' ?>">Delivered</div>
            </div>
        </div>

        <!-- Items -->
        <div class="card">
            <?php foreach ($items as $item): ?>
                <div class="item">
                    <img src="<?= BASE_URL . htmlspecialchars($item['product_image']) ?>">
                    <div>
                        <h4><?= htmlspecialchars($item['product_name']) ?></h4>
                        <p>Qty: <?= (int)$item['qty'] ?></p>
                        <p>₹ <?= number_format($item['product_price'], 2) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Address + Total -->
        <div class="card footer">
            <div>
                <p><strong>Delivery Address</strong></p>
                <p><?= htmlspecialchars($order['address']) ?>, <?= htmlspecialchars($order['city']) ?> - <?= htmlspecialchars($order['pincode']) ?></p>
                <p><strong>Payment:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
            </div>
            <div>
                <h2>Total: ₹ <?= number_format($order['total_amount'], 2) ?></h2>
                <a class="btn" href="<?= BASE_URL ?>views/products/orders.php">Back to Orders</a>
            </div>
        </div>

    </div>
</body>

</html>