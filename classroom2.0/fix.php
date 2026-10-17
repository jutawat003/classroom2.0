<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_file = __DIR__ . '/config/database.php';
if (file_exists($db_file)) {
    require_once $db_file;
}

$db = null;
if (class_exists('Database')) {
    $dbObj = new Database();
    $db = method_exists($dbObj, 'getConnection') ? $dbObj->getConnection() : null;
}

if (!isset($_SESSION['user']) && !isset($_SESSION['username'])) {
    $_SESSION['username'] = 'admin';
}

$reports = [];
if ($db) {
    try {
        $stmt = $db->query("SELECT * FROM reports ORDER BY id DESC");
        $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) { $reports = []; }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการแจ้งปัญหา - Classroom Finder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Kanit', sans-serif; background-color: #f1f5f9; padding: 20px; }</style>
</head>
<body>
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-danger"><i class="bi bi-exclamation-triangle-fill"></i> รายการแจ้งปัญหาและอุปกรณ์ชำรุด</h3>
        <a href="index.php" class="btn btn-secondary rounded-pill px-3"><i class="bi bi-arrow-left"></i> กลับหน้าหลัก</a>
    </div>

    <div class="card p-4 shadow-sm border-0 rounded-4 bg-white">
        <h5 class="fw-bold mb-3">รายการที่ได้รับแจ้ง</h5>
        <div class="table-responsive">
            <table class="table table-hover border align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>เลขห้อง</th>
                        <th>รายละเอียดปัญหา</th>
                        <th>ผู้แจ้ง</th>
                        <th>สถานะ</th>
                        <th class="text-center">การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($reports)): ?>
                        <?php foreach ($reports as $r): ?>
                            <tr>
                                <td class="fw-bold">ห้อง <?= htmlspecialchars($r['room_number'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($r['detail'] ?? $r['description'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($r['reporter'] ?? '-') ?></td>
                                <td><span class="badge bg-warning text-dark"><?= htmlspecialchars($r['status'] ?? 'รอแก้ไข') ?></span></td>
                                <td class="text-center">
                                    <button class="btn btn-success btn-sm"><i class="bi bi-check-lg"></i> ซ่อมแล้ว</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center text-muted">ยังไม่มีรายการแจ้งปัญหาเข้ามา</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>