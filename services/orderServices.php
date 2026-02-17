<?php

class OrderService{
    private mysqli $conn;

    public function __construct(mysqli $conn){
        $this->conn = $conn;
    }

    public function placeOrder(
    int $userID, string $name, string $phone, string $address, string $city, string $pincode):
     int {
        $this->conn->begin_transaction();

        try {
            // 1. Fetch cart
            $cartItems = $this->getUserCart($userID);
            if (empty($cartItems)) {
                throw new Exception("Cart is empty");
            }

            // 2. Calculate total
            $totalPrice = 0.0;
            foreach ($cartItems as $item) {
                $totalPrice += $item['product_price'] * $item['qty'];
            }

            // 3. Create order
            $orderId = $this->createOrder($userID, $name, $phone, $address, $city, $pincode, $totalPrice);

            // 4. Insert items
            $this->insertOrderItems($orderId, $cartItems);

            // 5. Clear cart
            $this->clearUserCart($userID);

            $this->conn->commit();
            return $orderId;

        } catch (Throwable $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    /* ------------------- Private Helpers ------------------- */

    private function getUserCart(int $userID): array{
        $stmt = $this->conn->prepare("
            SELECT 
                c.product_id,
                c.qty,
                p.name  AS product_name,
                p.price AS product_price,
                p.image AS product_image
            FROM cart c
            JOIN products p ON p.id = c.product_id
            WHERE c.user_id = ?
        ");
        $stmt->bind_param("i", $userID);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    //---> insert onder details in order table
    private function createOrder(
        int $userID,
        string $name,
        string $phone,
        string $address,
        string $city,
        string $pincode,
        float $totalPrice
    ): int {
        $paymentMethod = 'COD';
        $status = 'PLACED';

        $stmt = $this->conn->prepare("
            INSERT INTO orders 
            (user_id, name, phone_no, address, city, pincode, total_amount, payment_method, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "isssssdss",
            $userID,
            $name,
            $phone,
            $address,
            $city,
            $pincode,
            $totalPrice,
            $paymentMethod,
            $status
        );
        $stmt->execute();

        return $this->conn->insert_id;
    }

    private function insertOrderItems(int $orderId, array $cartItems): void{
        $stmt = $this->conn->prepare("
            INSERT INTO order_items 
            (order_id, product_id, product_name, product_price, product_image, qty, subtotal)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($cartItems as $item) {
            $subtotal = $item['product_price'] * $item['qty'];

            $stmt->bind_param(
                "iisssid",
                $orderId,
                $item['product_id'],
                $item['product_name'],
                $item['product_price'],
                $item['product_image'],
                $item['qty'],
                $subtotal
            );
            $stmt->execute();
        }
    }

    private function clearUserCart(int $userID): void{
        $stmt = $this->conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $userID);
        $stmt->execute();
    }
}
