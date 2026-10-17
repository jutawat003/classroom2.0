<?php
require_once '../../middleware/authCheck.php';
checkAdmin();

$admin_name = $_SESSION['user']['username'] ?? 'admin';

// ข้อมูลจำลองห้องเรียน
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
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Classroom Finder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; background-color: #e2e8f0; }
        .top-nav { background-color: #0f172a; color: white; padding: 12px 24px; }
        .badge-admin { background-color: #ef4444; color: white; font-size: 0.75rem; font-weight: 700; border-radius: 4px; padding: 2px 8px; }
        .tab-menu { background-color: #0f172a; padding: 0 24px 10px; }
        .tab-link { color: white; background-color: #0284c7; border-radius: 6px 6px 0 0; padding: 8px 16px; text-decoration: none; font-weight: 500; font-size: 0.9rem; }
        .tab-link.active { background-color: white; color: #0f172a; font-weight: 700; }
        .hero-box { background-color: #1e293b; color: white; border-radius: 12px; padding: 24px; }
        .tool-btn { border: 1px solid #cbd5e1; border-radius: 8px; padding: 12px; background: white; text-decoration: none; color: #1e293b; font-weight: 600; display: block; text-align: center; }
        .tool-btn:hover { background-color: #f8fafc; color: #0284c7; }
        .room-img { width: 60px; height: 50px; object-fit: cover; border-radius: 6px; }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="top-nav d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-shield-lock-fill text-warning fs-5"></i>
            <span class="fw-bold fs-5">Classroom Finder</span>
            <span class="badge-admin">ADMIN PANEL</span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="small"><i class="bi bi-person-circle"></i> สวัสดีผู้ดูแลระบบ, <strong><?= htmlspecialchars($admin_name) ?></strong></span>
            <a href="../../logout.php" onclick="return confirm('ต้องการออกจากระบบหรือไม่?')" class="btn btn-outline-light btn-sm rounded-pill px-3">
                <i class="bi bi-box-arrow-right"></i> ออกจากระบบ
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="tab-menu d-flex gap-1">
        <a href="dashboard.php" class="tab-link active"><i class="bi bi-gear-fill"></i> ระบบจัดการสำหรับ Admin</a>
        <a href="rooms_manage.php" class="tab-link"><i class="bi bi-grid-3x3-gap-fill"></i> ตารางเรียนทุกวิชา</a>
        <a href="bookings.php" class="tab-link"><i class="bi bi-check-circle-fill"></i> จัดการการจองห้องเรียน</a>
        <a href="reports.php" class="tab-link"><i class="bi bi-exclamation-triangle-fill"></i> รับแจ้งปัญหาการใช้งาน</a>
    </div>

    <div class="container-fluid px-4 my-4">
        <!-- Banner -->
        <div class="hero-box d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1"><i class="bi bi-sliders"></i> ศูนย์ควบคุมผู้ดูแลระบบ (Admin Command Center)</h3>
                <p class="mb-0 opacity-75 small">จัดการห้องเรียน เพิ่มรูปภาพ จัดการอาคาร ชั้น ตารางเรียน และอาจารย์ผู้สอน</p>
            </div>
            <a href="room_form.php" class="btn btn-primary fw-bold rounded-pill px-4">
                <i class="bi bi-plus-lg"></i> เพิ่มห้องเรียนใหม่
            </a>
        </div>

        <!-- เครื่องมือการจัดการ -->
        <div class="bg-white p-3 rounded-3 shadow-sm mb-4">
            <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-grid-fill"></i> เครื่องมือการจัดการระบบ</h6>
            <div class="row g-3">
                <div class="col-md-3">
                    <a href="room_form.php" class="tool-btn"><i class="bi bi-plus-circle text-primary"></i> เพิ่มห้องเรียน / อัปโหลดรูป</a>
                </div>
                <div class="col-md-3">
                    <a href="buildings_manage.php" class="tool-btn"><i class="bi bi-building text-secondary"></i> จัดการอาคาร และ ชั้นเรียน</a>
                </div>
                <div class="col-md-3">
                    <a href="bookings.php" class="tool-btn"><i class="bi bi-calendar-check text-warning"></i> อนุมัติการจองห้องเรียน</a>
                </div>
                <div class="col-md-3">
                    <a href="reports.php" class="tool-btn"><i class="bi bi-exclamation-octagon text-danger"></i> รับแจ้งปัญหาการใช้งาน</a>
                </div>
            </div>
        </div>

        <!-- ตารางข้อมูลห้องเรียน -->
        <div class="bg-white p-3 rounded-3 shadow-sm">
            <h5 class="fw-bold mb-3"><i class="bi bi-table"></i> ตรวจสอบและจัดการข้อมูลห้องเรียนทั้งหมดในระบบ</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>รูปภาพ</th>
                            <th>เลขห้อง / ชื่อห้อง</th>
                            <th>อาคาร / ชั้น</th>
                            <th>ประเภทห้อง</th>
                            <th>รายวิชาเรียน / วัน-เวลา</th>
                            <th>อาจารย์ผู้สอน</th>
                            <th class="text-center">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION['rooms'] as $rm): ?>
                        <tr>
                            <td><img src="<?= $rm['image'] ?>" class="room-img" alt="room"></td>
                            <td>
                                <strong class="text-primary">ห้อง <?= $rm['room_number'] ?></strong><br>
                                <span class="small text-muted"><?= $rm['room_name'] ?></span>
                            </td>
                            <td>
                                <strong><?= $rm['building'] ?></strong><br>
                                <span class="small text-muted">ชั้น <?= $rm['floor'] ?></span>
                            </td>
                            <td>
                                <span class="badge <?= $rm['type'] === 'ห้องบรรยาย' ? 'bg-info' : 'bg-cyan' ?> text-white">
                                    <?= $rm['type'] ?>
                                </span>
                            </td>
                            <td>
                                <strong><?= $rm['subject'] ?></strong><br>
                                <span class="small text-primary"><i class="bi bi-clock"></i> <?= $rm['schedule'] ?></span>
                            </td>
                            <td><i class="bi bi-person-badge"></i> <?= $rm['instructor'] ?></td>
                            <td class="text-center">
                                <a href="room_edit.php?id=<?= $rm['id'] ?>" class="btn btn-warning btn-sm text-white fw-bold me-1">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="room_delete.php?id=<?= $rm['id'] ?>" onclick="return confirm('ยืนยันการลบห้องเรียนนี้?')" class="btn btn-danger btn-sm">
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
</body>
</html>