<?php
// api/get_floors.php
require_once '../config/database.php';

header('Content-Type: application/json');

$building_id = $_GET['building_id'] ?? 0;

if ($building_id) {
    $db = (new Database())->getConnection();
    $stmt = $db->prepare("SELECT floor_id, floor_number, floor_name FROM floors WHERE building_id = ? ORDER BY floor_number ASC");
    $stmt->execute([$building_id]);
    $floors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($floors);
} else {
    echo json_encode([]);
}
?>