<?php
class User {
    private $conn;
    private $table = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function findByUsername($username) {
        $query = "SELECT * FROM " . $this->table . " WHERE username = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>