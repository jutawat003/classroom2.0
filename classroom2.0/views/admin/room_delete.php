<?php
ob_start();
session_start();

require_once __DIR__ . '/../../middleware/authCheck.php';
checkAdmin();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Room.php';

$id = $_GET['id'] ?? null;

if (!empty($id)) {
    $db = (new Database())->getConnection();
    $roomModel = new Room($db);
    $roomModel->delete($id);
}

ob_clean();
header('Location: dashboard.php');
exit();