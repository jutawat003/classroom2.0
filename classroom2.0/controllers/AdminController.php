<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$action = $_GET['action'] ?? '';
$db = (new Database())->getConnection();

// --- 1. ฟังก์ชันเพิ่มห้องเรียน ---
if ($action === 'add_room') {
    $roomNumber  = trim($_POST['room_number'] ?? '');
    $roomName    = trim($_POST['room_name'] ?? '');
    $subjectName = trim($_POST['subject_name'] ?? ''); // 👈 เพิ่มรับค่าชื่อวิชา
    $buildingId  = $_POST['building_id'] ?? null;
    $typeId      = $_POST['room_type_id'] ?? null;
    $floor       = $_POST['floor'] ?? null;
    $capacity    = $_POST['capacity'] ?? null;

    // การจัดการอัปโหลดรูปภาพ
    $imageName = null;
    if (!empty($_FILES['image']['name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imageName = time() . '_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../uploads/' . $imageName);
    }

    $sql = "INSERT INTO rooms (room_number, room_name, subject_name, building_id, room_type_id, floor, capacity, image) 
            VALUES (:room_number, :room_name, :subject_name, :building_id, :room_type_id, :floor, :capacity, :image)";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':room_number'  => $roomNumber,
        ':room_name'    => $roomName,
        ':subject_name' => $subjectName, // 👈 เพิ่มบันทึกลง SQL
        ':building_id'  => $buildingId,
        ':room_type_id' => $typeId,
        ':floor'        => $floor,
        ':capacity'     => $capacity,
        ':image'        => $imageName
    ]);

    header('Location: ../views/admin/dashboard.php');
    exit();
}

// --- 2. ฟังก์ชันแก้ไขห้องเรียน ---
if ($action === 'edit_room') {
    $roomId      = $_POST['room_id'] ?? null;
    $roomNumber  = trim($_POST['room_number'] ?? '');
    $roomName    = trim($_POST['room_name'] ?? '');
    $subjectName = trim($_POST['subject_name'] ?? ''); // 👈 เพิ่มรับค่าชื่อวิชา
    $buildingId  = $_POST['building_id'] ?? null;
    $typeId      = $_POST['room_type_id'] ?? null;
    $floor       = $_POST['floor'] ?? null;
    $capacity    = $_POST['capacity'] ?? null;
    $imageName   = $_POST['old_image'] ?? null;

    // หากมีการอัปโหลดรูปใหม่
    if (!empty($_FILES['image']['name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imageName = time() . '_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../uploads/' . $imageName);
    }

    $sql = "UPDATE rooms SET 
            room_number = :room_number, 
            room_name = :room_name, 
            subject_name = :subject_name, 
            building_id = :building_id, 
            room_type_id = :room_type_id, 
            floor = :floor, 
            capacity = :capacity, 
            image = :image 
            WHERE room_id = :room_id";

    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':room_number'  => $roomNumber,
        ':room_name'    => $roomName,
        ':subject_name' => $subjectName, // 👈 เพิ่มอัปเดตลง SQL
        ':building_id'  => $buildingId,
        ':room_type_id' => $typeId,
        ':floor'        => $floor,
        ':capacity'     => $capacity,
        ':image'        => $imageName,
        ':room_id'      => $roomId
    ]);

    header('Location: ../views/admin/dashboard.php');
    exit();
}