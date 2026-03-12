<?php
require "../../config/config.php";
require_once BASE_PATH . '/config/db.php';

$product_id = $_GET['id'] ?? "";

if (!empty($product_id)) {
    $stmt = $conn->prepare("select * from products where id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    // print_r($result);
    // exit;

    $name = $result['name'];
    $price = $result['price'];
    $image = $result['image'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $new_name  = trim($_POST['name']);
    $new_price = trim($_POST['price']);
    $new_image = $image; // default: image

    // image handling
    if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext        = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename   = $new_name . '.' . $ext;
        $upload_dir = BASE_PATH . '/uploads/products/';
        $dest       = $upload_dir . $filename;


        // print("Print Image : <br>");
        // print_r($filename);
        // exit;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
            $new_image = 'uploads/products/' . $filename;
        }
    }

    // echo " Print New Image : ".$new_image;
    // exit();

    $stmt = $conn->prepare("UPDATE products SET name=?, price=?, image=? WHERE id=?");
    $stmt->bind_param("sdsi", $new_name, $new_price, $new_image, $product_id);
    $stmt->execute();

    header("Location: " . BASE_URL . "views/admin/products.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Product | Admin</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>icon.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg: #0f0f13;
            --bg2: #17171d;
            --bg3: #1e1e26;
            --border: #2a2a38;
            --text: #e0e0ee;
            --text2: #9898c0;
            --accent: #7c3aed;
            --accent2: #a855f7;
            --red: #ef4444;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            min-height: 100vh;
        }

        /* ════════════════════════
           SIDEBAR — full styles
           (sidebar.php has no CSS
           so we write it here)
        ════════════════════════ */
        .sidebar {
            width: 230px;
            background: var(--bg2);
            border-right: 1px solid var(--border);
            position: fixed;
            height: 100vh;
            display: flex;
            flex-direction: column;
            z-index: 100;
        }

        .logo {
            font-size: 17px;
            font-weight: 700;
            text-align: center;
            padding: 22px 16px;
            border-bottom: 1px solid var(--border);
            color: var(--text);
            letter-spacing: 0.3px;
        }

        .logo span {
            color: var(--accent2);
        }

        .menu {
            list-style: none;
            padding: 12px 10px;
            flex: 1;
            overflow-y: auto;
        }

        .menu::-webkit-scrollbar {
            width: 3px;
        }

        .menu::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 3px;
        }

        .menu li {
            margin: 2px 0;
        }

        .menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            text-decoration: none;
            color: #c0c0e0;
            font-size: 14px;
            font-weight: 500;
            border-radius: 8px;
            border-left: 3px solid transparent;
            transition: 0.2s;
        }

        .menu a:hover {
            background: rgba(124, 58, 237, 0.08);
            color: var(--text);
        }

        .menu a.active {
            background: rgba(124, 58, 237, 0.12);
            color: var(--accent2);
            border-left-color: var(--accent2);
        }

        .menu a i {
            width: 16px;
            font-size: 13px;
            text-align: center;
        }

        /* submenu */
        .submenu {
            display: none;
            list-style: none;
            padding-left: 36px;
            margin-top: 2px;
        }

        .submenu li a {
            padding: 7px 10px;
            font-size: 13px;
            color: var(--text2);
            border-left: none;
        }

        .submenu li a:hover {
            color: var(--accent2);
            background: transparent;
        }

        .has-submenu:hover .submenu {
            display: block;
        }

        .arrow {
            font-size: 11px;
            margin-left: auto;
            transition: 0.3s;
        }

        .has-submenu:hover .arrow {
            transform: rotate(180deg);
        }

        /* ════════════════════════
           MAIN
        ════════════════════════ */
        .main {
            margin-left: 230px;
            padding: 28px 34px;
            width: calc(100% - 230px);
        }

        /* Topbar */
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
            padding-bottom: 18px;
            border-bottom: 1px solid var(--border);
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: var(--text2);
            margin-bottom: 5px;
        }

        .breadcrumb a {
            color: var(--accent2);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .breadcrumb i {
            font-size: 9px;
        }

        .page-title {
            font-size: 19px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .page-title i {
            color: var(--accent2);
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 16px;
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text2);
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            text-decoration: none;
            transition: 0.2s;
        }

        .btn-back:hover {
            border-color: var(--accent);
            color: var(--accent2);
        }

        /* ID Badge */
        .id-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            background: rgba(124, 58, 237, 0.1);
            border: 1px solid rgba(124, 58, 237, 0.2);
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            color: var(--accent2);
            font-family: monospace;
            margin-bottom: 20px;
        }

        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 270px;
            gap: 18px;
            align-items: start;
            max-width: 920px;
        }

        /* Card */
        .card {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 16px;
        }

        .card-header {
            padding: 13px 20px;
            background: var(--bg3);
            border-bottom: 1px solid var(--border);
            font-size: 12px;
            font-weight: 600;
            color: var(--text2);
            text-transform: uppercase;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-header i {
            color: var(--accent2);
        }

        .card-body {
            padding: 20px;
        }

        /* Form Fields */
        .form-group {
            margin-bottom: 16px;
        }

        .form-group:last-child {
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text2);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 7px;
        }

        .req {
            color: var(--red);
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            background: var(--bg3);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 10px 13px;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            outline: none;
            transition: 0.2s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
        }

        .form-group textarea {
            min-height: 90px;
            resize: vertical;
        }

        .two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        /* Current Image */
        .current-img {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px;
            background: var(--bg3);
            border: 1px solid var(--border);
            border-radius: 8px;
            margin-bottom: 12px;
        }

        .current-img img {
            width: 54px;
            height: 54px;
            object-fit: cover;
            border-radius: 7px;
            border: 1px solid var(--border);
        }

        .current-img-info p {
            font-size: 13px;
            font-weight: 500;
            color: var(--text);
            margin-bottom: 2px;
        }

        .current-img-info span {
            font-size: 11px;
            color: var(--text2);
        }

        /* File Input */
        .file-label {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 13px;
            background: var(--bg3);
            border: 1px dashed var(--border);
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            color: var(--text2);
            transition: 0.2s;
        }

        .file-label:hover {
            border-color: var(--accent);
            color: var(--accent2);
        }

        .file-label i {
            color: var(--accent2);
        }

        /* Save Buttons */
        .btn-row {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .btn-save {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px;
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-save:hover {
            background: var(--accent2);
            transform: translateY(-1px);
        }

        .btn-cancel {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 10px;
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text2);
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            text-decoration: none;
            transition: 0.2s;
        }

        .btn-cancel:hover {
            border-color: var(--red);
            color: var(--red);
        }

        /* Danger Zone */
        .danger-card {
            background: rgba(239, 68, 68, 0.04);
            border: 1px solid rgba(239, 68, 68, 0.15);
            border-radius: 12px;
            padding: 16px 18px;
        }

        .danger-title {
            font-size: 12px;
            font-weight: 700;
            color: var(--red);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .danger-desc {
            font-size: 12px;
            color: var(--text2);
            margin-bottom: 12px;
            line-height: 1.6;
        }

        .btn-delete {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 10px;
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: var(--red);
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-delete:hover {
            background: rgba(239, 68, 68, 0.18);
        }

        ::-webkit-scrollbar {
            width: 5px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 3px;
        }
    </style>
</head>

<body>
    <?php include BASE_PATH . '/views/admin/layout/sidebar.php'; ?>

    <div class="main">
        <div class="topbar">
            <div>
                <div class="breadcrumb">
                    <a href="<?= BASE_URL ?>views/admin/products.php">Products</a>
                    <i class="fa fa-chevron-right"></i>
                    <span>Edit Product</span>
                </div>
                <div class="page-title">
                    <i class="fa fa-pen-to-square"></i> Edit Product
                </div>
            </div>
            <a href="<?= BASE_URL ?>views/admin/products.php" class="btn-back">
                <i class="fa fa-arrow-left"></i> Back to Products
            </a>
        </div>

        <div class="id-badge">
            <i class="fa fa-hashtag"></i> Product ID : <?php echo $product_id;  ?>
        </div>

        <!--------- Form Handling --------->
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-grid">

<!-- LEFT -->
<div>

    <!-- Card 1: Basic Info -->
    <div class="card">
        <div class="card-header">
            <i class="fa fa-circle-info"></i> Basic Information
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Product Name <span class="req">*</span></label>
                <input type="text" name="name"
                    value="<?php echo htmlspecialchars($name); ?>">
            </div>
            <div class="form-group">
                <label>Price (₹) <span class="req">*</span></label>
                <input type="number" name="price"
                    value="<?php echo htmlspecialchars($price); ?>">
            </div>
        </div>
    </div>
    <!-- ✅ Card 1 yahan bandhua -->

    <!-- Card 2: Image — BILKUL ALAG, bahar -->
    <div class="card">
        <div class="card-header">
            <i class="fa fa-image"></i> Product Image
        </div>
        <div class="card-body">
            <div class="current-img">
                <img src="<?php echo BASE_URL . $image; ?>" alt="Current" id="previewImg">
                <div class="current-img-info">
                    <p><?php echo htmlspecialchars($name); ?></p>
                    <span>Current image</span>
                </div>
            </div>
            <div class="form-group">
                <label>Replace Image</label>
                <label class="file-label">
                    <i class="fa fa-upload"></i>
                    Click to upload new image
                    <input type="file" name="image" accept="image/*"
                           style="display:none"
                           onchange="document.getElementById('previewImg').src = URL.createObjectURL(this.files[0])">
                </label>
            </div>
        </div>
    </div>
    <!-- ✅ Card 2 yahan band hua -->

</div>
<!-- LEFT end -->

<!-- RIGHT -->
<div>
    <div class="card">
        <div class="card-header">
            <i class="fa fa-floppy-disk"></i> Save Changes
        </div>
        <div class="card-body">
            <div class="btn-row">
                <button type="submit" class="btn-save">
                    <i class="fa fa-check"></i> Save Product
                </button>
                <a href="<?= BASE_URL ?>views/admin/products.php" class="btn-cancel">
                    <i class="fa fa-xmark"></i> Cancel
                </a>
            </div>
        </div>
    </div>
</div>
         
        </form>
</body>
</html>