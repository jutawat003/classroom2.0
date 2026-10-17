<?php
// config/database.php
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/classroom/');
}

$host = 'localhost';$dbname = 'classroom';
$username = 'root';$password = '';

try {
    // 1. เชื่อมต่อเข้า MySQL Server หลักก่อน (ยังไม่ระบุ dbname)
    $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. สั่งสร้าง Database ชื่อ classroom อัตโนมัติถ้ายังไม่มี
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // 3. สลับไปใช้ Database classroom
    $pdo->exec("USE `$dbname`");

} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}
?>