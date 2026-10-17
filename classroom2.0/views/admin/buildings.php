<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================
// 1. ตรวจสอบ Session (ป้องกันการเด้งไปหน้า Login)
// ==========================================
if (!isset($_SESSION['user']) && !isset($_SESSION['username']) && !isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$username_display = $_SESSION['user']['username'] ?? $_SESSION['username'] ?? 'admin';

// ==========================================
// 2. เชื่อมต่อฐานข้อมูล (Database Connection)
// ==========================================
$db = null;
$db_path = file_exists(__DIR__ . '/../../config/database.php') ? __DIR__ . '/../../config/database.php' : __DIR__ . '/../config/database.php';

if (file_exists($db_path)) {
    require_once $db_path;
    if (class_exists('Database')) {
        $dbObj = new Database();
        $db = method_exists($dbObj, 'getConnection') ? $dbObj->getConnection() : null;
    }
}

// ==========================================
// 3. จัดการการเพิ่ม / ลบ อาคารเรียน
// ==========================================
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_building'])) {
    $building_name = trim($_POST['building_name'] ?? '');
    $floors_count = (int)($_POST['floors_count'] ?? 1);

    if (!empty($building_name) && $db) {
        try {
            $stmt = $db->prepare("INSERT INTO buildings (building_name, total_floors) VALUES (?, ?)");
            $stmt->execute([$building_name, $floors_count]);
            $message = "เพิ่มอาคารเรียนเรียบร้อยแล้ว!";
            $message_type = "success";
        } catch (Exception $e) {
            $message = "เกิดข้อผิดพลาด: " . $e->getMessage();
            $message_type = "danger";
        }
    }
}

if (isset($_GET['delete_id']) && $db) {
    $del_id = (int)$_GET['delete_id'];
    try {
        $stmt = $db->prepare("DELETE FROM buildings WHERE building_id = ? OR id = ?");
        $stmt->execute([$del_id, $del_id]);
        header("Location: buildings.php");
        exit();
    } catch (Exception $e) {
        $message = "ไม่สามารถลบได้ เนื่องจากมีห้องเรียนใช้งานอาคารนี้อยู่";
        $message_type = "danger";
    }
}

// ==========================================
// 4. ดึงข้อมูลอาคารเรียน
// ==========================================
$buildings = [];
if ($db) {
    try {
        $stmt = $db->query("SELECT * FROM buildings ORDER BY building_id ASC");
        $buildings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $buildings = [];
    }
}

// ข้อมูลสำรองกรณีไม่มี DB
if (empty($buildings)) {
    $buildings = [
        ['building_id' => 1, 'building_name' => 'อาคารเรียนรวม 1 (CB1)', 'total_floors' => 4],
        ['building_id' => 2, 'building_name' => 'อาคารปฏิบัติการเทคโนโลยี (CB2)', 'total_floors' => 5],
        ['building_id' => 3, 'building_name' => 'อาคารศูนย์วิทยบริการ', 'total_floors' => 3]
    ];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการอาคารเรียนและชั้นเรียน - Classroom Finder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; background-color: #f1f5f9; }
        .top-navbar { background-color: #0f172a; color: white; padding: 12px 24px; }
        .card-custom { border: none; border-radius: 14px; }
    </style>
</head>
<body>

    <!-- Header Navbar -->
    <div class="top-navbar d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2 fs-5 fw-bold">
            <i class="bi bi-building-fill text-warning"></i> ระบบจัดการอาคารเรียนและชั้นเรียน
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="small"><i class="bi bi-person-circle"></i> ผู้ดูแลระบบ: <strong><?= htmlspecialchars($username_display) ?></strong></span>
            <a href="../../index.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left"></i> กลับหน้าหลัก Admin
            </a>
        </div>
    </div>

    <div class="container my-4">

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i><?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- ฟอร์มเพิ่มอาคารเรียน -->
            <div class="col-md-4">
                <div class="card card-custom shadow-sm p-4 bg-white">
                    <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-plus-circle-fill"></i> เพิ่มอาคารเรียนใหม่</h5>
                    <form method="POST" action="buildings.php">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">ชื่ออาคารเรียน</label>
                            <input type="text" name="building_name" class="form-control" placeholder="เช่น อาคารเรียนรวม 1 (CB1)" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">จำนวนชั้นทั้งหมด</label>
                            <input type="number" name="floors_count" class="form-control" value="1" min="1" max="50" required>
                        </div>
                        <button type="submit" name="add_building" class="btn btn-primary w-100 fw-bold rounded-pill">
                            <i class="bi bi-save"></i> บันทึกข้อมูลอาคาร
                        </button>
                    </form>
                </div>
            </div>

            <!-- ตารางแสดงรายการอาคารเรียน -->
            <div class="col-md-8">
                <div class="card card-custom shadow-sm p-4 bg-white">
                    <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-building text-primary"></i> รายการอาคารเรียนและชั้นเรียนทั้งหมด</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle border">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 80px;">รหัส</th>
                                    <th>ชื่ออาคารเรียน</th>
                                    <th class="text-center" style="width: 120px;">จำนวนชั้น</th>
                                    <th class="text-center" style="width: 120px;">การจัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($buildings as $b): 
                                    $bid = $b['building_id'] ?? $b['id'];
                                ?>
                                    <tr>
                                        <td class="fw-bold text-secondary">#<?= htmlspecialchars($bid) ?></td>
                                        <td>
                                            <span class="fw-bold text-dark"><?= htmlspecialchars($b['building_name']) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info text-dark fs-6"><?= htmlspecialchars($b['total_floors'] ?? '-') ?> ชั้น</span>
                                        </td>
                                        <td class="text-center">
                                            <a href="buildings.php?delete_id=<?= $bid ?>" class="btn btn-outline-danger btn-sm rounded-circle" onclick="return confirm('ยืนยันการลบอาคารเรียนนี้?');" title="ลบอาคาร">
                                                <i class="bi bi-trash"></i>
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

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>