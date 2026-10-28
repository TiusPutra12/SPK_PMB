<?php
// app/controllers/process.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/UserModel.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

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

        // Menggunakan UserModel untuk verifikasi
        $user = $this->userModel->verifyLogin($username, $password);

        if ($user) {
            $_SESSION['login']    = true; // Gunakan 'login' agar cocok dengan index.php kita
            $_SESSION['username'] = $user['username'];
            
            return [
                'success' => true,
                'message' => 'Login berhasil! Mengalihkan ke sistem...',
                'user'    => $user
            ];
        }

        return [
            'success' => false,
            'message' => 'Username atau password yang Anda masukkan salah!',
            'user'    => null
        ];
    }
}
?>