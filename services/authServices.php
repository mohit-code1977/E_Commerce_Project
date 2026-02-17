<?php

class AuthService{
    private mysqli $conn;

    public function __construct(mysqli $conn){
        $this->conn = $conn;
    }

    public function isEmailTaken(string $email): bool{
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->num_rows > 0;
    }

    public function register(string $name, string $email, string $password): int{
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->conn->prepare("
            INSERT INTO users (name, email, password) 
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("sss", $name, $email, $hash);
        $stmt->execute();

        return $this->conn->insert_id; // new user id
    }
}
