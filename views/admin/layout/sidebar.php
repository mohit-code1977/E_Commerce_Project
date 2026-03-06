<?php
$current_page = basename($_SERVER['PHP_SELF']);
// echo "<script>console.log('print current_page : $current_page');</script>";
?>

<div class="sidebar">
    <div class="logo">E-Commerce</div>
    <ul class="menu">
        <li>
            <a href="<?= BASE_URL ?>/views/admin/dashboard.php"
                class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                Dashboard
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/views/admin/products.php"
                class="<?= $current_page == 'products.php' ? 'active' : '' ?>">
                Products
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/views/admin/categories.php"
                class="<?= $current_page == 'categories.php' ? 'active' : '' ?>">
                Categories
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/views/admin/import_products.php"
                class="<?= $current_page == 'import_products.php' ? 'active' : '' ?>">
                Import CSV
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/views/admin/orders.php"
                class="<?= $current_page == 'orders.php' ? 'active' : '' ?>">
                Orders
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/views/admin/logs.php"
                class="<?= $current_page == 'logs.php' ? 'active' : '' ?>">
                Activity Logs
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/views/navigation/navigation.php">Storefront</a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/views/admin/logout.php">Logout</a>
        </li>
    </ul>
</div>
