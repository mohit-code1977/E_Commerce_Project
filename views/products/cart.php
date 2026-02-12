<?php
require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/auth/session.php';

$userID = $_SESSION['id'];

$total = 0;
$subtotal = 0;

/*-----------calculate total value of all products-----------*/
if (isset($_SESSION['cart']) && isset($userID)) {
    $items = $_SESSION['cart'][$userID];

    //----> calculate total value and subtotal also
    foreach ($items as $key => &$item) {
        $item['subtotal'] = $item['productPrice'] * $item['qty'];
        $total += $item['productPrice'] * $item['qty'];
    }
    unset($item);

    //----> save back to session
    $_SESSION['cart'][$userID] = $items;
    $_SESSION['totalPrice'] = $total;

}
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
</body>

</html>