<?php

function mergeData($userID, mysqli $conn){

    /*-------- Get guest cart from Session/Cookies ---------*/
    $guestCart = [];

    if (!empty($_SESSION['cart'])) {
        $guestCart = $_SESSION['cart'];
    } elseif (!empty($_COOKIE['cart'])) {
        $guestCart = json_decode($_COOKIE['cart'], true) ?? [];
    }

    // print("GuestCart : ");
    // print_r($guestCart);
    // exit;

    if (empty($guestCart)) return;

    /*-------- Data merge with DB ---------*/
    foreach ($guestCart as $productId => $item) {

        $stmt = $conn->prepare("SELECT qty FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $userID, $productId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if ($row) {
            // same product → qty increase
            $newQty = $row['qty'] + (int)$item['qty'];
            $upd = $conn->prepare("UPDATE cart SET qty = ? WHERE user_id = ? AND product_id = ?");
            $upd->bind_param("iii", $newQty, $userID, $productId);
            $upd->execute();
        } else {
            // unique product → insert
            $qty = (int)$item['qty'];
            $ins = $conn->prepare("INSERT INTO cart (user_id, product_id, qty) VALUES (?, ?, ?)");
            $ins->bind_param("iii", $userID, $productId, $qty);
            $ins->execute();
        }
    }


    /*--------clear cart from session/cookies-------*/ 
    unset($_SESSION['cart']);
    setcookie("cart", $row['id'], time() - 3600, "/");

}