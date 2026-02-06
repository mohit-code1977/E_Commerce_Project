<?php
session_start();

require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['email'])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}
