<?php
require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/config/db.php';

$success = $error = '';

// ── DELETE ──
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    // Check if category has products
    $check = $conn->prepare("SELECT COUNT(*) as cnt FROM products WHERE category_id = ?");
    $check->bind_param("i", $del_id);
    $check->execute();
    $cnt = $check->get_result()->fetch_assoc()['cnt'];

    if ($cnt > 0) {
        $error = "Cannot delete — this category has {$cnt} product(s) linked to it.";
    } else {
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $del_id);
        $stmt->execute();
        $success = "Category deleted successfully.";
    }
}

// ── ADD ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name      = trim($_POST['name'] ?? '');
    $parent_id = $_POST['parent_id'] === '' ? null : intval($_POST['parent_id']);

    if (empty($name)) {
        $error = "Category name cannot be empty.";
    } else {
        $stmt = $conn->prepare("INSERT INTO categories (name, parent_id) VALUES (?, ?)");
        $stmt->bind_param("si", $name, $parent_id);
        $stmt->execute();
        $success = "Category '<strong>{$name}</strong>' added successfully!";
    }
}

// ── EDIT ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $edit_id   = intval($_POST['edit_id']);
    $name      = trim($_POST['name'] ?? '');
    $parent_id = $_POST['parent_id'] === '' ? null : intval($_POST['parent_id']);

    if (empty($name)) {
        $error = "Category name cannot be empty.";
    } else {
        $stmt = $conn->prepare("UPDATE categories SET name = ?, parent_id = ? WHERE id = ?");
        $stmt->bind_param("sii", $name, $parent_id, $edit_id);
        $stmt->execute();
        $success = "Category updated successfully!";
    }
}

// ── FETCH ALL ──
$all = $conn->query("SELECT * FROM categories ORDER BY parent_id, name")->fetch_all(MYSQLI_ASSOC);

// Build name map for showing parent name
$nameMap = [];
foreach ($all as $c) $nameMap[$c['id']] = $c['name'];

// Edit prefill
$editing = null;
if (isset($_GET['edit'])) {
    $eid = intval($_GET['edit']);
    foreach ($all as $c) {
        if ($c['id'] == $eid) { $editing = $c; break; }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Categories | Admin</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>icon.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/views/admin/dark-theme.css">
    <style>
        .layout { display: flex; }
        .page-grid {
            display: grid;
            grid-template-columns: 340px 1fr;
            gap: 24px;
            align-items: start;
        }
        .form-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
        }
        .form-card h3 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 18px;
            color: #111827;
            border-bottom: 2px solid #f3f0ff;
            padding-bottom: 10px;
        }
        .form-group { margin-bottom: 16px; }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 9px 13px;
            border: 1px solid #e5e7eb;
            border-radius: 7px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            outline: none;
            transition: border 0.2s;
        }
        .form-group input:focus,
        .form-group select:focus { border-color: #6d28d9; }
        .btn-submit {
            width: 100%;
            padding: 10px;
            background: #6d28d9;
            color: white;
            border: none;
            border-radius: 7px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: 0.2s;
        }
        .btn-submit:hover { background: #5b21b6; }
        .btn-edit {
            padding: 5px 12px;
            background: #ede9fe;
            color: #6d28d9;
            border: none;
            border-radius: 5px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            font-family: 'Poppins', sans-serif;
        }
        .btn-delete {
            padding: 5px 12px;
            background: #fee2e2;
            color: #dc2626;
            border: none;
            border-radius: 5px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            font-family: 'Poppins', sans-serif;
        }
        .btn-edit:hover   { background: #ddd6fe; }
        .btn-delete:hover { background: #fecaca; }
        .alert {
            padding: 11px 15px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 14px;
        }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .alert-error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        .badge-parent { background: #ede9fe; color: #6d28d9; }
        .badge-sub    { background: #dbeafe; color: #1d4ed8; }
        .badge-child  { background: #dcfce7; color: #15803d; }
        .editing-banner {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            color: #92400e;
            margin-bottom: 14px;
        }
    </style>
</head>
<body>
    <div class="layout">
        <!------ Adding SideBar Layout ------>
        <?php include "layout/sidebar.php"; ?>

        <div class="main">
            <div class="topbar">
                <h1 class="msg">Admin : <p class="greeting">Categories</p></h1>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?= $success ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><i class="fa fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="page-grid">

                <!-- ── ADD / EDIT FORM ── -->
                <div class="form-card">
                    <?php if ($editing): ?>
                        <div class="editing-banner">
                            <i class="fa fa-pen"></i> Editing: <strong><?= htmlspecialchars($editing['name']) ?></strong>
                            &nbsp;<a href="categories.php" style="color:#6d28d9;">Cancel</a>
                        </div>
                        <h3>Edit Category</h3>
                        <form method="POST">
                            <input type="hidden" name="action"  value="edit">
                            <input type="hidden" name="edit_id" value="<?= $editing['id'] ?>">
                    <?php else: ?>
                        <h3><i class="fa fa-plus"></i> Add New Category</h3>
                        <form method="POST">
                            <input type="hidden" name="action" value="add">
                    <?php endif; ?>

                        <div class="form-group">
                            <label>Category Name *</label>
                            <input type="text" name="name" required
                                   placeholder="e.g. Tablets"
                                   value="<?= htmlspecialchars($editing['name'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label>Parent Category <small style="color:#9ca3af;">(leave empty for top-level)</small></label>
                            <select name="parent_id">
                                <option value="">-- No Parent (Top Level) --</option>
                                <?php foreach ($all as $cat): ?>
                                    <?php if (!$editing || $cat['id'] != $editing['id']): ?>
                                        <option value="<?= $cat['id'] ?>"
                                            <?= ($editing && $editing['parent_id'] == $cat['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn-submit">
                            <?= $editing ? '<i class="fa fa-save"></i> Update Category' : '<i class="fa fa-plus"></i> Add Category' ?>
                        </button>
                    </form>
                </div>

                <!-- ── CATEGORIES TABLE ── -->
                <div class="table-box">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Parent</th>
                                <th>Level</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all as $cat):
                                // Determine level
                                if ($cat['parent_id'] === null) {
                                    $level = 'badge-parent'; $levelText = 'Top Level';
                                } else {
                                    // Check if parent itself has a parent
                                    $grandparent = null;
                                    foreach ($all as $c) {
                                        if ($c['id'] == $cat['parent_id']) { $grandparent = $c['parent_id']; break; }
                                    }
                                    $level = $grandparent === null ? 'badge-sub' : 'badge-child';
                                    $levelText = $grandparent === null ? 'Sub' : 'Child';
                                }
                            ?>
                            <tr>
                                <td><?= $cat['id'] ?></td>
                                <td><strong><?= htmlspecialchars($cat['name']) ?></strong></td>
                                <td><?= $cat['parent_id'] ? htmlspecialchars($nameMap[$cat['parent_id']] ?? '-') : '<span style="color:#9ca3af;">—</span>' ?></td>
                                <td><span class="badge <?= $level ?>"><?= $levelText ?></span></td>
                                <td style="display:flex;gap:8px;">
                                    <a href="?edit=<?= $cat['id'] ?>" class="btn-edit">
                                        <i class="fa fa-pen"></i> Edit
                                    </a>
                                    <a href="?delete=<?= $cat['id'] ?>" class="btn-delete"
                                       onclick="return confirm('Delete \'<?= htmlspecialchars($cat['name']) ?>\'?')">
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
    </div>
</body>
</html>