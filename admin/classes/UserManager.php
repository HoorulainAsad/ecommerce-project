<?php
// admin/classes/UserManager.php

require_once __DIR__ . '/../includes/database.php';

class UserManager {
    private $conn;

    public function __construct() {
        $this->conn = getDbConnection();
    }

    
    public function getAllUsers() {
        $sql = "SELECT id, username, email, created_at FROM users ORDER BY created_at DESC";
        $result = $this->conn->query($sql);
        $users = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }
        return $users;
    }

    
    public function getTotalUsersCount() {
        $sql = "SELECT COUNT(id) AS total_users FROM users"; 
        $result = $this->conn->query($sql);
        if ($result && $row = $result->fetch_assoc()) {
            return (int)$row['total_users'];
        }
        return 0;
    }
}
?>
