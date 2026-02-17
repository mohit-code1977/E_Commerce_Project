<?php
require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/auth/session.php';

// --- Auth check ---
$userID = (int)($_COOKIE['loginID'] ?? 0);
if ($userID <= 0) {
    die("Unauthorized access");
}

$username = htmlspecialchars($_SESSION['name'] ?? $_COOKIE['userName']);


/* ================= Fetch Orders ================= */
$orders = [];

$stmt = $conn->prepare("
    SELECT id, total_amount, status, created_at 
    FROM orders 
    WHERE user_id = ? 
    ORDER BY id DESC
");

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

/* =========== Fetch Order Items (Single Query) =========== */
$orderItemsMap = [];

if (!empty($orders)) {
    $orderIds = array_column($orders, 'id');
    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
    $types = str_repeat('i', count($orderIds));

    $sql = "
        SELECT order_id, product_name, product_image, product_price, qty
        FROM order_items
        WHERE order_id IN ($placeholders)
    ";

    $stmtItems = $conn->prepare($sql);
    if (!$stmtItems) {
        die("Prepare failed: " . $conn->error);
    }

    $stmtItems->bind_param($types, ...$orderIds);
    $stmtItems->execute();
    $itemsRes = $stmtItems->get_result();

    while ($item = $itemsRes->fetch_assoc()) {
        $orderItemsMap[$item['order_id']][] = $item;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>My Orders</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" href="<?= BASE_URL ?>icon.png">
  <link rel="stylesheet" href="<?= BASE_URL ?>/views/products/orders.css">
</head>

<body>
  <div class="container">
  <nav class="navbar">
  <div class="nav-left">
    <h1 class="page-title">My Orders</h1>
    <span class="greeting">Hi, <strong><?= $username ?></strong> 👋</span>
  </div>

  <div class="nav-right">
    <a href="<?= BASE_URL ?>" class="nav-btn nav-home">Home</a>
    <a href="<?= BASE_URL ?>auth/logout.php" class="nav-btn nav-logout">Logout</a>
  </div>
</nav>



    <?php if (empty($orders)): ?>
      <div class="order-card">
        <h3>No orders yet 😶</h3>
        <p>Start shopping to see your orders here.</p>
      </div>
    <?php endif; ?>

    <?php foreach ($orders as $order): 
        $oid = (int)$order['id'];
        $items = $orderItemsMap[$oid] ?? [];
        $statusClass = strtolower($order['status']);
    ?>
      <div class="order-card">
        <div class="order-top">
          <div>
            <div class="order-id">Order #<?= $oid ?></div>
            <div class="order-meta">Placed on <?= date("d M Y", strtotime($order['created_at'])) ?></div>
          </div>
          <span class="status <?= $statusClass ?>">
            <?= htmlspecialchars($order['status']) ?>
          </span>
        </div>

        <div class="items">
          <?php foreach ($items as $item): ?>
            <div class="item">
              <img src="<?= BASE_URL . htmlspecialchars($item['product_image']) ?>" alt="">
              <div class="item-info">
                <h4><?= htmlspecialchars($item['product_name']) ?></h4>
                <p>Qty: <?= (int)$item['qty'] ?></p>
              </div>
              <div class="price">₹ <?= number_format((float)$item['product_price'], 2) ?></div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="order-footer">
          <div><strong>Total:</strong> ₹ <?= number_format((float)$order['total_amount'], 2) ?></div>
          <div class="actions">
            <a href="<?= BASE_URL ?>views/products/orderDetails.php?id=<?= $oid ?>">View Details</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Animations -->
  <script src="https://unpkg.com/gsap@3/dist/gsap.min.js"></script>
  <script>
    gsap.from(".order-card", {
      opacity: 0,
      y: 40,
      duration: 0.8,
      stagger: 0.12,
      ease: "power3.out"
    });
  </script>
</body>
</html>
