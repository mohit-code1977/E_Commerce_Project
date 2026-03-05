<?php
require '../../config/config.php';

?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Products Panel</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>icon.png">
    <link rel="stylesheet" href="<?= BASE_URL ?>/views/admin/products.css">
</head>

<body>
    <div class="container">
        <!-- SIDEBAR -->
        <div class="sidebar">
            <h2>Admin</h2>
            <ul>
                <li><a href="#">Dashboard</a></li>
                <li><a href="#">Products</a></li>
                <li><a href="#">Categories</a></li>
                <li><a href="#">Upload CSV</a></li>
                <li><a href="#">Storefront</a></li>
            </ul>
        </div>

        <!-- MAIN CONTENT -->
        <div class="main">
            <div class="topbar">
                <h1>Admin Products Panel</h1>
                <button class="add-btn">+ Add Product</button>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td><img src="phone.webp"></td>
                            <td>Samsung Galaxy</td>
                            <td>12999</td>
                            <td>Android</td>
                            <td>
                                <button class="edit">Edit</button>
                                <button class="delete">Delete</button>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td><img src="iphone.webp"></td>
                            <td>iPhone 15</td>
                            <td>79900</td>
                            <td>iPhone</td>
                            <td>
                                <button class="edit">Edit</button>
                                <button class="delete">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>