<?php
// admin/classes/UserManager.php

require_once __DIR__ . '/../includes/database.php';

class UserManager {
    private $conn;

    public function __construct() {
        $this->conn = getDbConnection();
    }

    /**
     * Retrieves the total count of registered users.
     * @return int The total number of users.
     */
    public function getTotalUsersCount() {
        $sql = "SELECT COUNT(id) AS total_users FROM users"; // Assuming 'users' is your frontend user table
        $result = $this->conn->query($sql);
        if ($result && $row = $result->fetch_assoc()) {
            return (int)$row['total_users'];
        }
        return 0;
    }

    /**
     * Retrieves all users from the database.
     * @return array An array of user associative arrays.
     */
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

    // No __destruct() here, as database connection is handled globally.
}
?>
