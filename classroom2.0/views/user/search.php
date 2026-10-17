<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['rooms'])) {
    $_SESSION['rooms'] = [
        [
            'id' => 1,
            'room_number' => 'CB101',
            'room_name' => 'ห้องเรียนรวม 1',
            'building' => 'อาคารเรียนรวม 1 (CB1)',
            'floor' => '1',
            'type' => 'ห้องบรรยาย',
            'subject' => '[INT101] Programming Fundamentals',
            'schedule' => 'ทุกวันจันทร์ 08:00 - 10:00 น.',
            'instructor' => 'ผศ.ดร. สมชาย ใจดี',
            'image' => 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=200&auto=format&fit=crop'
        ],
        [
            'id' => 2,
            'room_number' => 'CB102',
            'room_name' => 'ห้องปฏิบัติการคอมพิวเตอร์ 2',
            'building' => 'อาคารเรียนรวม 1 (CB1)',
            'floor' => '1',
            'type' => 'ห้องปฏิบัติการ',
            'subject' => '[GEN111] Man and Philosophy',
            'schedule' => 'ทุกวันอังคาร 10:00 - 12:00 น.',
            'instructor' => 'อ.วิภาดา รัตนกุล',
            'image' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?w=200&auto=format&fit=crop'
        ]
    ];
}

$rooms = $_SESSION['rooms'];
$student_name = $_SESSION['user']['username'] ?? 'user';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ค้นหาห้องเรียน - Classroom Finder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; background-color: #f4f6f9; }
        .nav-header { background-color: #0f172a; }
        .card-room { border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.2s; }
        .card-room:hover { transform: translateY(-4px); }
        .room-img-box { height: 180px; background-color: #e2e8f0; }
        .room-img-box img { width: 100%; height: 100%; object-fit: cover; }
        .search-banner { background-color: #0284c7; border-radius: 12px; }
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
            <a href="search.php" class="btn btn-primary rounded-pill px-3"><i class="bi bi-search"></i> ค้นหาห้องเรียน</a>
            <a href="#" class="btn btn-info text-white rounded-pill px-3"><i class="bi bi-calendar-plus"></i> จองห้องเรียน</a>
            <a href="schedule.php" class="btn btn-info text-white rounded-pill px-3"><i class="bi bi-calendar3"></i> ตารางเรียนทุกวิชา</a>
            <a href="#" class="btn btn-info text-white rounded-pill px-3"><i class="bi bi-exclamation-triangle"></i> แจ้งปัญหาการใช้งาน</a>
        </div>
    </div>

    <div class="container my-4">
        <!-- Search Banner -->
        <div class="search-banner p-4 text-white text-center mb-4">
            <h4 class="fw-bold mb-1"><i class="bi bi-building"></i> ค้นหาห้องเรียนและตรวจสอบสถานะ</h4>
            <p class="mb-0 opacity-75">ค้นหาห้องเรียนตามหมายเลข อาคาร หรือชั้น เพื่อดูรายละเอียด</p>
        </div>

        <!-- Room Grid -->
        <div class="row g-4">
            <?php foreach ($rooms as $room): ?>
                <?php $roomId = $room['id'] ?? 1; ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card card-room h-100 bg-white">
                        <div class="room-img-box d-flex align-items-center justify-content-center">
                            <?php if (!empty($room['image'])): ?>
                                <img src="<?= htmlspecialchars($room['image']) ?>" alt="รูปห้องเรียน">
                            <?php else: ?>
                                <div class="text-center text-secondary">
                                    <i class="bi bi-door-open fs-1"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body d-flex flex-column justify-content-between p-3">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="fw-bold text-primary mb-0">ห้อง <?= htmlspecialchars($room['room_number']) ?></h5>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($room['type']) ?></span>
                                </div>
                                <p class="text-danger small mb-2"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($room['building']) ?> (ชั้น <?= htmlspecialchars($room['floor']) ?>)</p>
                                
                                <p class="small text-muted mb-1"><strong>รายวิชา:</strong> <?= htmlspecialchars($room['subject']) ?></p>
                                <p class="small text-success mb-3"><strong>เวลาเรียน:</strong> <?= htmlspecialchars($room['schedule']) ?></p>
                            </div>
                            
                            <a href="detail.php?id=<?= $roomId ?>" class="btn btn-primary rounded-pill w-100">
                                <i class="bi bi-info-circle"></i> ดูรายละเอียดห้องเรียน
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>