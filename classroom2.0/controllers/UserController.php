<?php
session_start();

// เปิดการแสดง Error ทั้งหมด
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ตั้งค่าเชื่อมต่อ Database
$dbHost = 'localhost';
$dbName = 'classroom_db';
$dbUser = 'root';
$dbPass = '';

try {
    $db = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8", $dbUser, $dbPass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("<h2 style='color:red;'>เชื่อมต่อ Database ไม่สำเร็จ: " . $e->getMessage() . "</h2>");
}

$action = $_GET['action'] ?? '';

// ==========================================
// บันทึก / อัปเดตวิชา (รองรับทั้ง add_schedule และ update_subject)
// ==========================================
if ($action === 'add_schedule' || $action === 'update_subject') {
    $roomId      = $_POST['room_id'] ?? null;
    $subjectName = trim($_POST['subject_name'] ?? '');

    if (!$roomId) {
        die("<h3 style='color:red;'>Error: ไม่พบค่า room_id ที่ส่งมาจากฟอร์ม</h3>");
    }

    try {
        // บันทึก/อัปเดตชื่อวิชาลงตาราง rooms
        $sql = "UPDATE rooms SET subject_name = :subject_name WHERE room_id = :room_id";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':subject_name' => $subjectName,
            ':room_id'      => $roomId
        ]);

        // เด้งกลับไปหน้าเดิมทันที
        if (isset($_SERVER['HTTP_REFERER'])) {
            header("Location: " . $_SERVER['HTTP_REFERER']);
        } else {
            header("Location: ../views/user/detail.php?id=" . $roomId);
        }
        exit();

    } catch (PDOException $e) {
        die("<h3 style='color:red;'>Error SQL: " . $e->getMessage() . "</h3>");
    }
} else {
    echo "<h3>ไม่มี action ที่ส่งมา (Action: " . htmlspecialchars($action) . ")</h3>";
}