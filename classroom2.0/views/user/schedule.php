<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';

// ดึงข้อมูลห้องเรียนจาก Session เดียวกับ Admin
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

$rooms_data = $_SESSION['rooms'];
$student_name = $_SESSION['user']['username'] ?? 'user';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตารางเรียนทุกวิชา - Classroom Finder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; background-color: #f4f6f9; }
        .nav-header { background-color: #0f172a; }
        .card-custom { border: none; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .table-custom-header th { background-color: #f8fafc !important; color: #1e293b !important; font-weight: 600; border-bottom: 2px solid #e2e8f0; }
        .badge-schedule { background-color: #0284c7; color: #ffffff; font-size: 0.85rem; font-weight: 500; border-radius: 50rem; }
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
            <a href="#" class="btn btn-info text-white rounded-pill px-3"><i class="bi bi-calendar-plus"></i> จองห้องเรียน</a>
            <a href="schedule.php" class="btn btn-primary rounded-pill px-3"><i class="bi bi-calendar3"></i> ตารางเรียนทุกวิชา</a>
            <a href="#" class="btn btn-info text-white rounded-pill px-3"><i class="bi bi-exclamation-triangle"></i> แจ้งปัญหาการใช้งาน</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container my-4">
        <div class="card card-custom p-4 bg-white">
            
            <div class="mb-4">
                <h4 class="fw-bold text-primary mb-1">
                    <i class="bi bi-calendar-week"></i> ตารางเรียนทุกวิชาประจำภาคเรียน
                </h4>
                <p class="text-muted small mb-0">ตรวจสอบตารางเรียน รายวิชา อาคาร และอาจารย์ผู้สอนทั้งหมด</p>
            </div>

            <div class="table-responsive">
                <table class="table align-middle table-hover border mb-0">
                    <thead class="table-custom-header">
                        <tr>
                            <th class="py-3 px-3">ห้องเรียน</th>
                            <th class="py-3 px-3">อาคาร / ชั้น</th>
                            <th class="py-3 px-3">รายวิชา</th>
                            <th class="py-3 px-3">อาจารย์ผู้สอน</th>
                            <th class="py-3 px-3">วัน-เวลาเรียน</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rooms_data as $row): ?>
                            <tr>
                                <td class="fw-bold text-primary px-3">
                                    ห้อง <?= htmlspecialchars($row['room_number']) ?>
                                </td>
                                <td class="px-3">
                                    <?= htmlspecialchars($row['building']) ?> (ชั้น <?= htmlspecialchars($row['floor']) ?>)
                                </td>
                                <td class="fw-medium px-3">
                                    <?= htmlspecialchars($row['subject']) ?>
                                </td>
                                <td class="px-3 text-secondary">
                                    <i class="bi bi-person-badge me-1"></i><?= htmlspecialchars($row['instructor']) ?>
                                </td>
                                <td class="px-3">
                                    <span class="badge badge-schedule px-3 py-2">
                                        <i class="bi bi-clock me-1"></i><?= htmlspecialchars($row['schedule']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>