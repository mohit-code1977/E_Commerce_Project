<?php
if (!isset($_COOKIE['userChoice'])) {
    header("Location: " . BASE_URL . "views/navigation/navigation.php?consent=required");
    exit;
}
