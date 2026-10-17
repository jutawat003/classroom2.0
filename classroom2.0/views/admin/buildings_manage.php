<?php
// ดึงไฟล์ Database และ authCheck จากโฟลเดอร์หลัก
if (file_exists(__DIR__ . '/../../models/Database.php')) {
    require_once __DIR__ . '/../../models/Database.php';
} elseif (file_exists(__DIR__ . '/../../config/database.php')) {
    require_once __DIR__ . '/../../config/database.php';
}

if (file_exists(__DIR__ . '/../../middleware/authCheck.php')) {
    require_once __DIR__ . '/../../middleware/authCheck.php';
    if (function_exists('checkLogin')) {
        checkLogin();
    }
} else {
    session_start();
}

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
$db = null;
if (class_exists('Database')) {
    $database = new Database();
    $db = method_exists($database, 'getConnection') ? $database->getConnection() : (method_exists($database, 'connect') ? $database->connect() : null);
}

// จัดการการส่งฟอร์ม (เพิ่ม / แก้ไข / ลบ)
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $db) {
    // 1. เพิ่มอาคาร
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $building_name = trim($_POST['building_name'] ?? '');
        if (!empty($building_name)) {
            $stmt = $db->prepare("INSERT INTO buildings (building_name) VALUES (:name)");
            if ($stmt->execute([':name' => $building_name])) {
                $message = "เพิ่มอาคารเรียบร้อยแล้ว";
                $message_type = "success";
            }
        }
    }
    
    // 2. แก้ไขอาคาร
    if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $id = $_POST['id'] ?? 0;
        $building_name = trim($_POST['building_name'] ?? '');
        if (!empty($id) && !empty($building_name)) {
            $stmt = $db->prepare("UPDATE buildings SET building_name = :name WHERE id = :id");
            if ($stmt->execute([':name' => $building_name, ':id' => $id])) {
                $message = "แก้ไขข้อมูลอาคารเรียบร้อยแล้ว";
                $message_type = "success";
            }
        }
    }

    // 3. ลบอาคาร
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = $_POST['id'] ?? 0;
        if (!empty($id)) {
            $stmt = $db->prepare("DELETE FROM buildings WHERE id = :id");
            if ($stmt->execute([':id' => $id])) {
                $message = "ลบอาคารเรียบร้อยแล้ว";
                $message_type = "danger";
            }
        }
    }
}

// ดึงรายการอาคารทั้งหมด
$buildings = [];
if ($db) {
    try {
        $stmt = $db->prepare("SELECT * FROM buildings ORDER BY id DESC");
        $stmt->execute();
        $buildings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $buildings = [];
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการอาคาร - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; background-color: #f8fafc; }
        .card { border-radius: 12px; border: none; }
        .table th { background-color: #f1f5f9; }
    </style>
</head>
<body>

    <!-- Header / Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 py-3">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="dashboard.php">
                <i class="bi bi-building"></i> ระบบจัดการอาคารเรียน
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="dashboard.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left"></i> กลับหน้า Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-primary mb-0">
                <i class="bi bi-building-gear"></i> จัดการข้อมูลอาคาร
            </h3>
            <button class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#addBuildingModal">
                <i class="bi bi-plus-lg"></i> เพิ่มอาคารใหม่
            </button>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle-fill"></i> <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- ตารางแสดงรายการอาคาร -->
        <div class="card shadow-sm p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width: 15%;">รหัสอาคาร (ID)</th>
                            <th style="width: 60%;">ชื่ออาคาร</th>
                            <th style="width: 25%;" class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($buildings)): ?>
                            <?php foreach ($buildings as $b): ?>
                                <tr>
                                    <td class="fw-bold text-secondary">#<?= $b['id'] ?></td>
                                    <td class="fw-semibold text-dark"><?= htmlspecialchars($b['building_name']) ?></td>
                                    <td class="text-center">
                                        <!-- ปุ่มแก้ไข -->
                                        <button class="btn btn-warning btn-sm me-1 fw-bold text-dark" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editBuildingModal<?= $b['id'] ?>">
                                            <i class="bi bi-pencil-square"></i> แก้ไข
                                        </button>
                                        
                                        <!-- ฟอร์มลบ -->
                                        <form method="POST" class="d-inline" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบอาคารนี้?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm fw-bold">
                                                <i class="bi bi-trash"></i> ลบ
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Modal แก้ไขอาคาร -->
                                <div class="modal fade" id="editBuildingModal<?= $b['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square"></i> แก้ไขชื่ออาคาร</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="action" value="edit">
                                                    <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">ชื่ออาคาร</label>
                                                        <input type="text" name="building_name" class="form-control" value="<?= htmlspecialchars($b['building_name']) ?>" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                                    <button type="submit" class="btn btn-primary fw-bold">บันทึกการแก้ไข</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">ยังไม่มีข้อมูลอาคารในระบบ</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal เพิ่มอาคารใหม่ -->
    <div class="modal fade" id="addBuildingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle"></i> เพิ่มอาคารใหม่</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label fw-bold">ชื่ออาคาร</label>
                            <input type="text" name="building_name" class="form-control" placeholder="เช่น อาคารเรียนรวม 1 (CB1)" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary fw-bold">บันทึกข้อมูล</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>