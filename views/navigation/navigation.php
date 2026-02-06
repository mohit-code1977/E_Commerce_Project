<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/db.php';

$user_name = $_SESSION['name'];
$cartCount = $_SESSION['cart_count'] ?? 0;

// Fetch categories from DB
$cats = [];
$res = $conn->query("SELECT id, name, parent_id FROM categories");
while ($row = $res->fetch_assoc()) {
    $cats[] = $row;
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navigation Page</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>icon.png">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.9.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="stylesheet" href="navigation.css">
</head>

<body>
    <div id="main">
        <!---------Navigation Bar---------->
        <nav>
            <div class="left">
                <h2>Dashboard</h2>
                <div class="category-menu">
                    <span>Categories ▾</span>
                    <!-- add tree function -->
                </div>
            </div>

            <div class="mid">
                <h2>Welcome :</h2>
                <p class="username"><?= htmlspecialchars($user_name) ?></p>
            </div>

            <div class="right">
                <i class="ri-shopping-cart-2-line"></i>
                <h3>Cart</h3>
                <p class="cart_count"><?= $cartCount ?></p>
                <a href="<?= BASE_URL ?>auth/logout.php" id="logout_btn">Logout</a>
            </div>
        </nav>


    </div>
</body>
</html>