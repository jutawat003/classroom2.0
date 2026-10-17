<?php
class Floor {
    private $conn;
    private $table = "floors";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getByBuilding($building_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE building_id = ? ORDER BY floor_number ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$building_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($building_id, $floor_number, $floor_name) {
        $query = "INSERT INTO " . $this->table . " (building_id, floor_number, floor_name) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$building_id, $floor_number, $floor_name]);
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE floor_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }
}
?>