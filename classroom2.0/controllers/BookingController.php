<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

/**
 * Class BookingController
 * จัดการตารางและการอนุมัติการจองห้องเรียน
 */
class BookingController {
    /** @var PDO|null */
    private $db;

    public function __construct() {
        if (class_exists('Database')) {
            /** @var object $database */
            $database = new Database();
            if (method_exists($database, 'getConnection')) {
                $this->db = $database->getConnection();
            } elseif (method_exists($database, 'connect')) {
                $this->db = $database->connect();
            } else {
                $this->db = null;
            }
        }
    }

    /**
     * ดึงรายการคำขอจองทั้งหมดที่รออนุมัติ (status = 'pending')
     * @return array
     */
    public function getPendingBookings() {
        if (!$this->db) return [];
        try {
            $stmt = $this->db->prepare("SELECT * FROM bookings WHERE status = 'pending' ORDER BY id DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * อัปเดตสถานะการจอง (approved / rejected)
     * @param int $booking_id
     * @param string $status
     * @return bool
     */
    public function updateStatus($booking_id, $status) {
        if (!$this->db) return false;
        if (!in_array($status, ['approved', 'rejected'])) return false;

        try {
            $stmt = $this->db->prepare("UPDATE bookings SET status = :status WHERE id = :id");
            return $stmt->execute([
                ':status' => $status,
                ':id' => $booking_id
            ]);
        } catch (Exception $e) {
            return false;
        }
    }
}

// รับคำขอ POST เมื่อมีการกดปุ่มอนุมัติ หรือ ปฏิเสธ จากหน้าจอ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $booking_id = $_POST['booking_id'] ?? 0;
    $status = $_POST['status'] ?? '';

    $controller = new BookingController();
    $controller->updateStatus($booking_id, $status);

    // ทำงานเสร็จแล้ว เด้งกลับไปยังหน้าเดิมทันที
    $referrer = $_SERVER['HTTP_REFERER'] ?? '../views/admin/dashboard.php';
    header("Location: " . $referrer);
    exit();
}
?>