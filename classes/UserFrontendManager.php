<?php
// classes/UserFrontendManager.php

require_once __DIR__ . '/../admin/includes/database.php';

class UserFrontendManager {
    private $conn;

    public function __construct() {
        $this->conn = getDbConnection();
    }

    /**
     * Registers a new user.
     * @param string $username The user's chosen username.
     * @param string $email The user's email address.
     * @param string $password The user's plain-text password.
     * @return bool True on success, false if user/email already exists or on error.
     */
    public function registerUser($username, $email, $password) {
        $checkSql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmtCheck = $this->conn->prepare($checkSql);
        if (!$stmtCheck) {
            error_log("UserFrontendManager: Register prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }
        $stmtCheck->bind_param("ss", $username, $email);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
        if ($resultCheck->num_rows > 0) {
            // User with this username or email already exists
            $stmtCheck->close();
            return false;
        }
        $stmtCheck->close();

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("UserFrontendManager: Register insert prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("sss", $username, $email, $hashedPassword);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Authenticates a user.
     * @param string $email The user's email address.
     * @param string $password The user's plain-text password.
     * @return array|null User data on successful authentication, null otherwise.
     */
    public function authenticateUser($email, $password) {
        $sql = "SELECT id, username, email, password FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("UserFrontendManager: Authenticate prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                unset($user['password']); // Don't return the hashed password
                $stmt->close();
                return $user;
            }
        }
        $stmt->close();
        return null;
    }

}
?>
