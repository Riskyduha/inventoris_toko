<?php

class AuthController {
    private $user;
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_SECONDS = 300;

    public function __construct() {
        require_once BASE_PATH . '/app/models/User.php';
        $this->user = new User();
    }

    // Show login page
    public function login() {
        if (isset($_SESSION['user_id'])) {
            header('Location: /');
            exit;
        }

        $this->ensureLoginState();
        $error = '';
        $csrfToken = $this->getCsrfToken();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->isLoginLocked()) {
                $remaining = max(1, $_SESSION['auth_lock_until'] - time());
                $waitMinutes = (int) ceil($remaining / 60);
                $error = "Terlalu banyak percobaan login gagal. Coba lagi dalam {$waitMinutes} menit.";
                require_once BASE_PATH . '/app/views/auth/login.php';
                return;
            }

            $postedToken = $_POST['csrf_token'] ?? '';
            if (!$this->isValidCsrfToken($postedToken)) {
                $error = 'Sesi login tidak valid. Silakan refresh halaman dan coba lagi.';
                $csrfToken = $this->refreshCsrfToken();
                require_once BASE_PATH . '/app/views/auth/login.php';
                return;
            }

            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = $this->user->login($username, $password);
            if ($user) {
                session_regenerate_id(true);
                $this->clearLoginAttempts();
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = $user['role'];
                header('Location: /');
                exit;
            } else {
                $this->recordFailedLoginAttempt();
                $error = 'Username atau password salah!';
            }

            $csrfToken = $this->refreshCsrfToken();
        }

        require_once BASE_PATH . '/app/views/auth/login.php';
    }

    // Logout
    public function logout() {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
        }
        session_destroy();
        header('Location: /login');
        exit;
    }

    // Show user management (admin only)
    public function users() {
        $this->checkAuth(['admin']);
        
        $users = $this->user->getAllUsers();
        require_once BASE_PATH . '/app/views/auth/users.php';
    }

    // Create user form
    public function createUser() {
        $this->checkAuth(['admin']);
        
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $nama = $_POST['nama'] ?? '';
            $role = $_POST['role'] ?? 'kasir';
            $allowedRoles = ['admin', 'manager', 'kasir', 'inspeksi'];

            if (!$username || !$password || !$nama) {
                $error = 'Semua field harus diisi!';
            } elseif (!in_array($role, $allowedRoles, true)) {
                $error = 'Role tidak valid!';
            } elseif ($this->user->usernameExists($username)) {
                $error = 'Username sudah digunakan!';
            } else {
                if ($this->user->createUser($username, $password, $nama, $role)) {
                    header('Location: /users');
                    exit;
                } else {
                    $error = 'Gagal membuat user!';
                }
            }
        }

        require_once BASE_PATH . '/app/views/auth/create-user.php';
    }

    // Edit user form
    public function editUser($id) {
        $this->checkAuth(['admin']);
        
        $user = $this->user->getUserById($id);
        if (!$user) {
            header('Location: /users');
            exit;
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'] ?? '';
            $nama = $_POST['nama'] ?? '';
            $role = $_POST['role'] ?? 'kasir';
            $allowedRoles = ['admin', 'manager', 'kasir', 'inspeksi'];

            if (!$username || !$nama) {
                $error = 'Semua field harus diisi!';
            } elseif (!in_array($role, $allowedRoles, true)) {
                $error = 'Role tidak valid!';
            } elseif ($this->user->usernameExists($username, $id)) {
                $error = 'Username sudah digunakan!';
            } else {
                if ($this->user->updateUser($id, $username, $nama, $role)) {
                    header('Location: /users');
                    exit;
                } else {
                    $error = 'Gagal mengupdate user!';
                }
            }
        }

        require_once BASE_PATH . '/app/views/auth/edit-user.php';
    }

    // Delete user
    public function deleteUser($id) {
        $this->checkAuth(['admin']);
        
        $this->user->deleteUser($id);
        header('Location: /users');
        exit;
    }

    // Check authentication and authorization
    private function checkAuth($allowedRoles = []) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        if (!empty($allowedRoles) && !in_array($_SESSION['role'], $allowedRoles)) {
            header('Location: /');
            exit;
        }
    }

    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
    }

    public static function requireRole($roles) {
        if (!self::isLoggedIn()) {
            header('Location: /login');
            exit;
        }

        if (!in_array($_SESSION['role'], (array)$roles)) {
            header('Location: /');
            exit;
        }
    }

    private function ensureLoginState() {
        if (!isset($_SESSION['auth_failed_attempts'])) {
            $_SESSION['auth_failed_attempts'] = 0;
        }
        if (!isset($_SESSION['auth_lock_until'])) {
            $_SESSION['auth_lock_until'] = 0;
        }
        if (!isset($_SESSION['login_csrf_token']) || !is_string($_SESSION['login_csrf_token'])) {
            $_SESSION['login_csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    private function getCsrfToken() {
        return $_SESSION['login_csrf_token'];
    }

    private function refreshCsrfToken() {
        $_SESSION['login_csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['login_csrf_token'];
    }

    private function isValidCsrfToken($postedToken) {
        if (!is_string($postedToken) || $postedToken === '') {
            return false;
        }

        if (!isset($_SESSION['login_csrf_token']) || !is_string($_SESSION['login_csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['login_csrf_token'], $postedToken);
    }

    private function isLoginLocked() {
        $lockUntil = (int) ($_SESSION['auth_lock_until'] ?? 0);
        if ($lockUntil <= time()) {
            $_SESSION['auth_lock_until'] = 0;
            return false;
        }
        return true;
    }

    private function recordFailedLoginAttempt() {
        $attempts = (int) ($_SESSION['auth_failed_attempts'] ?? 0) + 1;
        $_SESSION['auth_failed_attempts'] = $attempts;

        if ($attempts >= self::MAX_LOGIN_ATTEMPTS) {
            $_SESSION['auth_lock_until'] = time() + self::LOCKOUT_SECONDS;
            $_SESSION['auth_failed_attempts'] = 0;
        }
    }

    private function clearLoginAttempts() {
        $_SESSION['auth_failed_attempts'] = 0;
        $_SESSION['auth_lock_until'] = 0;
        $this->refreshCsrfToken();
    }
}
