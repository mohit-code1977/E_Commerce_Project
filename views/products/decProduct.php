<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../auth/session.php';

/*--------------Fetch userID and productID--------------*/ 
$userID = (int)($_SESSION['id'] ?? 0);
$productKey = (int)($_GET['id'] ?? 0);

/*--------------Check Validations--------------*/ 
if ($userID > 0 && $productKey > 0 && isset($_SESSION['cart'][$userID][$productKey])) {

    //-----> get product count
    $productCount = (int)$_SESSION['cart'][$userID][$productKey]['qty'];

    /*--------------decrement product logic--------------*/ 
    if ($productCount <= 1) {
        unset($_SESSION['cart'][$userID][$productKey]);
    } else {
        $_SESSION['cart'][$userID][$productKey]['qty'] = $productCount - 1;
    }
}

/*--------------redirect to the card page--------------*/ 
header("Location: " . BASE_URL . "views/products/cart.php");
exit();
