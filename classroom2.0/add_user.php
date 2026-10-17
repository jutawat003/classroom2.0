<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ไฟล์นี้อยู่โฟลเดอร์นอก ถอย 1 ชั้นไป config
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

$users = [];
if ($db) {
    try {
        $stmt = $db->query("SELECT * FROM users ORDER BY id DESC");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) { $users = []; }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการผู้ใช้งานและสิทธิ์ - Classroom Finder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Kanit', sans-serif; background-color: #f1f5f9; padding: 20px; }</style>
</head>
<body>
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><i class="bi bi-people-fill text-primary"></i> จัดการผู้ใช้งานและสิทธิ์</h3>
        <a href="index.php" class="btn btn-secondary rounded-pill px-3"><i class="bi bi-arrow-left"></i> กลับหน้าหลัก</a>
    </div>

    <div class="card p-4 shadow-sm border-0 rounded-4 bg-white">
        <h5 class="fw-bold mb-3">รายชื่อผู้ใช้งานทั้งหมด</h5>
        <div class="table-responsive">
            <table class="table table-hover border align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>ชื่อผู้ใช้งาน (Username)</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>สิทธิ์การใช้งาน (Role)</th>
                        <th class="text-center">การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= $u['id'] ?></td>
                                <td><?= htmlspecialchars($u['username'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($u['fullname'] ?? $u['name'] ?? '-') ?></td>
                                <td><span class="badge bg-primary"><?= htmlspecialchars($u['role'] ?? 'user') ?></span></td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm"><i class="bi bi-pencil"></i> แก้ไข</button>
                                    <button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> ลบ</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center text-muted">ไม่พบข้อมูลผู้ใช้งาน</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>