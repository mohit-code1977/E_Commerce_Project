<?php

class CartService{
    private mysqli $conn;

    public function __construct(mysqli $conn){
        $this->conn = $conn;
    }

    /**
     * Merge guest cart (session/cookie) into user's DB cart after login
     */
    public function mergeGuestCartToUser(int $userID): void{
        $guestCart = $this->getGuestCart();

        if (empty($guestCart)) {
            return;
        }

        $this->conn->begin_transaction();

        try {
            foreach ($guestCart as $productId => $item) {
                $productId = (int)$productId;
                $qty = (int)($item['qty'] ?? 0);

                if ($productId <= 0 || $qty <= 0) continue;

                $existingQty = $this->getUserCartQty($userID, $productId);

                if ($existingQty !== null) {
                    $this->updateQty($userID, $productId, $existingQty + $qty);
                } else {
                    $this->insertItem($userID, $productId, $qty);
                }
            }

            $this->clearGuestCart();
            $this->conn->commit();

        } catch (Throwable $e) {
            $this->conn->rollback();
            throw $e; // let caller handle error
        }
    }

    /* ----------------- Helpers ----------------- */

    /*-------- Get guest cart --------*/
    private function getGuestCart(): array{
        if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            return $_SESSION['cart'];
        }

        if (!empty($_COOKIE['cart'])) {
            $decoded = json_decode($_COOKIE['cart'], true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /*-------- Get User Cart Products Qantity --------*/
    private function getUserCartQty(int $userID, int $productId): ?int{
        $stmt = $this->conn->prepare("SELECT qty FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $userID, $productId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? (int)$row['qty'] : null;
    }

    /*-------- Update User Cart Products Qantity --------*/
    private function updateQty(int $userID, int $productId, int $qty): void{
        $stmt = $this->conn->prepare("UPDATE cart SET qty = ? WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("iii", $qty, $userID, $productId);
        $stmt->execute();
    }

    /*-------- Add Products into Carts DB --------*/
    private function insertItem(int $userID, int $productId, int $qty): void{
        $stmt = $this->conn->prepare("INSERT INTO cart (user_id, product_id, qty) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $userID, $productId, $qty);
        $stmt->execute();
    }

    /*-------- Clear Guest Cart --------*/
    private function clearGuestCart(): void{
        unset($_SESSION['cart']);
        setcookie("cart", "", time() - 3600, "/");
    }


    /*------------Add  Data Into Cart-----------*/ 
    private function addDataIntoCart(){

    }



}
