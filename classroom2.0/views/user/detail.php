<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';

$room_id = $_GET['id'] ?? 1;

// ดึงข้อมูลห้องเรียนจาก Session
$rooms = $_SESSION['rooms'] ?? [];
$selected_room = null;

foreach ($rooms as $r) {
    if ($r['id'] == $room_id) {
        $selected_room = $r;
        break;
    }
}

// ถ้าไม่พบ ให้ใช้ข้อมูล Default
if (!$selected_room && !empty($rooms)) {
    $selected_room = $rooms[0];
}

$student_name = $_SESSION['user']['username'] ?? 'user';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดห้องเรียน - Classroom Finder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; background-color: #f4f6f9; }
        .nav-header { background-color: #0f172a; }
        .card-custom { border: none; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .room-detail-img { width: 100%; height: 320px; object-fit: cover; border-radius: 12px; }
    </style>
</head>
<body>

    <!-- Header / Navbar -->
    <nav class="nav-header text-white py-3 px-4 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <span class="fw-bold fs-4 text-warning"><i class="bi bi-geo-alt-fill"></i> Classroom Finder</span>
            <span class="badge bg-info text-dark">STUDENT / USER</span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span><i class="bi bi-person-circle"></i> สวัสดี, <?= htmlspecialchars($student_name) ?></span>
            <a href="../../logout.php" onclick="return confirm('ต้องการออกจากระบบหรือไม่?')" class="btn btn-outline-light btn-sm rounded-pill px-3">
                <i class="bi bi-box-arrow-right"></i> ออกจากระบบ
            </a>
        </div>
    </nav>

    <!-- Menu Bar -->
    <div class="container my-3">
        <div class="d-flex gap-2">
            <a href="search.php" class="btn btn-info text-white rounded-pill px-3"><i class="bi bi-search"></i> ค้นหาห้องเรียน</a>
            <a href="search.php" class="btn btn-info text-white rounded-pill px-3"><i class="bi bi-calendar-plus"></i> จองห้องเรียน</a>
            <a href="schedule.php" class="btn btn-info text-white rounded-pill px-3"><i class="bi bi-calendar3"></i> ตารางเรียนทุกวิชา</a>
        </div>
    </div>

    <!-- Details Content -->
    <div class="container my-4">
        <div class="card card-custom p-4 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold text-primary mb-0">
                    <i class="bi bi-door-open-fill"></i> รายละเอียดห้อง <?= htmlspecialchars($selected_room['room_number'] ?? 'CB101') ?>
                </h4>
                <a href="search.php" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-arrow-left"></i> ย้อนกลับ
                </a>
            </div>

            <div class="row g-4">
                <div class="col-md-5">
                    <img src="<?= htmlspecialchars($selected_room['image'] ?? 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=400') ?>" 
                         class="room-detail-img" alt="รูปห้องเรียน">
                </div>
                <div class="col-md-7">
                    <div class="p-3 border rounded-3 bg-light">
                        <h5 class="fw-bold text-dark mb-3"><?= htmlspecialchars($selected_room['room_name'] ?? 'ห้องเรียนรวม 1') ?></h5>
                        <p><strong>อาคาร/ชั้น:</strong> <?= htmlspecialchars($selected_room['building'] ?? 'อาคารเรียนรวม 1') ?> (ชั้น <?= htmlspecialchars($selected_room['floor'] ?? '1') ?>)</p>
                        <p><strong>ประเภทห้อง:</strong> <span class="badge bg-primary"><?= htmlspecialchars($selected_room['type'] ?? 'ห้องบรรยาย') ?></span></p>
                        <p><strong>รายวิชาที่ใช้สอน:</strong> <?= htmlspecialchars($selected_room['subject'] ?? '-') ?></p>
                        <p><strong>วัน-เวลาเรียน:</strong> <span class="text-success fw-bold"><?= htmlspecialchars($selected_room['schedule'] ?? '-') ?></span></p>
                        <p><strong>อาจารย์ผู้สอน:</strong> <?= htmlspecialchars($selected_room['instructor'] ?? '-') ?></p>
                    </div>

                    <div class="mt-4">
                        <button class="btn btn-success rounded-pill px-4 me-2" onclick="alert('ยื่นคำขอจองห้องเรียนเรียบร้อยแล้ว!')">
                            <i class="bi bi-calendar-check"></i> ยื่นขอจองห้องเรียนนี้
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>