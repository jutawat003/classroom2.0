<?php
require_once __DIR__ . '/middleware/authCheck.php';

// ตรวจสอบพาธไฟล์ Database ว่าอยู่ใน models หรือ config
if (file_exists(__DIR__ . '/models/Database.php')) {
    require_once __DIR__ . '/models/Database.php';
} elseif (file_exists(__DIR__ . '/config/database.php')) {
    require_once __DIR__ . '/config/database.php';
}

checkLogin();

// หากเป็น Admin ให้ Redirect ไปหน้า Admin Dashboard
if (($_SESSION['user']['role'] ?? '') === 'admin') {
    header("Location: views/admin/dashboard.php");
    exit();
}

$username_display = $_SESSION['user']['username'] ?? 'user';

// ดึงข้อมูลห้องเรียนจากฐานข้อมูล
$rooms = [];
try {
    if (class_exists('Database')) {
        $database = new Database();
        $db = method_exists($database, 'getConnection') ? $database->getConnection() : $database->connect();
        
        if ($db) {
            $stmt = $db->prepare("SELECT * FROM rooms ORDER BY id DESC");
            $stmt->execute();
            $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
} catch (Exception $e) {
    $rooms = $_SESSION['rooms'] ?? [];
}

// ข้อมูลตัวอย่างสำรองกรณีฐานข้อมูลยังไม่มีข้อมูล
if (empty($rooms)) {
    $rooms = [
        [
            'id' => 1,
            'room_number' => 'CB101',
            'room_name' => 'ห้องเรียนรวม 1',
            'building' => 'อาคารเรียนรวม 1 (CB1)',
            'floor' => '1',
            'type' => 'ห้องบรรยาย',
            'subject' => '[INT101] Programming Fundamentals',
            'schedule' => 'ทุกวันจันทร์ 08:00 - 10:00 น.',
            'instructor' => 'ผศ.ดร. สมชาย ใจดี',
            'image' => 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=400&auto=format&fit=crop'
        ],
        [
            'id' => 2,
            'room_number' => 'CB102',
            'room_name' => 'ห้องปฏิบัติการคอมพิวเตอร์ 2',
            'building' => 'อาคารเรียนรวม 1 (CB1)',
            'floor' => '1',
            'type' => 'ห้องปฏิบัติการ',
            'subject' => '[GEN111] Man and Philosophy',
            'schedule' => 'ทุกวันอังคาร 10:00 - 12:00 น.',
            'instructor' => 'อ.วิภาดา รัตนกุล',
            'image' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?w=400&auto=format&fit=crop'
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classroom Finder - User Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; background-color: #f1f5f9; }
        .top-header { background-color: #0f172a; color: white; padding: 12px 24px; }
        .badge-user { background-color: #06b6d4; color: #000; font-size: 0.75rem; font-weight: 700; border-radius: 4px; padding: 2px 8px; }
        
        .nav-custom-container { background-color: #0f172a; padding: 0 24px 12px; }
        .nav-custom .nav-link { 
            color: white; 
            background-color: #0284c7; 
            border-radius: 8px; 
            font-weight: 500; 
            padding: 8px 18px; 
            margin-right: 8px;
            border: none;
            transition: all 0.2s;
        }
        .nav-custom .nav-link.active { 
            background-color: #2563eb !important; 
            color: white !important;
            font-weight: 700; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
        }
        
        .banner-box { background-color: #0284c7; color: white; border-radius: 12px; padding: 20px; }
        .room-card { border: none; border-radius: 12px; overflow: hidden; background: white; transition: transform 0.2s; }
        .room-card:hover { transform: translateY(-4px); }
        .room-card img { height: 180px; object-fit: cover; width: 100%; }
    </style>
</head>
<body>

    <!-- Header Navbar -->
    <div class="top-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-geo-alt-fill text-warning fs-5"></i>
            <span class="fw-bold fs-5">Classroom Finder</span>
            <span class="badge-user">STUDENT / USER</span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="small"><i class="bi bi-person-circle"></i> สวัสดี, <strong><?= htmlspecialchars($username_display) ?></strong></span>
            <a href="logout.php" onclick="return confirm('ต้องการออกจากระบบใช่หรือไม่?')" class="btn btn-outline-light btn-sm rounded-pill px-3">
                <i class="bi bi-box-arrow-right"></i> ออกจากระบบ
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="nav-custom-container">
        <ul class="nav nav-pills nav-custom" id="userTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="search-tab" data-bs-toggle="pill" data-bs-target="#search-pane" type="button" role="tab">
                    <i class="bi bi-search"></i> ค้นหาห้องเรียน
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="booking-tab" data-bs-toggle="pill" data-bs-target="#booking-pane" type="button" role="tab">
                    <i class="bi bi-calendar-plus"></i> จองห้องเรียน
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="schedule-tab" data-bs-toggle="pill" data-bs-target="#schedule-pane" type="button" role="tab">
                    <i class="bi bi-calendar3"></i> ตารางเรียนทุกวิชา
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="report-tab" data-bs-toggle="pill" data-bs-target="#report-pane" type="button" role="tab">
                    <i class="bi bi-exclamation-triangle"></i> แจ้งปัญหาการใช้งาน
                </button>
            </li>
        </ul>
    </div>

    <div class="container my-4">
        <div class="tab-content" id="userTabContent">
            
            <!-- TAB 1: ค้นหาและรายการห้องเรียน -->
            <div class="tab-pane fade show active" id="search-pane" role="tabpanel">
                <div class="banner-box text-center mb-4">
                    <h4 class="fw-bold mb-1"><i class="bi bi-building"></i> ค้นหาห้องเรียนและตรวจสอบสถานะ</h4>
                    <p class="small opacity-75 mb-0">ค้นหาห้องเรียนตามหมายเลข อาคาร หรือชั้น เพื่อดูรายละเอียด</p>
                </div>

                <div class="row g-4">
                    <?php if(!empty($rooms)): ?>
                        <?php foreach ($rooms as $rm): ?>
                            <div class="col-md-4">
                                <div class="card room-card shadow-sm h-100">
                                    <img src="<?= !empty($rm['image']) ? $rm['image'] : 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=400' ?>" alt="room">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h5 class="fw-bold text-primary mb-0">ห้อง <?= htmlspecialchars($rm['room_number'] ?? '') ?></h5>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($rm['type'] ?? 'ห้องเรียน') ?></span>
                                        </div>
                                        <p class="text-muted small mb-2"><i class="bi bi-geo-alt-fill text-danger"></i> <?= htmlspecialchars($rm['building'] ?? 'ไม่ระบุอาคาร') ?></p>
                                        <hr class="my-2">
                                        <p class="small mb-1"><strong>รายวิชา:</strong> <?= htmlspecialchars($rm['subject'] ?? 'ไม่มีการเรียนการสอน') ?></p>
                                        <p class="small text-success mb-3"><strong>เวลาเรียน:</strong> <?= htmlspecialchars($rm['schedule'] ?? 'ว่าง') ?></p>
                                        <a href="views/admin/room_detail.php?id=<?= $rm['id'] ?>" class="btn btn-outline-primary w-100 fw-bold rounded-3 btn-sm">
                                            <i class="bi bi-info-circle"></i> ดูรายละเอียดห้องเรียน
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5">
                            <p class="text-muted">ไม่พบข้อมูลห้องเรียนในระบบ</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TAB 2: จองห้องเรียน -->
            <div class="tab-pane fade" id="booking-pane" role="tabpanel">
                <div class="card p-4 shadow-sm mx-auto border-0" style="max-width: 550px;">
                    <h4 class="fw-bold text-primary mb-3 text-center"><i class="bi bi-calendar-plus"></i> จองห้องเรียน</h4>
                    <form onsubmit="alert('ส่งคำขอจองห้องเรียนเรียบร้อยแล้ว!'); return false;">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">เลือกห้องเรียน</label>
                            <select class="form-select" required>
                                <?php foreach ($rooms as $rm): ?>
                                    <option value="<?= $rm['id'] ?>">ห้อง <?= htmlspecialchars($rm['room_number'] ?? '') ?> (<?= htmlspecialchars($rm['building'] ?? '') ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">วัตถุประสงค์ในการจอง</label>
                            <input type="text" class="form-control" placeholder="เช่น ติวหนังสือกลุ่ม, ประชุมกิจกรรม" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">วันที่ใช้งาน</label>
                                <input type="date" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">ช่วงเวลา</label>
                                <input type="text" class="form-control" placeholder="09:00 - 12:00 น." required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2 rounded-3">ยืนยันส่งคำขอจอง</button>
                    </form>
                </div>
            </div>

            <!-- TAB 3: ตารางเรียนทุกวิชา -->
            <div class="tab-pane fade" id="schedule-pane" role="tabpanel">
                <div class="card p-4 shadow-sm bg-white border-0">
                    <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-calendar3"></i> ตารางเรียนทุกวิชาประจำภาคเรียน</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ห้องเรียน</th>
                                    <th>อาคาร / ชั้น</th>
                                    <th>รายวิชา</th>
                                    <th>อาจารย์ผู้สอน</th>
                                    <th>วัน-เวลาเรียน</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rooms as $rm): ?>
                                <tr>
                                    <td class="fw-bold text-primary">ห้อง <?= htmlspecialchars($rm['room_number'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($rm['building'] ?? '-') ?> (ชั้น <?= htmlspecialchars($rm['floor'] ?? '1') ?>)</td>
                                    <td><?= htmlspecialchars($rm['subject'] ?? '-') ?></td>
                                    <td><i class="bi bi-person"></i> <?= htmlspecialchars($rm['instructor'] ?? '-') ?></td>
                                    <td><span class="badge bg-info text-dark"><i class="bi bi-clock"></i> <?= htmlspecialchars($rm['schedule'] ?? '-') ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 4: แจ้งปัญหาการใช้งาน -->
            <div class="tab-pane fade" id="report-pane" role="tabpanel">
                <div class="card p-4 shadow-sm mx-auto border-0" style="max-width: 550px;">
                    <h4 class="fw-bold text-danger mb-3 text-center"><i class="bi bi-exclamation-triangle"></i> แจ้งปัญหาการใช้งานห้องเรียน</h4>
                    <form onsubmit="alert('ส่งรายงานแจ้งปัญหาเข้าสู่ระบบแล้ว'); return false;">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">เลือกห้องเรียนที่มีปัญหา</label>
                            <select class="form-select" required>
                                <?php foreach ($rooms as $rm): ?>
                                    <option value="<?= $rm['id'] ?>">ห้อง <?= htmlspecialchars($rm['room_number'] ?? '') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">รายละเอียดปัญหา / อุปกรณ์ชำรุด</label>
                            <textarea class="form-control" rows="4" placeholder="ระบุรายละเอียด เช่น ไมโครโฟนไม่ดัง, โปรเจกเตอร์เปิดไม่ติด..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger w-100 fw-bold py-2 rounded-3">ส่งรายงานแจ้งปัญหา</button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <!-- สคริปต์สลับแท็บของ Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>