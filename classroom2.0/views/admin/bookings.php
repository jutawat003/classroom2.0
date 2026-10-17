<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';

// Mockup Data สำหรับทดสอบการกดอนุมัติ/ปฏิเสธ
if (!isset($_SESSION['admin_mock_bookings'])) {
    $_SESSION['admin_mock_bookings'] = [
        [
            'booking_id' => 1,
            'room_number' => 'CB101',
            'fullname' => 'ดร. สมชาย ใจดี',
            'purpose' => 'สอนชดเชย วิชา INT101',
            'booking_date' => '2026-10-10',
            'time_slot' => '09:00 - 12:00 น.',
            'status' => 'pending'
        ],
        [
            'booking_id' => 2,
            'room_number' => 'CB102',
            'fullname' => 'อ. วิภาดา สุขใจ',
            'purpose' => 'ประชุมภาควิชาคอมพิวเตอร์',
            'booking_date' => '2026-10-12',
            'time_slot' => '13:00 - 15:00 น.',
            'status' => 'pending'
        ]
    ];
}

$action = $_GET['action'] ?? null;
$booking_id = $_GET['id'] ?? null;

if ($action && $booking_id) {
    $new_status = ($action === 'approve') ? 'approved' : (($action === 'reject') ? 'rejected' : null);
    if ($new_status) {
        foreach ($_SESSION['admin_mock_bookings'] as &$mb) {
            if ($mb['booking_id'] == $booking_id) {
                $mb['status'] = $new_status;
            }
        }
        header("Location: bookings.php");
        exit;
    }
}

$bookings = $_SESSION['admin_mock_bookings'];
$admin_name = $_SESSION['user']['username'] ?? 'admin';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการการจองห้องเรียน - Admin Panel</title>
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
        <a href="bookings.php" class="tab-link active"><i class="bi bi-check-circle-fill"></i> จัดการการจองห้องเรียน</a>
        <a href="reports.php" class="tab-link"><i class="bi bi-exclamation-triangle-fill"></i> รับแจ้งปัญหาการใช้งาน</a>
    </div>

    <div class="container-fluid px-4 my-4">
        <div class="bg-white p-4 rounded-3 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-warning mb-0"><i class="bi bi-clock-history"></i> รายการคำขอจองห้องเรียน (รอการอนุมัติ)</h5>
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm rounded-pill"><i class="bi bi-arrow-left"></i> กลับหน้า Dashboard</a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ห้องเรียน</th>
                            <th>ผู้ขอจอง</th>
                            <th>วัตถุประสงค์</th>
                            <th>วันที่ใช้ห้อง</th>
                            <th>ช่วงเวลา</th>
                            <th class="text-center">สถานะ</th>
                            <th class="text-center">ดำเนินการอนุมัติ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $row): ?>
                            <tr>
                                <td class="fw-bold text-primary">ห้อง <?= htmlspecialchars($row['room_number']) ?></td>
                                <td><?= htmlspecialchars($row['fullname']) ?></td>
                                <td><?= htmlspecialchars($row['purpose']) ?></td>
                                <td><?= htmlspecialchars($row['booking_date']) ?></td>
                                <td><?= htmlspecialchars($row['time_slot']) ?></td>
                                <td class="text-center">
                                    <?php if ($row['status'] === 'approved'): ?>
                                        <span class="badge bg-success px-3 py-2 rounded-pill"><i class="bi bi-check-circle"></i> อนุมัติแล้ว</span>
                                    <?php elseif ($row['status'] === 'rejected'): ?>
                                        <span class="badge bg-danger px-3 py-2 rounded-pill"><i class="bi bi-x-circle"></i> ปฏิเสธแล้ว</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="bi bi-hourglass-split"></i> รออนุมัติ</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($row['status'] === 'pending'): ?>
                                        <a href="bookings.php?action=approve&id=<?= $row['booking_id'] ?>" 
                                           class="btn btn-success btn-sm rounded-pill px-3 me-1"
                                           onclick="return confirm('อนุมัติคำขอนี้?');">
                                            <i class="bi bi-check-lg"></i> อนุมัติ
                                        </a>
                                        <a href="bookings.php?action=reject&id=<?= $row['booking_id'] ?>" 
                                           class="btn btn-outline-danger btn-sm rounded-pill px-3"
                                           onclick="return confirm('ปฏิเสธคำขอนี้?');">
                                            <i class="bi bi-x-lg"></i> ปฏิเสธ
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">ดำเนินการแล้ว</span>
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