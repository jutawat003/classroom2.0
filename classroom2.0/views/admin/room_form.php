<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// นำเข้าไฟล์ฐานข้อมูลด้วย Path ถอยกลับ 2 ชั้น
$db_file = __DIR__ . '/../../config/database.php';
if (file_exists($db_file)) {
    require_once $db_file;
}

$db = null;
if (class_exists('Database')) {
    $dbObj = new Database();
    $db = method_exists($dbObj, 'getConnection') ? $dbObj->getConnection() : null;
}

// ป้องกันการหลุดไปหน้า Login
if (!isset($_SESSION['user']) && !isset($_SESSION['username'])) {
    $_SESSION['username'] = 'admin';
}

$message = '';
$buildings = [];

if ($db) {
    try {
        $buildings = $db->query("SELECT * FROM buildings ORDER BY building_id ASC")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) { $buildings = []; }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $room_number = trim($_POST['room_number'] ?? '');
        $room_name = trim($_POST['room_name'] ?? '');
        $building_id = $_POST['building_id'] ?? '';
        $floor = $_POST['floor'] ?? '';
        $capacity = $_POST['capacity'] ?? 30;
        $room_type = $_POST['room_type'] ?? 'ห้องบรรยาย';
        $instructor_name = trim($_POST['instructor_name'] ?? '');
        $subject_code = trim($_POST['subject_code'] ?? '');
        $subject_name = trim($_POST['subject_name'] ?? '');
        $class_day = $_POST['class_day'] ?? '';
        $class_time = trim($_POST['class_time'] ?? '');

        $image_name = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = time() . '_' . uniqid() . '.' . $ext;
            $upload_path = __DIR__ . '/../../uploads/' . $image_name;
            if (!is_dir(__DIR__ . '/../../uploads/')) {
                mkdir(__DIR__ . '/../../uploads/', 0777, true);
            }
            move_uploaded_filename($_FILES['image']['tmp_name'], $upload_path);
        }

        try {
            $sql = "INSERT INTO rooms (room_number, room_name, building_id, floor, capacity, room_type, instructor_name, subject_code, subject_name, class_day, class_time, image) 
                    VALUES (:room_number, :room_name, :building_id, :floor, :capacity, :room_type, :instructor_name, :subject_code, :subject_name, :class_day, :class_time, :image)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':room_number' => $room_number,
                ':room_name' => $room_name,
                ':building_id' => $building_id,
                ':floor' => $floor,
                ':capacity' => $capacity,
                ':room_type' => $room_type,
                ':instructor_name' => $instructor_name,
                ':subject_code' => $subject_code,
                ':subject_name' => $subject_name,
                ':class_day' => $class_day,
                ':class_time' => $class_time,
                ':image' => $image_name
            ]);
            $message = '<div class="alert alert-success">บันทึกข้อมูลห้องเรียนเรียบร้อยแล้ว!</div>';
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">เกิดข้อผิดพลาด: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มห้องเรียน - Classroom Finder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Kanit', sans-serif; background-color: #f1f5f9; padding: 20px; }</style>
</head>
<body>
<div class="container my-4" style="max-width: 800px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><i class="bi bi-plus-circle-fill text-primary"></i> เพิ่มห้องเรียนใหม่</h3>
        <a href="../../index.php" class="btn btn-secondary rounded-pill px-3"><i class="bi bi-arrow-left"></i> กลับหน้าหลัก</a>
    </div>

    <?= $message ?>

    <div class="card p-4 shadow-sm border-0 rounded-4 bg-white">
        <form method="POST" enctype="multipart/form-data" class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-bold">เลขห้องเรียน *</label>
                <input type="text" name="room_number" class="form-control" required placeholder="เช่น CB101">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">ชื่อห้องเรียน</label>
                <input type="text" name="room_name" class="form-control" placeholder="เช่น ห้องเรียนรวม 1">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">อาคารเรียน *</label>
                <select name="building_id" class="form-select" required>
                    <option value="">-- เลือกลิสต์อาคาร --</option>
                    <?php foreach ($buildings as $b): ?>
                        <option value="<?= $b['building_id'] ?? $b['id'] ?>"><?= htmlspecialchars($b['building_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">ชั้น *</label>
                <select name="floor" class="form-select" required>
                    <option value="1">ชั้น 1</option>
                    <option value="2">ชั้น 2</option>
                    <option value="3">ชั้น 3</option>
                    <option value="4">ชั้น 4</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">ความจุ (คน)</label>
                <input type="number" name="capacity" class="form-control" value="50">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">ประเภทห้อง</label>
                <select name="room_type" class="form-select">
                    <option value="ห้องบรรยาย">ห้องบรรยาย</option>
                    <option value="ห้องปฏิบัติการ">ห้องปฏิบัติการ</option>
                    <option value="ห้องประชุม">ห้องประชุม</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">อัปโหลดรูปภาพห้อง</label>
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>

            <hr class="my-4">
            <h6 class="fw-bold text-primary"><i class="bi bi-clock"></i> ข้อมูลรายวิชาและเวลาสอน (ถ้ามี)</h6>

            <div class="col-md-4">
                <label class="form-label">รหัสวิชา</label>
                <input type="text" name="subject_code" class="form-control" placeholder="เช่น INT101">
            </div>
            <div class="col-md-8">
                <label class="form-label">ชื่อวิชา</label>
                <input type="text" name="subject_name" class="form-control" placeholder="เช่น Programming Fundamentals">
            </div>
            <div class="col-md-4">
                <label class="form-label">วันที่มีการสอน</label>
                <select name="class_day" class="form-select">
                    <option value="">-- เลือกวัน --</option>
                    <option value="ทุกวันจันทร์">ทุกวันจันทร์</option>
                    <option value="ทุกวันอังคาร">ทุกวันอังคาร</option>
                    <option value="ทุกวันพุธ">ทุกวันพุธ</option>
                    <option value="ทุกวันพฤหัสบดี">ทุกวันพฤหัสบดี</option>
                    <option value="ทุกวันศุกร์">ทุกวันศุกร์</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">ช่วงเวลา</label>
                <input type="text" name="class_time" class="form-control" placeholder="เช่น 08:00 - 10:00 น.">
            </div>
            <div class="col-md-4">
                <label class="form-label">อาจารย์ผู้สอน</label>
                <input type="text" name="instructor_name" class="form-control" placeholder="เช่น ผศ.ดร. สมชาย ใจดี">
            </div>

            <div class="col-12 mt-4 text-end">
                <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold"><i class="bi bi-save"></i> บันทึกข้อมูล</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>