<?php
session_start();
session_unset();
session_destroy();

setcookie("loginID", "", time() - 3600, "/");
setcookie("userName", "", time() - 3600, "/");

header("Location: /TASK/auth/login.php");
exit;
?>