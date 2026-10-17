<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ตรวจสอบตำแหน่ง URL เพื่อส่งไปที่ views/logout.php ถูกต้องแน่นอน
$script_name = $_SERVER['SCRIPT_NAME'];
$in_views = (strpos($script_name, '/views/') !== false);

// บังคับลิงก์ไปที่ views/logout.php (ไม่ออกไป auth/ แล้ว)
$logout_url = $in_views ? '../logout.php' : 'views/logout.php';

$username_display = $_SESSION['user']['username'] ?? $_SESSION['username'] ?? $_SESSION['user_id'] ?? 'ผู้ใช้งาน';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classroom Finder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; background-color: #f8fafc; }
        .top-navbar { background-color: #0f172a; color: white; padding: 12px 24px; }
    </style>
</head>
<body>

<div class="top-navbar d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-2 fs-5 fw-bold text-white">
        <i class="bi bi-geo-alt-fill text-info"></i> Classroom Finder
    </div>
    <div class="d-flex align-items-center gap-3">
        <span class="small text-white-50">
            <i class="bi bi-person-circle"></i> สวัสดี, <strong class="text-white"><?= htmlspecialchars($username_display) ?></strong>
        </span>
        
        <!-- ลิงก์ตรงไปที่ logout.php นอก auth -->
        <a href="<?= $logout_url ?>" class="btn btn-outline-light btn-sm rounded-pill px-3">
            <i class="bi bi-box-arrow-right"></i> ออกจากระบบ
        </a>
    </div>
</div>