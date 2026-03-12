<?php
require "../../config/config.php";
require_once BASE_PATH . '/config/db.php';

 if (isset($_POST['submit'])) {
    $product_count = 0;
        $file = $_FILES['csvfile']['tmp_name'];

        if (($handle = fopen($file, "r")) !== FALSE) {
            // skip first row of the csv file
            fgetcsv($handle);

            // fetching data into local variables
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $name = $data[0] ?? "";
                $price = $data[1] ?? "";
                $category_id = $data[2] ?? "";
                $image_path = $data[3] ?? "";

                $stmt = $conn->prepare("insert into products (name, price, image, category_id) values (?, ?, ?, ?)");

                /*----------- Add Product ---------*/ 
                if(!empty($name) && !empty($price)){
                    $stmt->bind_param("sdsi", $name, $price, $image_path, $category_id);                    
                    $stmt->execute();
                    $product_count++;
                }
            }
            fclose($handle);
        }
    }

?> 

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Import CSV | Admin</title>
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
            --text2: #9090a8;
            --accent: #7c3aed;
            --accent2: #a855f7;
            --green: #10b981;
            --red: #ef4444;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            min-height: 100vh;
        }

        /* ── Sidebar ── */
        .sidebar {
            width: 230px;
            background: var(--bg2);
            border-right: 1px solid var(--border);
            padding: 24px 0;
            position: fixed;
            height: 100vh;
        }

        .logo {
            font-size: 18px;
            font-weight: 700;
            text-align: center;
            padding-bottom: 24px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 16px;
        }

        .logo span {
            color: var(--accent2);
        }

        .menu {
            list-style: none;
            padding: 0 12px;
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
            color: #aaaacc;
            font-size: 14px;
            font-weight: 500;
            border-radius: 8px;
            transition: 0.2s;
        }

        .menu a:hover {
            background: rgba(124, 58, 237, 0.1);
            color: var(--text);
        }

        .menu a.active {
            background: rgba(124, 58, 237, 0.15);
            color: var(--accent2);
            border-left: 3px solid var(--accent2);
        }

        .menu a i {
            width: 16px;
            font-size: 13px;
        }

        /* ── Main ── */
        .main {
            margin-left: 230px;
            padding: 30px 36px;
            width: calc(100% - 230px);
        }

        /* ── Topbar ── */
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border);
        }

        .topbar h1 {
            font-size: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .topbar h1 span {
            color: var(--accent2);
        }

        /* ── Page Container ── */
        .page-wrap {
            max-width: 600px;
        }

        .page-heading {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .page-sub {
            font-size: 13px;
            color: var(--text2);
            margin-bottom: 28px;
        }

        /* ── Upload Box ── */
        .upload-box {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 28px;
            margin-bottom: 16px;
        }

        .drop-zone {
            border: 2px dashed var(--border);
            border-radius: 10px;
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            transition: 0.25s;
            background: var(--bg3);
            margin-bottom: 20px;
        }

        .drop-zone:hover,
        .drop-zone.dragover {
            border-color: var(--accent);
            background: rgba(124, 58, 237, 0.05);
        }

        .drop-zone input {
            display: none;
        }

        .drop-icon {
            font-size: 36px;
            color: var(--accent2);
            margin-bottom: 12px;
            display: block;
        }

        .drop-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 6px;
        }

        .drop-hint {
            font-size: 12px;
            color: var(--text2);
            margin-bottom: 16px;
        }

        .browse-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 20px;
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: 0.2s;
        }

        .browse-btn:hover {
            background: var(--accent2);
        }

        /* ── File Selected ── */
        .file-info {
            display: none;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: rgba(124, 58, 237, 0.08);
            border: 1px solid rgba(124, 58, 237, 0.2);
            border-radius: 8px;
            margin-bottom: 16px;
        }

        .file-info.show {
            display: flex;
        }

        .file-info i {
            color: var(--accent2);
            font-size: 20px;
        }

        .file-meta {
            flex: 1;
        }

        .file-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
        }

        .file-size {
            font-size: 11px;
            color: var(--text2);
            margin-top: 2px;
        }

        .clear-btn {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: var(--red);
            width: 26px;
            height: 26px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            transition: 0.2s;
        }

        .clear-btn:hover {
            background: rgba(239, 68, 68, 0.2);
        }

        /* ── Buttons Row ── */
        .btn-row {
            display: flex;
            gap: 10px;
        }

        .btn-submit {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
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

        .btn-submit:hover {
            background: var(--accent2);
            transform: translateY(-1px);
        }

        .btn-submit:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            transform: none;
        }

        .btn-template {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text2);
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: 0.2s;
            text-decoration: none;
        }

        .btn-template:hover {
            border-color: var(--accent);
            color: var(--accent2);
        }

        /* ── CSV Format Card ── */
        .format-box {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px 24px;
            margin-top: 16px;
        }

        .format-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--text2);
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .col-tags {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }

        .col-tag {
            padding: 3px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
            font-family: monospace;
        }

        .required {
            background: rgba(124, 58, 237, 0.12);
            color: var(--accent2);
            border: 1px solid rgba(124, 58, 237, 0.2);
        }

        .optional {
            background: rgba(245, 158, 11, 0.08);
            color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.15);
        }

        .csv-sample {
            background: var(--bg3);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 14px 16px;
            font-family: monospace;
            font-size: 12px;
            line-height: 2;
            overflow-x: auto;
        }

        .csv-sample .header {
            color: var(--accent2);
        }

        .csv-sample .row {
            color: var(--text2);
        }

        .csv-sample .c-name {
            color: #a78bfa;
        }

        .csv-sample .c-price {
            color: #34d399;
        }

        .csv-sample .c-cat {
            color: #60a5fa;
        }

        .csv-sample .c-img {
            color: #f59e0b;
        }

        /* ── Results ── */
        .results-box {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px 24px;
            margin-top: 20px;
        }

        .result-stats {
            display: flex;
            gap: 16px;
            margin-bottom: 20px;
        }

        .stat {
            flex: 1;
            background: var(--bg3);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 14px;
            text-align: center;
        }

        .stat-num {
            font-size: 26px;
            font-weight: 700;
        }

        .stat-lbl {
            font-size: 11px;
            color: var(--text2);
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat.total .stat-num {
            color: #60a5fa;
        }

        .stat.success .stat-num {
            color: var(--green);
        }

        .stat.fail .stat-num {
            color: var(--red);
        }

        /* Results table */
        .res-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .res-table th {
            padding: 10px 12px;
            text-align: left;
            color: var(--text2);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 1px solid var(--border);
        }

        .res-table td {
            padding: 10px 12px;
            border-bottom: 1px solid var(--border);
            color: var(--text);
        }

        .res-table tbody tr:last-child td {
            border-bottom: none;
        }

        .res-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        .ok-dot {
            color: var(--green);
            font-weight: 600;
        }

        .err-dot {
            color: var(--red);
            font-weight: 600;
        }

        /* Error log */
        .err-log {
            background: rgba(239, 68, 68, 0.05);
            border: 1px solid rgba(239, 68, 68, 0.15);
            border-radius: 8px;
            padding: 14px 16px;
            margin-top: 16px;
        }

        .err-log-title {
            font-size: 12px;
            font-weight: 600;
            color: var(--red);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .err-item {
            font-size: 12px;
            color: #f87171;
            padding: 4px 0;
            border-bottom: 1px solid rgba(239, 68, 68, 0.08);
            font-family: monospace;
        }

        .err-item:last-child {
            border-bottom: none;
        }

        /* Alert */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.08);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: var(--green);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: var(--red);
        }

        /* scrollbar */
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

        /* .upload_file{
            background-repeat: red;
        } */
    </style>
