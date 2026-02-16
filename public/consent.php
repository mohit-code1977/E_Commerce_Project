<?php
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $choice = $_POST['submit'] == '1' ? 'cookies' : 'session';
    setcookie("userChoice", $choice, time() + (86400 * 30), "/");
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? BASE_URL));
    exit;
}
