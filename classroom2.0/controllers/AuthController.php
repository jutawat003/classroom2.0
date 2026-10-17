<?php
// controllers/AuthController.php
class AuthController {
    
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        
        header("Location: ../views/auth/login.php");
        exit();
    }
}

// ตรวจจับ action ถ้ารายการร้องขอส่งมาที่ controller โดยตรง
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $auth = new AuthController();
    $auth->logout();
}
?>