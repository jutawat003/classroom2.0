<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// เช็กสิทธิ์ Admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'ADMIN') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../../config/database.php';

$db = (new Database())->getConnection();

// เพิ่มข้อมูลชั้น
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_floor'])) {
    $building_id  = $_POST['building_id'];
    $floor_number = $_POST['floor_number'];
    $floor_name   = $_POST['floor_name'];

    $stmt = $db->prepare("INSERT INTO floors (building_id, floor_number, floor_name) VALUES (?, ?, ?)");
    $stmt->execute([$building_id, $floor_number, $floor_name]);
    header("Location: floors.php?building_id=" . $building_id);
    exit;
}

// ลบข้อมูลชั้น
if (isset($_GET['delete'])) {
    $floor_id = $_GET['delete'];
    $b_id     = $_GET['building_id'] ?? '';
    $stmt = $db->prepare("DELETE FROM floors WHERE floor_id = ?");
    $stmt->execute([$floor_id]);
    header("Location: floors.php?building_id=" . $b_id);
    exit;
}

// โหลดรายการอาคารทั้งหมด
$buildings = $db->query("SELECT * FROM buildings ORDER BY building_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// รับค่ากรองตามอาคาร
$selected_building = $_GET['building_id'] ?? ($buildings[0]['building_id'] ?? 0);

// ดึงข้อมูลชั้นตามอาคารที่เลือก
$stmt = $db->prepare("SELECT f.*, b.building_name 
                      FROM floors f 
                      JOIN buildings b ON f.building_id = b.building_id 
                      WHERE f.building_id = ? 
                      ORDER BY f.floor_number ASC");
$stmt->execute([$selected_building]);
$floors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการข้อมูลชั้น - Classroom Finder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar แถบเมนูด้านซ้าย (แบบเดียวกับหน้า Dashboard) -->
        <div class="col-md-3 col-lg-2 bg-dark text-white min-vh-100 p-3">
            <h4 class="text-center my-2"><i class="bi bi-gear-fill text-primary"></i> Admin Panel</h4>
            <hr>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item mb-1">
                    <a href="dashboard.php" class="nav-link text-white"><i class="bi bi-door-open me-2"></i>จัดการห้องเรียน</a>
                </li>
                <li class="nav-item mb-1">
                    <a href="buildings.php" class="nav-link text-white"><i class="bi bi-building me-2"></i>จัดการอาคาร</a>
                </li>
                <li class="nav-item mb-1">
                    <a href="floors.php" class="nav-link active"><i class="bi bi-layers me-2"></i>จัดการชั้น</a>
                </li>
                <li class="nav-item mb-1">
                    <a href="room_types.php" class="nav-link text-white"><i class="bi bi-tags me-2"></i>จัดการประเภทห้อง</a>
                </li>
            </ul>
            <hr>
            <a href="../../logout.php" class="btn btn-danger btn-sm w-100"><i class="bi bi-box-arrow-right me-1"></i> ออกจากระบบ</a>
        </div>

        <!-- Main Content พื้นที่จัดการข้อมูลด้านขวา -->
        <div class="col-md-9 col-lg-10 p-4 bg-light min-vh-100">
            <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
                <h2 class="m-0 fs-4 text-primary"><i class="bi bi-layers-fill"></i> จัดการข้อมูลชั้น</h2>
            </div>

            <!-- ฟอร์มเลือกอาคารเพื่อกรองข้อมูล -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="floors.php" class="row g-3 align-items-center">
                        <div class="col-auto">
                            <label class="col-form-label fw-bold">เลือกอาคารที่ต้องการจัดการ:</label>
                        </div>
                        <div class="col-md-4">
                            <select name="building_id" class="form-select" onchange="this.form.submit()">
                                <?php foreach ($buildings as $b): ?>
                                    <option value="<?= $b['building_id'] ?>" <?= $selected_building == $b['building_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($b['building_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ฟอร์มเพิ่มข้อมูลชั้น -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white fw-bold">เพิ่มชั้นใหม่ในอาคารนี้</div>
                <div class="card-body">
                    <form method="POST" action="floors.php" class="row g-3">
                        <input type="hidden" name="building_id" value="<?= $selected_building ?>">
                        <div class="col-md-4">
                            <input type="number" name="floor_number" class="form-control" placeholder="ลำดับชั้น (เช่น 1, 2, 3)" required>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="floor_name" class="form-control" placeholder="ชื่อชั้น (เช่น ชั้น 1 โซน A / ไม่ระบุก็ได้)">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="add_floor" class="btn btn-success w-100"><i class="bi bi-plus-lg"></i> เพิ่มชั้น</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ตารางแสดงรายการชั้น -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>ลำดับชั้น</th>
                                    <th>ชื่อเรียกชั้น</th>
                                    <th>อาคาร</th>
                                    <th class="text-center">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($floors) > 0): ?>
                                    <?php foreach ($floors as $f): ?>
                                    <tr>
                                        <td><strong class="text-primary">ชั้น <?= $f['floor_number'] ?></strong></td>
                                        <td><?= htmlspecialchars($f['floor_name'] ?: '-') ?></td>
                                        <td><?= htmlspecialchars($f['building_name']) ?></td>
                                        <td class="text-center">
                                            <a href="floors.php?delete=<?= $f['floor_id'] ?>&building_id=<?= $selected_building ?>" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirm('ยืนยันการลบชั้นนี้?')">
                                                <i class="bi bi-trash"></i> ลบ
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">ยังไม่มีข้อมูลชั้นในอาคารนี้</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>