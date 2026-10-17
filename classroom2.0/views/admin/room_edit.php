<?php
require_once __DIR__ . '/../../middleware/authCheck.php';
checkAdmin();

require_once __DIR__ . '/../../config/database.php';

$db = (new Database())->getConnection();

$roomId = $_GET['id'] ?? null;
if (!$roomId) {
    header('Location: dashboard.php');
    exit();
}

// ดึงข้อมูลห้องเรียนที่ต้องการแก้ไข
$stmt = $db->prepare("SELECT * FROM rooms WHERE room_id = :id");
$stmt->execute([':id' => $roomId]);
$room = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$room) {
    header('Location: dashboard.php');
    exit();
}

// ดึงข้อมูลอาคารและประเภทห้องเรียน
$buildings = $db->query("SELECT * FROM buildings ORDER BY building_name ASC")->fetchAll(PDO::FETCH_ASSOC);
$types     = $db->query("SELECT * FROM room_types ORDER BY type_name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขห้องเรียน - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Kanit', sans-serif; }
        .card-custom { border-radius: 16px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-custom p-4 bg-white">
                <h4 class="fw-bold mb-4 text-warning">✏️ แก้ไขข้อมูลห้องเรียน</h4>

                <form action="../../controllers/AdminController.php?action=edit_room" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="room_id" value="<?= $room['room_id'] ?>">
                    <input type="hidden" name="old_image" value="<?= htmlspecialchars($room['image'] ?? '') ?>">

                    <div class="row g-3">
                        <!-- เลขห้อง -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">เลขห้อง <span class="text-danger">*</span></label>
                            <input type="text" name="room_number" class="form-control" value="<?= htmlspecialchars($room['room_number']) ?>" required>
                        </div>

                        <!-- ชื่อห้องเรียน -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">ชื่อห้องเรียน <span class="text-danger">*</span></label>
                            <input type="text" name="room_name" class="form-control" value="<?= htmlspecialchars($room['room_name']) ?>" required>
                        </div>

                        <!-- ช่องชื่อวิชา -->
                        <div class="col-12">
                            <label class="form-label fw-semibold text-primary">📚 ชื่อวิชาที่สอนในห้องนี้</label>
                            <input type="text" name="subject_name" class="form-control" placeholder="เช่น CS101 การเขียนโปรแกรมเบื้องต้น" value="<?= htmlspecialchars($room['subject_name'] ?? '') ?>">
                        </div>

                        <!-- อาคาร -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">อาคาร <span class="text-danger">*</span></label>
                            <select name="building_id" class="form-select" required>
                                <?php foreach ($buildings as $b): ?>
                                    <option value="<?= $b['building_id'] ?>" <?= $room['building_id'] == $b['building_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($b['building_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- ประเภทห้อง -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">ประเภทห้องเรียน <span class="text-danger">*</span></label>
                            <select name="room_type_id" class="form-select" required>
                                <?php foreach ($types as $t): ?>
                                    <option value="<?= $t['room_type_id'] ?>" <?= $room['room_type_id'] == $t['room_type_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($t['type_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- ชั้น -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">ชั้น</label>
                            <input type="number" name="floor" class="form-control" value="<?= htmlspecialchars($room['floor'] ?? '') ?>">
                        </div>

                        <!-- ความจุ -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">ความจุ (คน)</label>
                            <input type="number" name="capacity" class="form-control" value="<?= htmlspecialchars($room['capacity'] ?? '') ?>">
                        </div>

                        <!-- รูปภาพห้องเรียน -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">เปลี่ยนรูปภาพห้องเรียน</label>
                            <?php if (!empty($room['image'])): ?>
                                <div class="mb-2">
                                    <img src="../../uploads/<?= htmlspecialchars($room['image']) ?>" style="height: 100px; object-fit: cover;" class="rounded border">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="dashboard.php" class="btn btn-light rounded-pill px-4">ยกเลิก</a>
                        <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold">อัปเดตข้อมูล</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>