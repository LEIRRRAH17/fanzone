<?php
require_once BASE_PATH . '/config/database.php';

class UserModel {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    // Register a new user
    public function register($username, $password, $full_name, $fandoms) {
        // Check if username already exists
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Username already taken.'];
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $fandom_str = implode(',', $fandoms);

        $stmt = $this->conn->prepare("
            INSERT INTO users (username, password, full_name, fandoms)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$username, $hashed, $full_name, $fandom_str]);

        return ['success' => true, 'message' => 'Account created successfully!'];
    }

    // Login user
    public function login($username, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return ['success' => true, 'user' => $user];
        }

        return ['success' => false, 'message' => 'Invalid username or password.'];
    }

    // Get user by ID
    public function getUserById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Get user by username
    public function getUserByUsername($username) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    // Update profile
    public function updateProfile($id, $full_name, $bio, $fandoms, $profile_image = null) {
        $fandom_str = implode(',', $fandoms);
        if ($profile_image) {
            $stmt = $this->conn->prepare("
                UPDATE users SET full_name=?, bio=?, fandoms=?, profile_image=?
                WHERE id=?
            ");
            $stmt->execute([$full_name, $bio, $fandom_str, $profile_image, $id]);
        } else {
            $stmt = $this->conn->prepare("
                UPDATE users SET full_name=?, bio=?, fandoms=?
                WHERE id=?
            ");
            $stmt->execute([$full_name, $bio, $fandom_str, $id]);
        }
        return true;
    }

    // Search users
    public function searchUsers($keyword) {
        $like = "%$keyword%";
        $stmt = $this->conn->prepare("
            SELECT id, username, full_name, profile_image, fandoms
            FROM users
            WHERE username LIKE ? OR full_name LIKE ?
        ");
        $stmt->execute([$like, $like]);
        return $stmt->fetchAll();
    }
}