<?php
require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userID = $_COOKIE['loginID'] ?? null;


/* ================= CART SOURCE RESOLUTION ================= */

function getCartItems($userID, mysqli $conn): array{
    // 1. Logged-in user → DB is source of truth
    if ($userID) {
        $items = [];
        $stmt = $conn->prepare("
            SELECT c.product_id, c.qty, p.name, p.price, p.image
            FROM cart c
            JOIN products p ON p.id = c.product_id
            WHERE c.user_id = ?
        ");
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $res = $stmt->get_result();

        while ($row = $res->fetch_assoc()) {
            $items[] = [
                'product_id'   => (int)$row['product_id'],
                'productName'  => $row['name'],
                'productPrice' => (float)$row['price'],
                'productImg'   => $row['image'],
                'qty'          => (int)$row['qty'],
            ];
        }
        $stmt->close();
        return $items;
    }

    // 2. Guest → Cookie
    if (!empty($_COOKIE['cart'])) {
        $cookieCart = json_decode($_COOKIE['cart'], true) ?? [];
        if (!$cookieCart) return [];

        $items = [];
        $ids = array_keys($cookieCart);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));

        $stmt = $conn->prepare("SELECT id, name, price, image FROM products WHERE id IN ($placeholders)");
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
        $res = $stmt->get_result();

        while ($row = $res->fetch_assoc()) {
            $pid = $row['id'];
            $items[] = [
                'product_id'   => (int)$pid,
                'productName'  => $row['name'],
                'productPrice' => (float)$row['price'],
                'productImg'   => $row['image'],
                'qty'          => (int)$cookieCart[$pid]['qty'],
            ];
        }
        $stmt->close();
        return $items;
    }

    // 3. Fallback → session guest cart
    if (!empty($_SESSION['cart'])) {
        return $_SESSION['cart'];
    }

    return [];
}

/* ================= CALCULATE TOTAL ================= */

$items = getCartItems($userID, $conn);

$total = 0;
foreach ($items as &$item) {
    $item['subtotal'] = $item['productPrice'] * $item['qty'];
    $total += $item['subtotal'];
}
unset($item);


//--> set total of all products in Session
$_SESSION['totalPrice'] = $total ?? 0;
?>



<!------ HTML CODE WRITE HERE ------>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart Page</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>icon.png">
    <link rel="stylesheet" href="<?= BASE_URL ?>views/products/cart.css">
</head>

<body>
    <div id="page">
        <nav id="nav">
            <h1>Shopping Card</h1>
            <h3>Review your selected items <a href="<?= BASE_URL ?>/views/navigation/navigation.php">Back Button</a></h3>
            
        </nav>

        <main id="main">
            <div class="cart-card">
                <?php if (empty($items)) { ?>
                    <h1>No Record Found</h1>
                <?php } else { ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($items as $item) { ?>
                                <tr>
                                    <td class="product-col">
                                        <img class="thumb" src="<?= BASE_URL . $item['productImg'] ?>" alt="">
                                        <span><?= $item['productName'] ?></span>
                                    </td>

                                    <td class="price"><?= $item['productPrice'] ?></td>

                                    <td class="quantity">
                                        <a href="<?= BASE_URL ?>views/products/decProduct.php?id=<?= $item['product_id'] ?>">-</a>
                                        <span><?= $item['qty'] ?></span>
                                        <a href="<?= BASE_URL ?>views/products/incProduct.php?id=<?= $item['product_id'] ?>">+</a>
                                    </td>

                                    <td class="subtotal"><?= $item['subtotal'] ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>

                    <div class="cart-footer">
                        <div class="total">Total: <span><?= number_format($total, 2); ?></span></div>
                        <div class="actions">
                            <a href="<?= BASE_URL ?>views/navigation/navigation.php" class="btn shopingBtn">Continue Shopping</a>
                            <a href="<?= BASE_URL ?>views/products/placeOrder.php" class="btn primary">Checkout</a>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </main>
    </div>

    <?= include_once(BASE_PATH. "/views/partials/cookie_banner.php"); ?>
</body>

</html>