<?php
// app/models/UserModel.php
require_once __DIR__ . '/../includes/koneksi.php';

class UserModel {
    private $db;

    public function __construct() {
        global $koneksi;
        $this->db = $koneksi;
    }

    public function getUserByUsername($username) {
        // Ambil data user dari tabel 'users'
        $stmt = mysqli_prepare($this->db, "SELECT * FROM users WHERE username = ? LIMIT 1");
        if (!$stmt) {
            return null;
        }

        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        return $user;
    }

    public function verifyLogin($username, $password) {
        $user = $this->getUserByUsername($username);
        if (!$user) {
            return false;
        }

        // Kita gunakan hash SHA-512 sesuai kesepakatan database kita
        $hashSha512 = hash('sha512', $password);

        if ($user['password'] === $hashSha512) {
            return $user;
        }

        return false;
    }
}
?>