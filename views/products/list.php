<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';

$product_key = $_GET['cat'];
// echo $product_key;

$sql = "select * from categories where id = $product_key";
$result = $conn->query($sql);

while($row = $result->fetch_assoc()){
    print_r($row);
    print("<br>");

    print($row['name']);
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List Page</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>icon.png">
</head>
<body>
    <h1>Hiiiii.....You're In Products Listing Page</h1>
</body>
</html>