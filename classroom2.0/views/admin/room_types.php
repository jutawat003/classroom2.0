<?php
session_start();
require_once __DIR__ . '/../../middleware/authCheck.php';
checkAdmin();
require_once __DIR__ . '/../../config/database.php';

$db = (new Database())->getConnection();

// ดึงรายการประเภทห้องทั้งหมด
$stmt = $db->query("SELECT * FROM room_types ORDER BY room_type_id ASC");
$types = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการประเภทห้องเรียน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow-sm border-0 mx-auto" style="max-width: 700px; border-radius: 16px;">
        <div class="card-body p-4">
            
            <!-- หัวข้อ และ ปุ่มย้อนกลับไปหน้าเพิ่มห้องเรียน -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold m-0">จัดการประเภทห้องเรียน</h3>
                <a href="room_form.php" class="btn btn-outline-secondary btn-sm">← กลับหน้าเพิ่มห้องเรียน</a>
            </div>

            <!-- ฟอร์มเพิ่มประเภทห้อง -->
            <form action="../../controllers/AdminController.php?action=add_type" method="POST" class="mb-4">
                <label class="form-label fw-bold">เพิ่มประเภทห้องเรียนใหม่</label>
                <div class="input-group">
                    <input type="text" name="type_name" class="form-control" placeholder="กรอกชื่อประเภทห้อง เช่น ห้องสตูดิโอ" required>
                    <button type="submit" class="btn btn-primary px-4">+ เพิ่มประเภท</button>
                </div>
            </form>

            <!-- ตารางแสดงรายการ -->
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="30%">รหัสประเภท</th>
                        <th>ชื่อประเภท</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($types as $type): ?>
                        <tr>
                            <td><?= htmlspecialchars($type['room_type_id'] ?? $type['id'] ?? '') ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($type['type_name'] ?? $type['name'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>
    </div>
</div>
</body>
</html>