</head>

<body>

    <!-- ── Sidebar ── -->
    <div class="sidebar">
        <?php include BASE_PATH . '/views/admin/layout/sidebar.php' ?>
    </div>

    <!-- ── Main ── -->
    <div class="main">

        <!-- header of the page -->
        <div class="topbar">
            <h1><i class="fa fa-file-csv" style="color:var(--accent2)"></i> Admin : <span>Import CSV</span></h1>
        </div>

        <div class="page-wrap">
            <div class="page-heading">Import Products</div>
            <div class="page-sub">Upload a CSV file to bulk import products into the store.</div>

            <!-- Upload Box -->
            <div class="upload-box">
                <form method="POST" enctype="multipart/form-data" id="csvForm">
                    <input type="file" class="upload_file" name="csvfile" accept=".csv">
                    <br><br>
                    <input class="submit_btn" type="submit" name="submit" value="Upload">
                </form>
            </div>

            <!-- CSV Format Guide -->
            <div class="format-box">
                <div class="format-title">CSV Format</div>
                <div class="col-tags">
                    <span class="col-tag required">name *</span>
                    <span class="col-tag required">price *</span>
                    <span class="col-tag required">category_id *</span>
                    <span class="col-tag optional">image</span>
                </div>
                <div class="csv-sample">
                    <div class="header">name,price,category_id,image</div>
                    <div class="row"><span class="c-name">Dell XPS 13</span>,<span class="c-price">95000</span>,<span class="c-cat">9</span>,<span class="c-img">uploads/products/Dell XPS 13.webp</span></div>
                    <div class="row"><span class="c-name">iPhone 15 Pro</span>,<span class="c-price">134900</span>,<span class="c-cat">8</span>,<span class="c-img">uploads/products/iPhone 15 Pro.webp</span></div>
                </div>
            </div>

            <!-- success message -->
            <div class="success_msg">
                <?php 
                if($product_count > 0){?>
                    <p class="product_added_msg" style="color:#10b981;"> <?= $product_count ?> - Product Added Successfully </p>
                <?php }
                $product_count = 0;
                ?>
            </div>
        </div>
    </div>

</body>

</html>