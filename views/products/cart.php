<?php
require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/auth/session.php';

$userID = $_SESSION['id'];

$total = 0;
$subtotal = 0;

if (isset($_SESSION['cart']) && isset($userID)) {
    $items = $_SESSION['cart'][$userID];
    // print_r($items);
    foreach ($items as $key => &$item) {
        // print_r($item);
        // print("<br><br>");        
        $item['subtotal'] += $item['productPrice'] * $item['qty'];
        $total += $item['productPrice'] * $item['qty'];

        // print_r($item);
    }

    print_r($items);
    exit;
    // echo "Total : $total";
}



?>


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
            <h3>Review your selected items</h3>
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
                            <?= $items ?>
                                <tr>
                                    <td class="product-col">
                                        <img class="thumb" src="<?= BASE_URL . $item['productImg'] ?>" alt="">
                                        <span><?= $item['productName'] ?></span>
                                    </td>
                                    <td class="price"><?= $item['productPrice'] ?></td>

                                    <td>
                                        <a href="">-</a>
                                        <span><?= $item['qty'] ?></span>
                                        <a href="">+</a>
                                    </td>

                                    <td class="subtotal"><?= $item['subtotal'] ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>

                    <div class="cart-footer">
                        <div class="total">Total: <span><?= $total ?></span></div>
                        <div class="actions">
                            <button class="btn">Continue Shopping</button>
                            <button class="btn">Update Cart</button>
                            <button class="btn primary">Checkout</button>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </main>
    </div>
</body>

</html>