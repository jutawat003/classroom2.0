<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ถอยกลับ 2 ชั้นไปหา config/database.php
require_once __DIR__ . '/../../config/database.php';

$db = null;
if (class_exists('Database')) {
    /** @var dynamic $database */
    $database = new Database();
    $db = method_exists($database, 'getConnection') ? $database->getConnection() : (method_exists($database, 'connect') ? $database->connect() : null);
}

$id = $_GET['id'] ?? null;

$room = null;
if ($db && $id) {
    try {
        /** @var PDO $db */
        $stmt = $db->prepare("SELECT r.*, b.building_name, t.type_name, f.floor_number 
                              FROM rooms r
                              LEFT JOIN buildings b ON r.building_id = b.building_id
                              LEFT JOIN room_types t ON r.room_type_id = t.room_type_id
                              LEFT JOIN floors f ON r.floor_id = f.floor_id
                              WHERE r.room_id = ?");
        $stmt->execute([$id]);
        $room = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $room = null;
    }
}

// ข้อมูลตัวอย่างถ้าไม่มีใน DB
if (!$room) {
    $room = [
        'room_number' => 'CB10' . ($id ?? '1'),
        'room_name' => 'ห้องเรียนบรรยาย',
        'building_name' => 'อาคารเรียนรวม 1 (CB1)',
        'floor_number' => '1',
        'type_name' => 'ห้องบรรยาย',
        'capacity' => 60,
        'image' => ''
    ];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดห้อง <?= htmlspecialchars($room['room_number']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; background-color: #f8fafc; }
        .card-custom { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
        .room-img-container { height: 260px; overflow: hidden; border-radius: 12px; }
        .room-img { width: 100%; height: 100%; object-fit: cover; }
    </style>
</head>
<body>

    <div class="container my-5" style="max-width: 850px;">
        <a href="javascript:history.back()" class="btn btn-outline-secondary mb-3 rounded-pill px-3">
            <i class="bi bi-arrow-left"></i> ย้อนกลับ
        </a>

        <div class="card card-custom p-4 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="fw-bold text-primary mb-1">
                        <i class="bi bi-door-open-fill"></i> ห้อง <?= htmlspecialchars($room['room_number']) ?> 
                        <span class="fs-5 text-dark"><?= htmlspecialchars($room['room_name'] ?? '') ?></span>
                    </h3>
                    <span class="badge bg-primary rounded-pill px-3 py-2">
                        <?= htmlspecialchars($room['type_name'] ?? 'ทั่วไป') ?>
                    </span>
                </div>
            </div>

            <hr>

            <div class="row g-4 align-items-center">
                <div class="col-md-6">
                    <div class="list-group list-group-flush fs-6">
                        <div class="list-group-item bg-transparent px-0 py-2">
                            <i class="bi bi-building text-primary me-2"></i><strong>อาคาร:</strong> 
                            <?= htmlspecialchars($room['building_name'] ?? '-') ?>
                        </div>
                        <div class="list-group-item bg-transparent px-0 py-2">
                            <i class="bi bi-layers text-primary me-2"></i><strong>ชั้น:</strong> 
                            ชั้น <?= htmlspecialchars($room['floor_number'] ?? '-') ?>
                        </div>
                        <div class="list-group-item bg-transparent px-0 py-2">
                            <i class="bi bi-people text-primary me-2"></i><strong>ความจุรองรับ:</strong> 
                            <?= htmlspecialchars($room['capacity'] ?? '-') ?> คน
                        </div>
                        <div class="list-group-item bg-transparent px-0 py-2">
                            <i class="bi bi-info-circle text-primary me-2"></i><strong>สถานะ:</strong> 
                            <span class="text-success fw-bold">พร้อมใช้งาน</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="room-img-container shadow-sm bg-light d-flex align-items-center justify-content-center text-center">
                        <?php if (!empty($room['image']) && file_exists(__DIR__ . '/../../uploads/' . $room['image'])): ?>
                            <img src="../../uploads/<?= htmlspecialchars($room['image']) ?>" alt="รูปห้องเรียน" class="room-img">
                        <?php else: ?>
                            <div class="text-muted p-4">
                                <i class="bi bi-image fs-1 d-block mb-2 text-secondary"></i>
                                <span>ไม่มีรูปภาพประกอบ</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>