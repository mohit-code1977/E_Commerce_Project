<?php
require_once __DIR__. '/../../config/config.php';
require_once BASE_PATH. '/config/db.php';
// require_once BASE_PATH. '/auth/session.php';


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
    <div id="main">
        <header id="header">
            <h1>Shopping Card</h1>
            <h3>Review your selected items</h3>
        </header>

        <main id="main">
            <table>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                </tr>
            </table>
        </main>

        <footer id="footer">

        </footer>
    </div>
</body>
</html>