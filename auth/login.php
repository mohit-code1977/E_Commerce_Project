<?php
require_once __DIR__ . '/../config/config.php';
require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/services/cartService.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

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
    $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();


    if ($result->num_rows === 1) {
      $row = $result->fetch_assoc();
      $db_psw = $row['password'];
      if (password_verify($password, $db_psw)) {
        $_SESSION['name']  = $row['name'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['id'] = $row['id'];

        setcookie("loginID", $row['id'], time() + 3600, "/");
        setcookie("cart", "", time()-3600, "/");

        //*--------------Login--------------*//
        $_SESSION['flag'] = true;
        $email = $password = "";

        /* ------- Merging cart data into users db -------- */
        mergeData($row['id'], $conn);

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
  <title>Login</title>
  <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>icon.png">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.9.0/fonts/remixicon.css" rel="stylesheet" />

  <style>
    * {
      box-sizing: border-box;
      font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
    }

    body {
      min-height: 100vh;
      margin: 0;
      display: grid;
      place-items: center;
      background: linear-gradient(135deg, #0e579b, #0b3c6d);
      color: #0f172a;
    }

    .card {
      width: 100%;
      max-width: 420px;
      background: #ffffff;
      padding: 28px 26px 32px;
      border-radius: 14px;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
    }

    .card h2 {
      text-align: center;
      margin-bottom: 20px;
      letter-spacing: 0.4px;
    }

    .field {
      margin-bottom: 14px;
      position: relative;
    }

    label {
      display: block;
      font-size: 14px;
      margin-bottom: 6px;
      color: #334155;
      font-weight: 600;
    }

    input {
      width: 100%;
      padding: 10px 12px;
      border-radius: 8px;
      border: 1px solid #cbd5e1;
      outline: none;
      font-size: 14px;
    }

    input:focus {
      border-color: #2563eb;
      box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
    }

    .icon {
      position: absolute;
      right: 10px;
      top: 36px;
      cursor: pointer;
      opacity: 0.7;
    }

    .hide {
      display: none;
    }

    .error {
      font-size: 12px;
      color: #dc2626;
      margin-top: 4px;
    }

    .btn {
      width: 100%;
      margin-top: 12px;
      padding: 10px;
      border-radius: 10px;
      border: none;
      cursor: pointer;
      font-weight: 700;
      background: #2563eb;
      color: #fff;
      transition: transform 0.05s ease, box-shadow 0.15s ease;
      box-shadow: 0 6px 14px rgba(37, 99, 235, 0.35);
    }

    .btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 10px 22px rgba(37, 99, 235, 0.45);
    }

    .alt {
      margin-top: 14px;
      text-align: center;
      font-size: 14px;
      color: #475569;
    }

    .alt a {
      color: #2563eb;
      font-weight: 700;
      text-decoration: none;
    }

    .alt a:hover {
      text-decoration: underline;
    }
  </style>
</head>

<body>
  <div class="card">
    <h2>Login</h2>

    <form method="POST" novalidate>
      <div class="field">
        <label>Email ID</label>
        <input type="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" placeholder="you@example.com">
        <div class="error"><?= $error['email'] ?? "" ?></div>
      </div>

      <div class="field">
        <label>Password</label>
        <input class="psw_input" type="password" name="password" placeholder="••••••••">
        <i id="mask" class="ri-eye-line icon"></i>
        <i id="mask_off" class="ri-eye-off-line icon hide"></i>
        <div class="error"><?= $error['psw'] ?? "" ?></div>
      </div>

      <button class="btn" name="login">Login</button>
    </form>

    <div class="alt">
      Don’t have an account?
      <a href="<?= BASE_URL ?>auth/registration.php">Register</a>
    </div>
  </div>

  <script src="<?= BASE_URL ?>auth/script.js"></script>
</body>

</html>