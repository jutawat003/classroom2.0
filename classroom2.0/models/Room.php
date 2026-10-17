<?php
class Room {
    private $conn;
    private $table_name = "rooms";

    public function __construct($db) {
        $this->conn = $db;
    }

    // ดึงข้อมูลห้องเรียน / ค้นหา (รับค่าเป็น Optional parameter)
    public function search($keyword = null, $building_id = null, $type_id = null) {
        $query = "SELECT r.*, 
                         b.building_name, 
                         t.type_name 
                  FROM " . $this->table_name . " r
                  LEFT JOIN buildings b ON r.building_id = b.building_id
                  LEFT JOIN room_types t ON r.room_type_id = t.type_name
                  WHERE 1=1";

        $params = [];

        if (!empty($keyword)) {
            $query .= " AND (r.room_number LIKE :keyword OR r.room_name LIKE :keyword)";
            $params[':keyword'] = "%{$keyword}%";
        }
        if (!empty($building_id)) {
            $query .= " AND r.building_id = :building_id";
            $params[':building_id'] = $building_id;
        }
        if (!empty($type_id)) {
            $query .= " AND r.room_type_id = :type_id";
            $params[':type_id'] = $type_id;
        }

        $query .= " ORDER BY r.room_number ASC";

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ดึงข้อมูลห้องตาม ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE room_id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // ลบห้องเรียน
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE room_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }
}