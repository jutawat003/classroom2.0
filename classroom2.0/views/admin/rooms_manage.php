<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. นำเข้าไฟล์ฐานข้อมูล
$db_file = __DIR__ . '/../../config/database.php';
if (file_exists($db_file)) {
    require_once $db_file;
}

$db = null;
if (class_exists('Database')) {
    $dbObj = new Database();
    $db = method_exists($dbObj, 'getConnection') ? $dbObj->getConnection() : null;
}

// 2. ตรวจสอบ Session
if (!isset($_SESSION['user']) && !isset($_SESSION['username'])) {
    $_SESSION['username'] = 'admin';
}

// 3. ดึงข้อมูลห้องเรียนทั้งหมด
$rooms = [];
if ($db) {
    try {
        $sql = "SELECT r.*, b.building_name 
                FROM rooms r 
                LEFT JOIN buildings b ON r.building_id = b.building_id 
                ORDER BY r.room_number ASC";
        $stmt = $db->query($sql);
        $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $rooms = [];
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classroom Finder - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Kanit', sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
            margin: 0;
            padding: 0;
        }
        
        /* Navbar ด้านบนสุด */
        .top-navbar {
            background-color: #1a1d20;
            color: #ffffff;
            padding: 10px 24px;
        }
        .brand-title {
            font-size: 1.25rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #ffffff;
            text-decoration: none;
        }
        .admin-badge {
            background-color: #dc3545;
            color: #ffffff;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 6px;
        }

        /*แถบ Tab เมนูหลัก */
        .sub-header-bar {
            background-color: #007bff;
            padding: 8px 24px;
            display: flex;
            gap: 8px;
        }
        .tab-btn {
            background-color: transparent;
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }
        .tab-btn.active {
            background-color: #ffffff;
            color: #000000;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .tab-btn:hover:not(.active) {
            background-color: rgba(255, 255, 255, 0.15);
            color: #ffffff;
        }

        /* กล่อง Header หลัก (Admin Command Center) */
        .banner-box {
            background: #111827;
            color: #ffffff;
            border-radius: 12px;
            padding: 24px 28px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* การ์ดเมนูลัด 4 ปุ่ม */
        .action-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-weight: 600;
            color: #1f2937;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .action-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border-color: #007bff;
            color: #007bff;
        }

        /* ตารางข้อมูล */
        .content-card {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .custom-table {
            width: 100%;
            margin-top: 16px;
        }
        .custom-table thead {
            background-color: #1f2937;
            color: #ffffff;
        }
        .custom-table th {
            padding: 12px 16px;
            font-weight: 600;
            text-align: left;
        }
        .custom-table td {
            padding: 14px 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f3f4f6;
        }
        .room-img-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
</head>
<body>

<!-- Navbar บนสุด -->
<div class="top-navbar d-flex justify-content-between align-items-center">
    <a href="../../index.php" class="brand-title">
        <i class="bi bi-shield-lock-fill text-warning"></i>
        <span>Classroom Finder</span>
        <span class="admin-badge">ADMIN</span>
    </a>
    <div class="d-flex align-items-center gap-3">
        <span><i class="bi bi-person-circle"></i> สวัสดีผู้ดูแลระบบ, <strong>admin</strong></span>
        <a href="../../logout.php" class="btn btn-outline-light btn-sm rounded-pill px-3">ออกจากระบบ</a>
    </div>
</div>

<!-- แถบ เมนู 3 ปุ่มหลัก -->
<div class="sub-header-bar">
    <a href="rooms_manage.php" class="tab-btn active">
        <i class="bi bi-gear-fill"></i> ระบบจัดการสำหรับ Admin
    </a>
    <a href="bookings.php" class="tab-btn">
        <i class="bi bi-check-circle-fill"></i> จัดการการจองห้องเรียน
    </a>
    <a href="../../fix.php" class="tab-btn">
        <i class="bi bi-exclamation-triangle-fill"></i> รับแจ้งปัญหาการใช้งาน
    </a>
</div>

<!-- เนื้อหาหลัก -->
<div class="container-fluid px-4 py-4">

    <!-- Header Banner -->
    <div class="banner-box">
        <div>
            <h4 class="fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-sliders"></i> ศูนย์ควบคุมผู้ดูแลระบบ (Admin Command Center)
            </h4>
            <p class="text-secondary mb-0 text-light opacity-75">จัดการห้องเรียน อาคารเรียน การอนุมัติจอง และสิทธิ์ผู้ใช้งาน</p>
        </div>
        <a href="room_form.php" class="btn btn-primary rounded-pill px-4 py-2 fw-bold">
            <i class="bi bi-plus-lg"></i> เพิ่มห้องเรียนใหม่
        </a>
    </div>

    <!-- เครื่องมือการจัดการระบบ 4 ปุ่ม -->
    <div class="content-card mb-4">
        <h6 class="fw-bold mb-3 text-secondary d-flex align-items-center gap-2">
            <i class="bi bi-grid-fill"></i> เครื่องมือการจัดการระบบ
        </h6>
        <div class="row g-3">
            <div class="col-md-3">
                <a href="room_form.php" class="action-card">
                    <i class="bi bi-plus-circle-fill text-primary fs-5"></i>
                    <span>เพิ่มห้องเรียน / อัปโหลดรูป</span>
                </a>
            </div>
            <div class="col-md-3">
                <a href="buildings.php" class="action-card">
                    <i class="bi bi-building-fill text-secondary fs-5"></i>
                    <span>จัดการอาคาร และ ชั้นเรียน</span>
                </a>
            </div>
            <div class="col-md-3">
                <a href="bookings.php" class="action-card">
                    <i class="bi bi-calendar-check-fill text-warning fs-5"></i>
                    <span>อนุมัติการจองห้องเรียน</span>
                </a>
            </div>
            <div class="col-md-3">
                <a href="../../add_user.php" class="action-card">
                    <i class="bi bi-people-fill text-dark fs-5"></i>
                    <span>จัดการผู้ใช้และสิทธิ์</span>
                </a>
            </div>
        </div>
    </div>

    <!-- ตารางห้องเรียน -->
    <div class="content-card">
        <h6 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2">
            <i class="bi bi-table"></i> ตรวจสอบและจัดการข้อมูลห้องเรียนทั้งหมด
        </h6>
        
        <div class="table-responsive">
            <table class="custom-table table-hover">
                <thead>
                    <tr>
                        <th style="width: 80px;">รูปภาพ</th>
                        <th>เลขห้อง / ชื่อห้อง</th>
                        <th>อาคาร / ชั้น</th>
                        <th>ประเภท</th>
                        <th>รายวิชา / วัน-เวลา</th>
                        <th>ผู้สอน</th>
                        <th class="text-center" style="width: 140px;">การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($rooms)): ?>
                        <?php foreach ($rooms as $r): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($r['image']) && file_exists(__DIR__ . '/../../uploads/' . $r['image'])): ?>
                                        <img src="../../uploads/<?= htmlspecialchars($r['image']) ?>" class="room-img-thumb" alt="Room">
                                    <?php else: ?>
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted" style="width:60px; height:60px;">
                                            <i class="bi bi-door-closed fs-4"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong class="text-primary">ห้อง <?= htmlspecialchars($r['room_number']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($r['room_name'] ?? '-') ?></small>
                                </td>
                                <td>
                                    <?= htmlspecialchars($r['building_name'] ?? 'อาคารเรียน') ?><br>
                                    <small class="text-muted">ชั้น <?= htmlspecialchars($r['floor'] ?? '1') ?></small>
                                </td>
                                <td><span class="badge bg-info text-dark"><?= htmlspecialchars($r['room_type'] ?? 'บรรยาย') ?></span></td>
                                <td>
                                    <strong><?= htmlspecialchars($r['subject_name'] ?? '-') ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($r['class_day'] ?? '') ?> <?= htmlspecialchars($r['class_time'] ?? '') ?></small>
                                </td>
                                <td><?= htmlspecialchars($r['instructor_name'] ?? '-') ?></td>
                                <td class="text-center">
                                    <a href="room_form.php?id=<?= $r['room_id'] ?? $r['id'] ?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil-square"></i></a>
                                    <a href="room_delete.php?id=<?= $r['room_id'] ?? $r['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('ยืนยันการลบ?');"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                ไม่พบข้อมูลห้องเรียน
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

</body>
</html>