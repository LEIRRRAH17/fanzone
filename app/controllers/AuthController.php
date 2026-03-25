<?php
require_once BASE_PATH . '/app/models/UserModel.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    // Show login page / handle login POST
    public function login() {
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            // Basic validation
            if (empty($username) || empty($password)) {
                $error = 'Please fill in all fields.';
            } else {
                $result = $this->userModel->login($username, $password);
                if ($result['success']) {
                    // Set session
                    $_SESSION['user_id']   = $result['user']['id'];
                    $_SESSION['username']  = $result['user']['username'];
                    $_SESSION['full_name'] = $result['user']['full_name'];
                    $_SESSION['profile_image'] = $result['user']['profile_image'];

                    header('Location: index.php?action=newsfeed');
                    exit;
                } else {
                    $error = $result['message'];
                }
            }
        }

        // Load login view
        require_once BASE_PATH . '/app/views/auth/login.php';
    }

    // Show register page / handle register POST
    public function register() {
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $full_name = trim($_POST['full_name'] ?? '');
            $username  = trim($_POST['username'] ?? '');
            $password  = $_POST['password'] ?? '';
            $confirm   = $_POST['confirm_password'] ?? '';
            $fandoms   = $_POST['fandoms'] ?? [];

            // Validation
            if (empty($full_name) || empty($username) || empty($password)) {
                $error = 'Please fill in all required fields.';
            } elseif (strlen($username) < 3) {
                $error = 'Username must be at least 3 characters.';
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters.';
            } elseif ($password !== $confirm) {
                $error = 'Passwords do not match.';
            } elseif (empty($fandoms)) {
                $error = 'Please select at least one fandom.';
            } else {
                // Sanitize
                $username  = htmlspecialchars($username);
                $full_name = htmlspecialchars($full_name);

                $result = $this->userModel->register($username, $password, $full_name, $fandoms);
                if ($result['success']) {
                    $success = 'Account created! You can now log in.';
                } else {
                    $error = $result['message'];
                }
            }
        }

        require_once BASE_PATH . '/app/views/auth/register.php';
    }

    // Logout
    public function logout() {
        session_destroy();
        header('Location: index.php?action=login');
        exit;
    }

    // Guard — redirect to login if not logged in
    public static function requireLogin() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
    }
}

