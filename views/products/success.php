<?php
require_once __DIR__ . '/../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = $_SESSION['flash_success'] ?? "🎉 Order placed successfully!";
unset($_SESSION['flash_success']);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Order Success</title>
    <meta charset="UTF-8">
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>icon.png">
    <style>
        .alert-box {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #16a34a;
            color: #022c22;
            padding: 14px 20px;
            border-radius: 10px;
            font-weight: 700;
            box-shadow: 0 10px 20px rgba(0, 0, 0, .25);
            z-index: 9999;
            animation: slideDown .4s ease-out;
        }

        @keyframes slideDown {
            from {
                transform: translate(-50%, -20px);
                opacity: 0;
            }

            to {
                transform: translate(-50%, 0);
                opacity: 1;
            }
        }
    </style>
</head>

<body>

    <div class="alert-box" id="alertBox">
        <?= htmlspecialchars($message) ?>
    </div>

    <script>
        console.log("Success page loaded. Redirecting in 3 seconds...");

        setTimeout(function() {
            // Hardcode relative path to avoid BASE_URL issues
            window.location.assign("/task/views/products/orders.php");
        }, 3000);
    </script>

</body>

</html>