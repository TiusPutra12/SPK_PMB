<?php
// app/controllers/process.php
// Berisi seluruh controller & logika proses aplikasi

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Muat Model yang berhubungan dengan SQL
require_once __DIR__ . '/../models/UserModel.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    /**
     * Memproses autentikasi login pengguna
     *
     * @param string $username
     * @param string $password
     * @return array ['success' => bool, 'message' => string, 'user' => array|null]
     */
    public function handleLogin($username, $password) {
        $username = trim($username);
        $password = trim($password);

        if (empty($username) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Silakan masukkan username dan password.',
                'user'    => null
            ];
        }

        // Panggil model untuk verifikasi ke database SQL pmbdb
        $user = $this->userModel->verifyLogin($username, $password);

        if ($user) {
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id']       = $user['id'];
            $_SESSION['username']      = $user['username'];
            $_SESSION['nama']          = 'Administrator PMB';

            return [
                'success' => true,
                'message' => 'Login berhasil! Mengalihkan ke sistem...',
                'user'    => $user
            ];
        }

        // Fallback akun default dari Text Akses bila database belum terisi
        if ($username === 'admin' && $password === 'salamloroati26') {
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id']       = 1;
            $_SESSION['username']      = 'admin';
            $_SESSION['nama']          = 'Administrator PMB';

            return [
                'success' => true,
                'message' => 'Login berhasil! Mengalihkan ke sistem...',
                'user'    => ['id' => 1, 'username' => 'admin']
            ];
        }

        return [
            'success' => false,
            'message' => 'Username atau password yang Anda masukkan salah!',
            'user'    => null
        ];
    }

    /**
     * Memproses logout pengguna
     */
    public function handleLogout() {
        session_unset();
        session_destroy();
        header("Location: ../login.php");
        exit;
    }
}

// Router/Handler Aksi Proses Otomatis jika dipanggil langsung via action form
if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
    $authController = new AuthController();

    if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $result = $authController->handleLogin($username, $password);

        if ($result['success']) {
            header("Location: ../website/index.php");
            exit;
        } else {
            $_SESSION['error_message'] = $result['message'];
            header("Location: ../login.php");
            exit;
        }
    } elseif ($action === 'logout') {
        $authController->handleLogout();
    }
}
?>
