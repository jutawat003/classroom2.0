<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_path = file_exists(__DIR__ . '/../config/database.php') ? __DIR__ . '/../config/database.php' : __DIR__ . '/config/database.php';
if (file_exists($db_path)) {
    require_once $db_path;
}

$action = $_GET['action'] ?? '';

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $reporter_name = trim($_POST['reporter_name'] ?? '');
    $room_id = $_POST['room_id'] ?? 0;
    $issue_type = $_POST['issue_type'] ?? '';
    $description = trim($_POST['description'] ?? '');

    if (class_exists('Database')) {
        try {
            $dbObj = new Database();
            $db = $dbObj->getConnection();

            if ($db) {
                $stmt = $db->prepare("INSERT INTO reports (room_id, reporter_name, issue_type, description, status) VALUES (:rid, :rname, :itype, :desc, 'pending')");
                $stmt->execute([
                    ':rid' => $room_id,
                    ':rname' => $reporter_name,
                    ':itype' => $issue_type,
                    ':desc' => $description
                ]);
            }
        } catch (Exception $e) {}
    }

    echo "<script>alert('แจ้งปัญหาเรียบร้อยแล้ว เจ้าหน้าที่จะรีบดำเนินการตรวจสอบให้ครับ'); window.location.href='../index.php';</script>";
    exit();
}