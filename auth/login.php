<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
session_start();

$error = [];
$email = $password = "";

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    /*------------Email Validation-------------*/
    if (empty($email)) {
        $error['email'] = "Email is required !";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error['email'] = "Invalid email address";
    }

    /*-----------Password Validation-----------*/
    if (empty($password)) {
        $error['password'] = "Password is required !";
    } elseif (
        strlen($password) < 8 ||
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[a-z]/', $password) ||
        !preg_match('/[0-9]/', $password) ||
        !preg_match('/[\W_]/', $password)
    ) {
        $error['password'] = "Min 8 chars with upper, lower, number & symbol";
    }

  /*----------Login Validation----------*/
   if (empty($error)) {
    $sql = "SELECT id, name, email, password FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $db_psw = $row['password'];
         if (password_verify($password, $db_psw)) {
            $_SESSION['name']  = $row['name'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['id'] = $row['id'];

            //*--------------Login--------------*//
                $_SESSION['flag'] = true;
                $email = $password = "";
                header("Location: " . BASE_URL . "/views/navigation/navigation.php");
                exit();
        } else {
            $error['psw'] = "Incorrect password";
        }

    } else {
        $error['email'] = "Email not found";
    }
}

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
      <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>icon.png">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.9.0/fonts/remixicon.css" rel="stylesheet" />
    <style>
        .icon {
            margin-top: 2px;
            cursor: pointer;
            font-size: 15px;
        }

        form a {
            text-decoration: none;
            color: #000;
        }

        #mask,
        #mask_off {
            position: absolute;
            left: 160px;
            opacity: 0.7;
        }

        .hide {
            display: none;
        }

        input{
            outline: none;
        }
    </style>
</head>

<body>
    <form method="POST" novalidate>
        <label for="">Email ID:</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>">
        <p style="color: red;"><?= $error['email'] ?? "" ?></p>

        <label for="">Password :</label><br>
        <input class="psw_input" type="password" name="password">
        <i id="mask" class='ri-eye-line icon'></i>
        <i id="mask_off" class='ri-eye-off-line icon hide'></i>
        <p style="color: red;"><?= $error['psw'] ?? "" ?></p>

        <button name="login">Login</button>
    </form>
    <button name="sign_up"><a href="<?= BASE_URL ?>/auth/registration.php">Register</a>  </button>
    <script src="<?= BASE_URL ?>/auth/script.js"></script>
</body>
</html> 