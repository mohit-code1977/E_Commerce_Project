<?php
require_once __DIR__ . '/../config/config.php';
require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/services/cartService.php';

session_start();

$error = [];
$unique_Email = [];
$name = $email = $psw = "";

if ($_SERVER['REQUEST_METHOD'] === "POST") {
  $name = trim($_POST['name'] ?? "");
  $email = trim($_POST['email'] ?? "");
  $psw = trim($_POST['password'] ?? "");

  /*-------------Name Validation------------*/
  if (empty($name)) {
    $error['name'] = "Name is required !";
  } elseif (!preg_match("/^[a-zA-Z ]{3,50}$/", $name)) {
    $error['name'] = "Name must contain only letters and spaces (3–50 chars)";
  }

  /*------------Email Validation-------------*/
  if (empty($email)) {
    $error['email'] = "Email is required !";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error['email'] = "Invalid email address";
  }

  /*-----------Password Validation-----------*/
  if (empty($psw)) {
    $error['password'] = "Password is required !";
  } elseif (
    strlen($psw) < 8 ||
    !preg_match('/[A-Z]/', $psw) ||
    !preg_match('/[a-z]/', $psw) ||
    !preg_match('/[0-9]/', $psw) ||
    !preg_match('/[\W_]/', $psw)
  ) {
    $error['password'] = "Min 8 chars with upper, lower, number & symbol";
  }


  /* ---- Email Uniqueness Check ---- */
  if (empty($error)) {
    $checkEmail = "SELECT email FROM users WHERE email = '$email'";
    $res = $conn->query($checkEmail);

    if ($res && $res->num_rows > 0) {
      $error['email'] = "This email is already registered!";
    } else {

      /*---------Password Hashing---------------*/
      $hash_password = password_hash($psw, PASSWORD_DEFAULT);

      $sql = "INSERT INTO USERS (name, email, password) VALUE 
            ('$name', '$email', '$hash_password')";

      if ($conn->query($sql)) {
        echo "
                <script>alert('Data Inserted Successfully');</script>";
        $_SESSION['name'] = $name;
        $_SESSION['email'] = $email;
        // $_SESSION['password'] = $hash_password;

        //---> get login ID
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $userID = (int)$row['id'];

        $_SESSION['id'] = $userID;

        setcookie("loginID", $userID, time() + 3600, "/");
        setcookie("cart", "", time()-3600, "/");


        /* ------------------------ Merging cart data into users db ------------------- */
        mergeData($userID, $conn);


        $name = $email = $psw = "";

        //---> redirect to the next page
        header("Location: " . BASE_URL . "/views/navigation/navigation.php");
        exit();
      } else {
        echo "Insertion Failed : " . $conn->connect_error;
      }
    }
  }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
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
      max-width: 440px;
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
      background: #16a34a;
      color: #fff;
      transition: transform 0.05s ease, box-shadow 0.15s ease;
      box-shadow: 0 6px 14px rgba(22, 163, 74, 0.35);
    }

    .btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 10px 22px rgba(22, 163, 74, 0.45);
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
    <h2>Create Account</h2>

    <form method="POST" novalidate>
      <div class="field">
        <label>Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($name ?? '') ?>" placeholder="Your name">
        <div class="error"><?= $error['name'] ?? "" ?></div>
      </div>

      <div class="field">
        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" placeholder="you@example.com">
        <div class="error"><?= $error['email'] ?? "" ?></div>
      </div>

      <div class="field">
        <label>Password</label>
        <input class="psw_input" type="password" name="password" placeholder="Create a strong password">
        <i id="mask" class="ri-eye-line icon"></i>
        <i id="mask_off" class="ri-eye-off-line icon hide"></i>
        <div class="error"><?= $error['password'] ?? "" ?></div>
      </div>

      <button class="btn" name="register">Register</button>
    </form>

    <div class="alt">
      Already have an account?
      <a href="<?= BASE_URL ?>auth/login.php">Login</a>
    </div>
  </div>

  <script src="<?= BASE_URL ?>auth/script.js"></script>
</body>

</html>