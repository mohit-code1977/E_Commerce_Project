<?php
require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/config/db.php';

$success = $error = '';

// ── DELETE PRODUCT ──
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    // Get image path first to delete file
    $row = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $row->bind_param("i", $del_id);
    $row->execute();
    $prod = $row->get_result()->fetch_assoc();

    if ($prod && !empty($prod['image'])) {
        $fullPath = BASE_PATH . '/' . $prod['image'];
        if (file_exists($fullPath)) unlink($fullPath);
    }

    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $del_id);
    $stmt->execute();
    $success = "Product deleted successfully.";
}

// ── FETCH CATEGORIES ──
$stmt = $conn->prepare("SELECT id, name FROM categories ORDER BY name ASC");
$stmt->execute();
$categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ── CATEGORY NAME MAP ──
$catMap = [];
foreach ($categories as $c) $catMap[$c['id']] = $c['name'];

// ── FILTER BY CATEGORY ──
$category_id = $_POST['category_id'] ?? '';
$search      = trim($_POST['search'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($category_id)) {
        $stmt = $conn->prepare("
            SELECT * FROM products
            WHERE category_id = ?
            OR category_id IN (SELECT id FROM categories WHERE parent_id = ?)
            OR category_id IN (
                SELECT id FROM categories
                WHERE parent_id IN (SELECT id FROM categories WHERE parent_id = ?)
            )
        ");
        $stmt->bind_param("iii", $category_id, $category_id, $category_id);
    } elseif (!empty($search)) {
        $like = "%{$search}%";
        $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ?");
        $stmt->bind_param("s", $like);
    } else {
        $stmt = $conn->prepare("SELECT * FROM products");
    }
} else {
    $stmt = $conn->prepare("SELECT * FROM products ORDER BY id DESC");
}
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products | Admin</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>icon.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- <link rel="stylesheet" href="<?= BASE_URL ?>/views/admin/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/views/admin/products.css"> -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/views/admin/dark-theme.css">
    <style>
        .layout { display: flex; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .header h2 { font-size: 20px; font-weight: 600; }
        .btn-add {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            background: #6d28d9;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            transition: 0.2s;
        }
        .btn-add:hover { background: #5b21b6; }
        .filters form { display: flex; gap: 10px; flex-wrap: wrap; }
        .filters input {
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 7px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            outline: none;
            min-width: 200px;
        }
        .btn-edit {
            padding: 5px 11px;
            background: #ede9fe;
            color: #6d28d9;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
            font-family: 'Poppins', sans-serif;
        }
        .btn-delete {
            padding: 5px 11px;
            background: #fee2e2;
            color: #dc2626;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
            font-family: 'Poppins', sans-serif;
        }
        .btn-edit:hover   { background: #ddd6fe; }
        .btn-delete:hover { background: #fecaca; }
        td img { width: 48px; height: 48px; object-fit: cover; border-radius: 7px; border: 1px solid #e5e7eb; }
        .alert {
            padding: 11px 15px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 14px;
        }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .count { color: #6b7280; font-size: 14px; }
    </style>
</head>
<body>
    <div class="layout">
        <?php include "layout/sidebar.php"; ?>

        <div class="main">
            <div class="topbar">
                <h1 class="msg">Admin : <p class="greeting">Products</p></h1>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?= $success ?></div>
            <?php endif; ?>

            <div class="header">
                <h2>Products <span class="count">(<?= count($products) ?>)</span></h2>
                <a href="<?= BASE_URL ?>/views/admin/add_product.php" class="btn-add">
                    <i class="fa fa-plus"></i> Add Product
                </a>
            </div>

            <!-- Filters -->
            <div class="filters" style="margin-bottom:20px;">
                <form method="POST">
                    <select name="category_id">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="search" placeholder="Search by name..."
                           value="<?= htmlspecialchars($search) ?>">
                    <button class="search-btn"><i class="fa fa-search"></i></button>
                </form>
            </div>

            <!-- Table -->
            <div class="table-box">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr><td colspan="6" style="text-align:center;color:#9ca3af;padding:30px;">No products found.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td><?= $p['id'] ?></td>
                                <td>
                                    <img src="<?= BASE_URL . htmlspecialchars($p['image'] ?? '') ?>"
                                         onerror="this.src='https://via.placeholder.com/48x48?text=?'"
                                         alt="">
                                </td>
                                <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
                                <td><?= htmlspecialchars($catMap[$p['category_id']] ?? 'Unknown') ?></td>
                                <td>₹<?= number_format($p['price'], 2) ?></td>
                                <td style="display:flex;gap:8px;">
                                    <a href="<?= BASE_URL ?>/views/admin/edit_product.php?id=<?= $p['id'] ?>"
                                       class="btn-edit"><i class="fa fa-pen"></i> Edit</a>
                                    <a href="?delete=<?= $p['id'] ?>" class="btn-delete"
                                       onclick="return confirm('Delete \'<?= htmlspecialchars($p['name']) ?>\'?')">
                                        <i class="fa fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>