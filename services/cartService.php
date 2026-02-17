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


/*================= ADD_TO_CART PAGE LOGIC HERE ==================*/ 

    /*------------Add  Data Into Cart-----------*/ 
    public function addToCart(int $productId): void
    {
        $mode = Consent::mode();

        if ($mode === 'db') {
            $this->addToDbCart((int)$_SESSION['id'], $productId);
            return;
        }

        // cookies / session
        $product = $this->getProductSnapshot($productId);
        $this->addToGuestCart($product);
    }


    /*------------Add  Data Into DB-----------*/ 
    private function addToDbCart(int $userId, int $productId): void{
        $stmt = $this->conn->prepare("
            INSERT INTO cart (user_id, product_id, qty)
            VALUES (?, ?, 1)
            ON DUPLICATE KEY UPDATE qty = qty + 1
        ");
        $stmt->bind_param("ii", $userId, $productId);
        $stmt->execute();
    }

    /*------------Add  Data Into Cart-----------*/ 
    private function addToGuestCart(array $product): void{
        $cart = Storage::get('cart') ?? [];

        $pid = (int)$product['id'];

        if (isset($cart[$pid])) {
            $cart[$pid]['qty'] += 1;
        } else {
            $cart[$pid] = [
                'product_id'   => $pid,
                'productImg'   => $product['image'],
                'productName'  => $product['name'],
                'productPrice' => (float)$product['price'],
                'qty'          => 1
            ];
        }

        Storage::set('cart', $cart);
    }

    private function getProductSnapshot(int $productId): array{
        $stmt = $this->conn->prepare("SELECT id, image, name, price FROM products WHERE id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc();

        if (!$data) {
            throw new Exception("Product not found");
        }

        return $data;
    }
}
