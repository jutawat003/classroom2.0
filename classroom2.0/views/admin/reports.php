<?php
require_once '../../middleware/authCheck.php';
checkAdmin();

$admin_name = $_SESSION['user']['username'] ?? 'admin';

if (!isset($_SESSION['reports'])) {
    $_SESSION['reports'] = [
        [
            'id' => 1,
            'reporter' => 'ดร.สมชาย ใจดี',
            'room' => 'CB101',
            'issue' => 'โปรเจกเตอร์เปิดไม่ติด',
            'details' => 'กดเปิดที่รีโมทแล้วไฟไม่เข้าเครื่อง ลองเสียบปลั๊กใหม่แล้วยังใช้งานไม่ได้',
            'created_at' => '2026-10-06 09:30 น.',
            'status' => 'pending'
        ],
        [
            'id' => 2,
            'reporter' => 'อ.วิภาดา รัตนกุล',
            'room' => 'CB102',
            'issue' => 'แอร์ไม่เย็น',
            'details' => 'แอร์มีแต่ลมออกมา ไม่มีลมเย็นเลยครับ',
            'created_at' => '2026-10-05 14:15 น.',
            'status' => 'resolved'
        ]
    ];
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $report_id = (int)$_GET['id'];
    foreach ($_SESSION['reports'] as &$rep) {
        if ($rep['id'] === $report_id) {
            $rep['status'] = ($_GET['action'] === 'resolve') ? 'resolved' : 'pending';
        }
    }
    header("Location: reports.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายการรับแจ้งปัญหาการใช้งาน - Admin Panel</title>
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
        <a href="dashboard.php" class="tab-link"><i class="bi bi-gear-fill"></i> ระบบจัดการสำหรับ Admin</a>
        <a href="rooms_manage.php" class="tab-link"><i class="bi bi-grid-3x3-gap-fill"></i> ตารางเรียนทุกวิชา</a>
        <a href="bookings.php" class="tab-link"><i class="bi bi-check-circle-fill"></i> จัดการการจองห้องเรียน</a>
        <a href="reports.php" class="tab-link active"><i class="bi bi-exclamation-triangle-fill"></i> รับแจ้งปัญหาการใช้งาน</a>
    </div>

    <div class="container-fluid px-4 my-4">
        <div class="bg-white p-4 rounded-3 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-danger mb-0"><i class="bi bi-exclamation-octagon-fill"></i> รายการรับแจ้งปัญหาการใช้งานห้องเรียน</h5>
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm rounded-pill"><i class="bi bi-arrow-left"></i> กลับหน้า Dashboard</a>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>ผู้แจ้งเรื่อง</th>
                            <th>ห้องเรียน</th>
                            <th>หัวข้อปัญหา</th>
                            <th>รายละเอียด</th>
                            <th>วันที่แจ้ง</th>
                            <th class="text-center">สถานะ</th>
                            <th class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION['reports'] as $index => $rep): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($rep['reporter']) ?></td>
                            <td><span class="badge bg-primary">ห้อง <?= htmlspecialchars($rep['room']) ?></span></td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($rep['issue']) ?></td>
                            <td class="text-muted small" style="max-width: 280px;"><?= htmlspecialchars($rep['details']) ?></td>
                            <td class="small"><?= htmlspecialchars($rep['created_at']) ?></td>
                            <td class="text-center">
                                <?php if ($rep['status'] === 'resolved'): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> แก้ไขเรียบร้อย</span>
                                <?php else: ?>
                                    <span class="badge bg-danger"><i class="bi bi-clock"></i> รอดำเนินการ</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($rep['status'] === 'pending'): ?>
                                    <a href="reports.php?action=resolve&id=<?= $rep['id'] ?>" class="btn btn-success btn-sm rounded-pill">
                                        <i class="bi bi-check-lg"></i> ทำเครื่องหมายว่าแก้ไขแล้ว
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
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