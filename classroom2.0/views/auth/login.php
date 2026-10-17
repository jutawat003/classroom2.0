<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user'])) {
    if (($_SESSION['user']['role'] ?? '') === 'admin') {
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: ../../index.php");
    }
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // ตรวจสอบข้อมูลเข้าสู่ระบบ
    if ($username === 'admin' && $password === '1234') {
        $_SESSION['user'] = ['username' => 'admin', 'role' => 'admin'];
        header("Location: ../admin/dashboard.php");
        exit();
    } elseif (($username === 'user' || $username === 'student') && $password === '1234') {
        $_SESSION['user'] = ['username' => $username, 'role' => 'user'];
        header("Location: ../../index.php");
        exit();
    } else {
        $error = 'ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง!';
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เข้าสู่ระบบ - Classroom Finder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; background-color: #0f172a; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-box { background: white; border-radius: 16px; padding: 40px; width: 100%; max-width: 400px; }
    </style>
</head>
<body>
<div class="login-box shadow-lg">
    <h3 class="fw-bold text-center mb-1">เข้าสู่ระบบ</h3>
    <p class="text-muted text-center small mb-4">Classroom Finder System</p>
    
    <?php if ($error): ?>
        <div class="alert alert-danger p-2 small text-center"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="mb-3">
            <label class="form-label small fw-bold">ชื่อผู้ใช้งาน</label>
            <input type="text" name="username" class="form-control" placeholder="Username" required>
        </div>
        <div class="mb-4">
            <label class="form-label small fw-bold">รหัสผ่าน</label>
            <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 fw-bold py-2">เข้าสู่ระบบ</button>
    </form>
</div>
</body>
</html>