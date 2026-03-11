<?php
require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/config/db.php';

$results   = [];
$errors    = [];
$success   = false;
$totalRows = 0;
$imported  = 0;

// ── Handle CSV Upload ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "File upload failed. Please try again.";
    } elseif (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'csv') {
        $errors[] = "Only .csv files are allowed.";
    } elseif ($file['size'] > 2 * 1024 * 1024) {
        $errors[] = "File too large. Max allowed: 2MB.";
    } else {
        $handle = fopen($file['tmp_name'], 'r');
        $header = fgetcsv($handle); // skip header row

        while (($row = fgetcsv($handle)) !== false) {
            $totalRows++;

            if (count($row) < 3) {
                $errors[] = "Row {$totalRows}: Not enough columns (need name, price, category_id).";
                continue;
            }

            $name        = trim($row[0] ?? '');
            $price       = floatval($row[1] ?? 0);
            $category_id = intval($row[2] ?? 0);
            $image       = trim($row[3] ?? '');

            if (empty($name) || $price <= 0 || $category_id <= 0) {
                $errors[] = "Row {$totalRows}: Invalid data — name='{$name}', price={$price}, category_id={$category_id}";
                continue;
            }

            $stmt = $conn->prepare("INSERT INTO products (name, price, category_id, image) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sdis", $name, $price, $category_id, $image);

            if ($stmt->execute()) {
                $imported++;
                $results[] = ['status' => 'ok', 'name' => $name, 'price' => $price, 'row' => $totalRows];
            } else {
                $errors[] = "Row {$totalRows}: DB error — " . $stmt->error;
                $results[] = ['status' => 'error', 'name' => $name, 'row' => $totalRows];
            }
        }
        fclose($handle);
        $success = $imported > 0;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Import CSV | Admin</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>icon.png">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/views/admin/dark-theme.css">
    <style>

        /* ─── Page-specific overrides ─── */
        body { font-family: 'Syne', sans-serif; }

        .csv-wrapper {
            max-width: 860px;
        }

        /* ── Page Title ── */
        .page-title {
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #4a4a6a;
            margin-bottom: 6px;
        }

        .page-heading {
            font-size: 32px;
            font-weight: 800;
            color: #e8e8f8;
            margin-bottom: 32px;
            line-height: 1.1;
        }

        .page-heading span {
            background: linear-gradient(135deg, #a78bfa, #7c3aed, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ── Upload Card ── */
        .upload-card {
            background: #0f0f18;
            border: 1px solid #1e1e2e;
            border-radius: 18px;
            padding: 36px;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }

        /* corner accent */
        .upload-card::before {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 180px; height: 180px;
            background: radial-gradient(circle, rgba(124,58,237,0.12) 0%, transparent 70%);
            pointer-events: none;
        }

        /* ── Drop Zone ── */
        .drop-zone {
            border: 2px dashed #2a2a40;
            border-radius: 14px;
            padding: 52px 24px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #0a0a12;
            position: relative;
            overflow: hidden;
        }

        .drop-zone::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at center, rgba(124,58,237,0.06) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .drop-zone:hover,
        .drop-zone.dragover {
            border-color: #7c3aed;
            background: #0d0d1a;
        }

        .drop-zone:hover::before,
        .drop-zone.dragover::before {
            opacity: 1;
        }

        .drop-zone input[type="file"] {
            display: none;
        }

        .drop-icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, rgba(124,58,237,0.15), rgba(168,85,247,0.08));
            border: 1px solid rgba(124,58,237,0.25);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            transition: all 0.3s;
            position: relative;
        }

        .drop-zone:hover .drop-icon {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(124,58,237,0.25);
            border-color: rgba(124,58,237,0.5);
        }

        /* pulse ring */
        .drop-icon::after {
            content: '';
            position: absolute;
            inset: -6px;
            border-radius: 22px;
            border: 1px solid rgba(124,58,237,0.2);
            animation: pulse-ring 2.5s ease-in-out infinite;
        }

        @keyframes pulse-ring {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50%       { transform: scale(1.08); opacity: 1; }
        }

        .drop-title {
            font-size: 18px;
            font-weight: 700;
            color: #e8e8f8;
            margin-bottom: 8px;
        }

        .drop-subtitle {
            font-size: 13px;
            color: #4a4a6a;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .drop-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Syne', sans-serif;
            cursor: pointer;
            transition: all 0.25s;
        }

        .drop-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(124,58,237,0.35);
        }

        /* ── File Selected State ── */
        .file-selected {
            display: none;
            align-items: center;
            gap: 14px;
            padding: 14px 18px;
            background: rgba(124,58,237,0.08);
            border: 1px solid rgba(124,58,237,0.2);
            border-radius: 10px;
            margin-top: 20px;
        }

        .file-selected.show { display: flex; }

        .file-icon {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .file-info { flex: 1; }
        .file-name {
            font-size: 14px;
            font-weight: 600;
            color: #e8e8f8;
            font-family: 'DM Mono', monospace;
        }
        .file-size { font-size: 12px; color: #5a5a7a; margin-top: 2px; }

        .file-clear {
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.2);
            color: #ef4444;
            width: 28px; height: 28px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            transition: 0.2s;
        }

        .file-clear:hover { background: rgba(239,68,68,0.2); }

        /* ── CSV Format Guide ── */
        .format-card {
            background: #0a0a12;
            border: 1px solid #1e1e2e;
            border-radius: 14px;
            padding: 22px 24px;
            margin-top: 24px;
        }

        .format-title {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: #4a4a6a;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .format-title::before {
            content: '';
            width: 20px; height: 1px;
            background: #2a2a40;
        }

        .csv-preview {
            font-family: 'DM Mono', monospace;
            font-size: 13px;
            line-height: 1.9;
        }

        .csv-header {
            color: #7c3aed;
            font-weight: 500;
        }

        .csv-row { color: #6a6a8a; }

        .csv-row .col-name  { color: #a78bfa; }
        .csv-row .col-price { color: #34d399; }
        .csv-row .col-cat   { color: #60a5fa; }
        .csv-row .col-img   { color: #f59e0b; }

        .col-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            margin: 2px;
            font-family: 'DM Mono', monospace;
        }

        .col-required { background: rgba(167,139,250,0.1); color: #a78bfa; border: 1px solid rgba(167,139,250,0.2); }
        .col-optional { background: rgba(245,158,11,0.08); color: #f59e0b; border: 1px solid rgba(245,158,11,0.15); }

        /* ── Submit Button ── */
        .submit-row {
            display: flex;
            gap: 12px;
            margin-top: 24px;
            align-items: center;
        }

        .btn-import {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 13px 28px;
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            font-family: 'Syne', sans-serif;
            cursor: pointer;
            transition: all 0.25s;
            position: relative;
            overflow: hidden;
        }

        .btn-import::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, #a855f7, #7c3aed);
            opacity: 0;
            transition: 0.25s;
        }

        .btn-import:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(124,58,237,0.35);
        }

        .btn-import:hover::before { opacity: 1; }
        .btn-import span, .btn-import i { position: relative; z-index: 1; }

        .btn-import:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            transform: none;
        }

        .btn-template {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 13px 20px;
            background: transparent;
            color: #6a6a8a;
            border: 1px solid #1e1e2e;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Syne', sans-serif;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.25s;
        }

        .btn-template:hover {
            border-color: #7c3aed;
            color: #a78bfa;
            background: rgba(124,58,237,0.06);
        }

        /* ── Progress Bar ── */
        .progress-wrap {
            display: none;
            margin-top: 20px;
        }

        .progress-wrap.show { display: block; }

        .progress-label {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #4a4a6a;
            margin-bottom: 8px;
            font-family: 'DM Mono', monospace;
        }

        .progress-bar-bg {
            height: 4px;
            background: #1a1a2e;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #7c3aed, #a855f7, #c084fc);
            border-radius: 4px;
            width: 0%;
            transition: width 0.4s ease;
            animation: glow-bar 2s ease infinite;
        }

        @keyframes glow-bar {
            0%, 100% { box-shadow: 0 0 6px rgba(124,58,237,0.4); }
            50%       { box-shadow: 0 0 14px rgba(168,85,247,0.7); }
        }

        /* ── Results Section ── */
        .results-section {
            animation: fadeInUp 0.5s ease both;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .results-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .results-title {
            font-size: 16px;
            font-weight: 700;
            color: #e8e8f8;
        }

        /* Stats row */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }

        .stat-box {
            background: #0a0a12;
            border: 1px solid #1e1e2e;
            border-radius: 12px;
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: fadeInUp 0.4s ease both;
        }

        .stat-box:nth-child(1) { animation-delay: 0.1s; }
        .stat-box:nth-child(2) { animation-delay: 0.15s; }
        .stat-box:nth-child(3) { animation-delay: 0.2s; }

        .stat-icon {
            width: 40px; height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .stat-icon.total    { background: rgba(59,130,246,0.1);  border: 1px solid rgba(59,130,246,0.2); }
        .stat-icon.success  { background: rgba(16,185,129,0.1);  border: 1px solid rgba(16,185,129,0.2); }
        .stat-icon.failed   { background: rgba(239,68,68,0.1);   border: 1px solid rgba(239,68,68,0.2); }

        .stat-num {
            font-size: 24px;
            font-weight: 700;
            font-family: 'DM Mono', monospace;
            line-height: 1;
        }

        .stat-box.total   .stat-num { color: #60a5fa; }
        .stat-box.success .stat-num { color: #34d399; }
        .stat-box.failed  .stat-num { color: #f87171; }

        .stat-label { font-size: 12px; color: #4a4a6a; margin-top: 3px; }

        /* Results Table */
        .results-table-wrap {
            background: #0a0a12;
            border: 1px solid #1e1e2e;
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .results-table-wrap table { width: 100%; border-collapse: collapse; }

        .results-table-wrap thead { background: #0d0d1a; }

        .results-table-wrap th {
            padding: 12px 16px;
            font-size: 11px;
            font-weight: 700;
            color: #3a3a5a;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            border-bottom: 1px solid #1a1a2e;
            font-family: 'DM Mono', monospace;
        }

        .results-table-wrap td {
            padding: 11px 16px;
            font-size: 13px;
            color: #c0c0e0;
            border-bottom: 1px solid #0f0f1a;
            font-family: 'DM Mono', monospace;
        }

        .results-table-wrap tbody tr:hover { background: rgba(124,58,237,0.04); }
        .results-table-wrap tbody tr:last-child td { border-bottom: none; }

        .row-ok    { color: #34d399; }
        .row-error { color: #f87171; }

        .status-dot {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-dot::before {
            content: '';
            width: 6px; height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .status-dot.ok::before    { background: #34d399; box-shadow: 0 0 6px #34d399; }
        .status-dot.err::before   { background: #f87171; box-shadow: 0 0 6px #f87171; }

        /* Error log */
        .error-log {
            background: rgba(239,68,68,0.04);
            border: 1px solid rgba(239,68,68,0.12);
            border-radius: 12px;
            padding: 18px 20px;
        }

        .error-log-title {
            font-size: 12px;
            font-weight: 700;
            color: #f87171;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .error-item {
            font-family: 'DM Mono', monospace;
            font-size: 12px;
            color: #f87171;
            padding: 5px 0;
            border-bottom: 1px solid rgba(239,68,68,0.08);
            opacity: 0.8;
        }

        .error-item:last-child { border-bottom: none; }

    </style>
</head>
<body>

    <?php include "layout/sidebar.php"; ?>

    <div class="main">

        <div class="topbar">
            <h1 class="msg">
                <i class="fa fa-file-csv" style="color:var(--accent2)"></i>
                Admin : <span class="greeting">Import CSV</span>
            </h1>
            <div class="top-actions">
                <a href="<?= BASE_URL ?>/views/admin/products.php" style="text-decoration:none">
                    <div class="icon-btn" title="Products"><i class="fa fa-box" style="color:#6a6a8a;font-size:14px;"></i></div>
                </a>
            </div>
        </div>

        <div class="csv-wrapper">

            <p class="page-title">Data Management</p>
            <h2 class="page-heading">Import <span>Products</span><br>via CSV</h2>

            <!-- ── Upload Card ── -->
            <div class="upload-card">

                <form method="POST" enctype="multipart/form-data" id="csvForm">

                    <!-- Drop Zone -->
                    <div class="drop-zone" id="dropZone" onclick="document.getElementById('csvInput').click()">
                        <input type="file" name="csv_file" id="csvInput" accept=".csv">

                        <div class="drop-icon">📊</div>
                        <p class="drop-title">Drop your CSV file here</p>
                        <p class="drop-subtitle">
                            Drag & drop a .csv file or click to browse<br>
                            <small>Maximum file size: 2MB</small>
                        </p>
                        <button type="button" class="drop-btn" onclick="event.stopPropagation(); document.getElementById('csvInput').click()">
                            <i class="fa fa-folder-open"></i> Browse File
                        </button>
                    </div>

                    <!-- File Selected Preview -->
                    <div class="file-selected" id="fileSelected">
                        <div class="file-icon">📄</div>
                        <div class="file-info">
                            <div class="file-name" id="fileName">—</div>
                            <div class="file-size" id="fileSize">—</div>
                        </div>
                        <button type="button" class="file-clear" onclick="clearFile()" title="Remove">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>

                    <!-- Progress -->
                    <div class="progress-wrap" id="progressWrap">
                        <div class="progress-label">
                            <span>Uploading...</span>
                            <span id="progressPct">0%</span>
                        </div>
                        <div class="progress-bar-bg">
                            <div class="progress-bar-fill" id="progressFill"></div>
                        </div>
                    </div>

                    <!-- Submit Row -->
                    <div class="submit-row">
                        <button type="submit" class="btn-import" id="importBtn" disabled>
                            <i class="fa fa-upload"></i>
                            <span>Import Products</span>
                        </button>
                        <a href="#" onclick="downloadTemplate()" class="btn-template">
                            <i class="fa fa-download"></i> Download Template
                        </a>
                    </div>

                </form>

                <!-- Format Guide -->
                <div class="format-card">
                    <div class="format-title">CSV Format Guide</div>

                    <div style="margin-bottom:14px;display:flex;flex-wrap:wrap;gap:6px;">
                        <span class="col-pill col-required">name *</span>
                        <span class="col-pill col-required">price *</span>
                        <span class="col-pill col-required">category_id *</span>
                        <span class="col-pill col-optional">image (optional)</span>
                    </div>

                    <div class="csv-preview">
                        <div class="csv-header">name,price,category_id,image</div>
                        <div class="csv-row">
                            <span class="col-name">Dell XPS 13</span>,<span class="col-price">95000</span>,<span class="col-cat">9</span>,<span class="col-img">uploads/products/Dell XPS 13.webp</span>
                        </div>
                        <div class="csv-row">
                            <span class="col-name">iPhone 15 Pro</span>,<span class="col-price">134900</span>,<span class="col-cat">8</span>,<span class="col-img">uploads/products/iPhone 15 Pro.webp</span>
                        </div>
                        <div class="csv-row">
                            <span class="col-name">Samsung Galaxy S24</span>,<span class="col-price">79999</span>,<span class="col-cat">12</span>,<span class="col-img"></span>
                        </div>
                    </div>
                </div>

            </div><!-- /upload-card -->


            <?php if (!empty($results) || !empty($errors)): ?>
            <!-- ── Results Section ── -->
            <div class="results-section">

                <div class="results-header">
                    <div class="results-title">Import Results</div>
                </div>

                <!-- Stats -->
                <div class="stats-row">
                    <div class="stat-box total">
                        <div class="stat-icon total">📋</div>
                        <div>
                            <div class="stat-num"><?= $totalRows ?></div>
                            <div class="stat-label">Total Rows</div>
                        </div>
                    </div>
                    <div class="stat-box success">
                        <div class="stat-icon success">✅</div>
                        <div>
                            <div class="stat-num"><?= $imported ?></div>
                            <div class="stat-label">Imported</div>
                        </div>
                    </div>
                    <div class="stat-box failed">
                        <div class="stat-icon failed">❌</div>
                        <div>
                            <div class="stat-num"><?= count($errors) ?></div>
                            <div class="stat-label">Failed</div>
                        </div>
                    </div>
                </div>

                <!-- Results Table -->
                <?php if (!empty($results)): ?>
                <div class="results-table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Row</th>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $r): ?>
                            <tr>
                                <td style="color:#3a3a5a;">#<?= $r['row'] ?></td>
                                <td class="<?= $r['status'] === 'ok' ? 'row-ok' : 'row-error' ?>">
                                    <?= htmlspecialchars($r['name']) ?>
                                </td>
                                <td><?= isset($r['price']) ? '₹' . number_format($r['price'], 2) : '—' ?></td>
                                <td>
                                    <?php if ($r['status'] === 'ok'): ?>
                                        <span class="status-dot ok">Imported</span>
                                    <?php else: ?>
                                        <span class="status-dot err">Failed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <!-- Error Log -->
                <?php if (!empty($errors)): ?>
                <div class="error-log">
                    <div class="error-log-title">
                        <i class="fa fa-triangle-exclamation"></i>
                        Error Log (<?= count($errors) ?>)
                    </div>
                    <?php foreach ($errors as $e): ?>
                        <div class="error-item">→ <?= htmlspecialchars($e) ?></div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

            </div>
            <?php endif; ?>

        </div><!-- /csv-wrapper -->
    </div><!-- /main -->


    <script>
        const csvInput  = document.getElementById('csvInput');
        const dropZone  = document.getElementById('dropZone');
        const fileSelected = document.getElementById('fileSelected');
        const importBtn = document.getElementById('importBtn');

        // ── File Input Change ──
        csvInput.addEventListener('change', () => {
            const file = csvInput.files[0];
            if (file) showFile(file);
        });

        function showFile(file) {
            document.getElementById('fileName').textContent = file.name;
            document.getElementById('fileSize').textContent = formatSize(file.size);
            fileSelected.classList.add('show');
            importBtn.disabled = false;
        }

        function clearFile() {
            csvInput.value = '';
            fileSelected.classList.remove('show');
            importBtn.disabled = true;
        }

        function formatSize(bytes) {
            if (bytes < 1024)       return bytes + ' B';
            if (bytes < 1024*1024)  return (bytes/1024).toFixed(1) + ' KB';
            return (bytes/1024/1024).toFixed(2) + ' MB';
        }

        // ── Drag & Drop ──
        dropZone.addEventListener('dragover', e => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('dragover');
        });

        dropZone.addEventListener('drop', e => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            const file = e.dataTransfer.files[0];
            if (file && file.name.endsWith('.csv')) {
                const dt = new DataTransfer();
                dt.items.add(file);
                csvInput.files = dt.files;
                showFile(file);
            }
        });

        // ── Fake Progress on Submit ──
        document.getElementById('csvForm').addEventListener('submit', () => {
            if (csvInput.files.length === 0) return;
            const wrap = document.getElementById('progressWrap');
            const fill = document.getElementById('progressFill');
            const pct  = document.getElementById('progressPct');
            wrap.classList.add('show');
            importBtn.disabled = true;
            importBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i><span>Importing...</span>';

            let p = 0;
            const iv = setInterval(() => {
                p += Math.random() * 18;
                if (p > 90) { p = 90; clearInterval(iv); }
                fill.style.width = p + '%';
                pct.textContent  = Math.round(p) + '%';
            }, 120);
        });

        // ── Download Template ──
        function downloadTemplate() {
            const csv  = 'name,price,category_id,image\nDell XPS 13,95000,9,uploads/products/Dell XPS 13.webp\niPhone 15,79900,8,uploads/products/iPhone 15.webp';
            const blob = new Blob([csv], { type: 'text/csv' });
            const a    = document.createElement('a');
            a.href     = URL.createObjectURL(blob);
            a.download = 'products_template.csv';
            a.click();
        }
    </script>

</body>
</html>