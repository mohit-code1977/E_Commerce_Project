<?php
require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/config/db.php';

$success = $error = '';

// ── UPDATE ORDER STATUS ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id = intval($_POST['order_id']);
    $status   = trim($_POST['status']);
    $allowed  = ['PLACED', 'PROCESSING', 'SHIPPED', 'DELIVERED', 'CANCELLED'];

    if (in_array($status, $allowed)) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $order_id);
        $stmt->execute();
        $success = "Order #<strong>{$order_id}</strong> status updated to <strong>{$status}</strong>.";
    }
}

// ── FETCH ALL ORDERS with user info ──
$orders = $conn->query("
    SELECT o.*, u.name AS user_name, u.email AS user_email
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.id DESC
")->fetch_all(MYSQLI_ASSOC);

// ── FETCH ORDER ITEMS for all orders ──
$orderItemsMap = [];
if (!empty($orders)) {
    $ids          = array_column($orders, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types        = str_repeat('i', count($ids));
    $stmt         = $conn->prepare("SELECT * FROM order_items WHERE order_id IN ($placeholders)");
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    foreach ($items as $item) $orderItemsMap[$item['order_id']][] = $item;
}

function statusColor($s) {
    return match(strtoupper($s)) {
        'PLACED'     => '#fef3c7;color:#92400e',
        'PROCESSING' => '#dbeafe;color:#1e40af',
        'SHIPPED'    => '#ede9fe;color:#6d28d9',
        'DELIVERED'  => '#d1fae5;color:#065f46',
        'CANCELLED'  => '#fee2e2;color:#991b1b',
        default      => '#f3f4f6;color:#374151'
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Orders | Admin</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>icon.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- <link rel="stylesheet" href="<?= BASE_URL ?>/views/admin/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/views/admin/products.css"> -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/views/admin/dark-theme.css">
    <style>
        .layout { display: flex; }
        .order-card {
            background: white;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid #f0f0f0;
            overflow: hidden;
        }
        .order-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            background: #fafafa;
            border-bottom: 1px solid #f0f0f0;
            flex-wrap: wrap;
            gap: 10px;
        }
        .order-head-left { display: flex; flex-direction: column; gap: 3px; }
        .order-id { font-size: 16px; font-weight: 700; color: #111827; }
        .order-meta { font-size: 13px; color: #6b7280; }
        .status-badge {
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .order-body { padding: 16px 20px; }
        .order-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }
        .info-item { font-size: 13px; }
        .info-item span { color: #6b7280; display: block; font-size: 12px; margin-bottom: 2px; }
        .items-list { border-top: 1px solid #f3f4f6; padding-top: 14px; }
        .order-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 0;
            border-bottom: 1px solid #f9fafb;
        }
        .order-item img {
            width: 50px; height: 50px;
            object-fit: cover;
            border-radius: 7px;
            border: 1px solid #e5e7eb;
        }
        .item-detail { flex: 1; }
        .item-detail h4 { font-size: 14px; font-weight: 600; color: #111827; }
        .item-detail p  { font-size: 13px; color: #6b7280; }
        .item-price { font-size: 14px; font-weight: 700; color: #6d28d9; }
        .order-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 20px;
            background: #fafafa;
            border-top: 1px solid #f0f0f0;
            flex-wrap: wrap;
            gap: 10px;
        }
        .total-amount { font-size: 16px; font-weight: 700; color: #111827; }
        .status-form { display: flex; gap: 8px; align-items: center; }
        .status-form select {
            padding: 7px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 7px;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            outline: none;
        }
        .status-form button {
            padding: 7px 16px;
            background: #6d28d9;
            color: white;
            border: none;
            border-radius: 7px;
            font-size: 13px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
        }
        .status-form button:hover { background: #5b21b6; }
        .alert {
            padding: 11px 15px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 14px;
        }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .empty { text-align: center; padding: 60px; color: #9ca3af; font-size: 15px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; }
        .header h2 { font-size: 20px; font-weight: 600; }
        .count-badge {
            background: #ede9fe;
            color: #6d28d9;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="layout">
        <?php include "layout/sidebar.php"; ?>

        <div class="main">
            <div class="topbar">
                <h1 class="msg">Admin : <p class="greeting">Orders</p></h1>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?= $success ?></div>
            <?php endif; ?>

            <div class="header">
                <h2>All Orders</h2>
                <span class="count-badge"><?= count($orders) ?> Orders</span>
            </div>

            <?php if (empty($orders)): ?>
                <div class="empty">
                    <i class="fa fa-box-open" style="font-size:40px;margin-bottom:12px;display:block;"></i>
                    No orders yet.
                </div>
            <?php endif; ?>

            <?php foreach ($orders as $order):
                $items = $orderItemsMap[$order['id']] ?? [];
                $sc    = statusColor($order['status']);
            ?>
                <div class="order-card">

                    <!-- Head -->
                    <div class="order-head">
                        <div class="order-head-left">
                            <div class="order-id">Order #<?= $order['id'] ?></div>
                            <div class="order-meta">
                                <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?>
                                &nbsp;·&nbsp;
                                <strong><?= htmlspecialchars($order['user_name'] ?? 'Guest') ?></strong>
                                (<?= htmlspecialchars($order['user_email'] ?? '') ?>)
                            </div>
                        </div>
                        <span class="status-badge" style="background:<?= $sc ?>">
                            <?= htmlspecialchars($order['status']) ?>
                        </span>
                    </div>

                    <!-- Body -->
                    <div class="order-body">

                        <!-- Delivery info -->
                        <div class="order-info-grid">
                            <div class="info-item">
                                <span>Customer Name</span>
                                <?= htmlspecialchars($order['name']) ?>
                            </div>
                            <div class="info-item">
                                <span>Phone</span>
                                <?= htmlspecialchars($order['phone_no']) ?>
                            </div>
                            <div class="info-item">
                                <span>City / Pincode</span>
                                <?= htmlspecialchars($order['city']) ?> - <?= htmlspecialchars($order['pincode']) ?>
                            </div>
                            <div class="info-item">
                                <span>Payment</span>
                                <?= htmlspecialchars($order['payment_method']) ?>
                            </div>
                            <div class="info-item">
                                <span>Address</span>
                                <?= htmlspecialchars($order['address']) ?>
                            </div>
                        </div>

                        <!-- Items -->
                        <div class="items-list">
                            <?php foreach ($items as $item): ?>
                                <div class="order-item">
                                    <img src="<?= BASE_URL . htmlspecialchars($item['product_image']) ?>"
                                         onerror="this.src='https://via.placeholder.com/50x50?text=?'"
                                         alt="">
                                    <div class="item-detail">
                                        <h4><?= htmlspecialchars($item['product_name']) ?></h4>
                                        <p>Qty: <?= $item['qty'] ?> &nbsp;·&nbsp; ₹<?= number_format($item['product_price'], 2) ?> each</p>
                                    </div>
                                    <div class="item-price">₹<?= number_format($item['subtotal'], 2) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Footer: Total + Status Update -->
                    <div class="order-footer">
                        <div class="total-amount">
                            Total: ₹<?= number_format($order['total_amount'], 2) ?>
                        </div>
                        <form method="POST" class="status-form">
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <select name="status">
                                <?php foreach (['PLACED','PROCESSING','SHIPPED','DELIVERED','CANCELLED'] as $s): ?>
                                    <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>>
                                        <?= $s ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit">
                                <i class="fa fa-save"></i> Update
                            </button>
                        </form>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>