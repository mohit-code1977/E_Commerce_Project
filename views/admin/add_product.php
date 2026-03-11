<?php
require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/config/db.php';

// Fetch all categories for dropdown
$stmt = $conn->prepare("SELECT id, name, parent_id FROM categories ORDER BY parent_id, name");
$stmt->execute();
$all_categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Build nested category options
function buildCategoryOptions($categories, $parentId = null, $depth = 0) {
    $html = '';
    foreach ($categories as $cat) {
        if ($cat['parent_id'] == $parentId) {
            $prefix = str_repeat('&nbsp;&nbsp;&nbsp;', $depth);
            $arrow  = $depth > 0 ? '↳ ' : '';
            $html .= "<option value='{$cat['id']}'>{$prefix}{$arrow}{$cat['name']}</option>";
            $html .= buildCategoryOptions($categories, $cat['id'], $depth + 1);
        }
    }
    return $html;
}

$success = $error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $price       = floatval($_POST['price'] ?? 0);
    $category_id = intval($_POST['category_id'] ?? 0);

    if (empty($name) || $price <= 0 || $category_id <= 0) {
        $error = "Please fill all required fields.";
    } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $error = "Please select a valid image.";
    } else {
        $file    = $_FILES['image'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($ext, $allowed)) {
            $error = "Invalid file type. Allowed: jpg, jpeg, png, webp, gif";
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $error = "File too large. Max 5MB allowed.";
        } else {
            $uploadDir = BASE_PATH . '/uploads/products/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $safeName  = preg_replace('/[^a-z0-9]+/', '-', strtolower($name));
            $fileName  = $safeName . '_' . time() . '.' . $ext;
            $destPath  = $uploadDir . $fileName;

            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                $image_path = 'uploads/products/' . $fileName;
                $stmt = $conn->prepare("INSERT INTO products (name, price, image, category_id) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("sdsi", $name, $price, $image_path, $category_id);

                if ($stmt->execute()) {
                    $success = "Product '<strong>{$name}</strong>' added successfully!";
                } else {
                    unlink($destPath);
                    $error = "Database error: " . $stmt->error;
                }
            } else {
                $error = "Failed to upload image. Check folder permissions.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product | Admin</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>icon.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- <link rel="stylesheet" href="<?= BASE_URL ?>/views/admin/dashboard.css"> -->
     <link rel="stylesheet" href="<?= BASE_URL ?>/views/admin/dark-theme.css">
    <style>
        .form-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            max-width: 680px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.06);
        }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 7px;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            transition: border 0.2s;
            outline: none;
        }
        .form-group input:focus,
        .form-group select:focus {
            border-color: #6d28d9;
        }
        .form-group textarea { height: 80px; resize: vertical; }
        .file-drop {
            border: 2px dashed #d1d5db;
            border-radius: 10px;
            padding: 24px;
            text-align: center;
            cursor: pointer;
            transition: 0.2s;
            background: #fafafa;
        }
        .file-drop:hover { border-color: #6d28d9; background: #f5f3ff; }
        .file-drop input { display: none; }
        .file-drop i { font-size: 28px; color: #9ca3af; margin-bottom: 8px; }
        .file-drop p { font-size: 13px; color: #6b7280; }
        .preview-wrap { margin-top: 14px; display: none; text-align: center; }
        .preview-wrap img {
            max-width: 180px;
            max-height: 180px;
            border-radius: 10px;
            border: 2px solid #e5e7eb;
            object-fit: cover;
        }
        .btn-submit {
            width: 100%;
            padding: 12px;
            background: #6d28d9;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: 0.2s;
        }
        .btn-submit:hover { background: #5b21b6; transform: translateY(-1px); }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .alert-error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .required { color: #ef4444; }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #6d28d9;
            text-decoration: none;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .back-link:hover { text-decoration: underline; }
        .page-title { font-size: 22px; font-weight: 600; margin-bottom: 24px; color: #111827; }
    </style>
</head>
<body>
    <?php include "layout/sidebar.php"; ?>

    <div class="main">
        <div class="topbar">
            <h1 class="msg">Admin : <p class="greeting">Add Product</p></h1>
        </div>

        <a href="<?= BASE_URL ?>/views/admin/products.php" class="back-link">
            <i class="fa fa-arrow-left"></i> Back to Products
        </a>

        <div class="form-card">

            <?php if ($success): ?>
                <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?= $success ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><i class="fa fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">

                <div class="form-group">
                    <label>Product Name <span class="required">*</span></label>
                    <input type="text" name="name" placeholder="e.g. Dell XPS 13" required
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Price (₹) <span class="required">*</span></label>
                    <input type="number" name="price" step="0.01" min="1"
                           placeholder="e.g. 55000" required
                           value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Category <span class="required">*</span></label>
                    <select name="category_id" required>
                        <option value="">-- Select Category --</option>
                        <?= buildCategoryOptions($all_categories) ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Product Image <span class="required">*</span></label>
                    <div class="file-drop" onclick="document.getElementById('imgInput').click()">
                        <input type="file" id="imgInput" name="image"
                               accept="image/*" onchange="previewImg(event)" required>
                        <i class="fa fa-cloud-upload-alt"></i>
                        <p>Click to upload image<br><small>JPG, PNG, WEBP — Max 5MB</small></p>
                    </div>
                    <div class="preview-wrap" id="previewWrap">
                        <img id="previewImg" src="" alt="Preview">
                        <p style="font-size:12px;color:#6b7280;margin-top:6px;" id="fileName"></p>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fa fa-plus"></i> &nbsp;Add Product
                </button>

            </form>
        </div>
    </div>

    <script>
        function previewImg(event) {
            const file = event.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById('previewImg').src = e.target.result;
                document.getElementById('previewWrap').style.display = 'block';
                document.getElementById('fileName').textContent = file.name;
            };
            reader.readAsDataURL(file);
        }
    </script>
</body>
</html>