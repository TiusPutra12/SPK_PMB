<?php
// app/models/UserModel.php
// Berisi seluruh query SQL terkait entitas User

require_once __DIR__ . '/../includes/koneksi.php';

class UserModel {
    private $db;

    public function __construct() {
        global $koneksi;
        $this->db = $koneksi;
    }

    /**
     * Mengambil user berdasarkan username
     *
     * @param string $username
     * @return array|null
     */
    public function getUserByUsername($username) {
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

    /**
     * Memverifikasi kredensial login user dengan hash sha512 (atau fallback password teks biasa/md5)
     *
     * @param string $username
     * @param string $password
     * @return array|false
     */
    public function verifyLogin($username, $password) {
        $user = $this->getUserByUsername($username);
        if (!$user) {
            return false;
        }

        $hashSha512 = hash('sha512', $password);

        // Cek kecocokan password dengan hash sha512
        if ($user['password'] === $hashSha512) {
            return $user;
        }

        // Fallback kompatibilitas: password plain text atau password_verify bcrypt
        if ($user['password'] === $password || password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    /**
     * Mengambil seluruh data user
     *
     * @return array
     */
    public function getAllUsers() {
        $query = "SELECT id, username FROM users ORDER BY id ASC";
        $result = mysqli_query($this->db, $query);
        $users = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $users[] = $row;
            }
        }
        return $users;
    }
}
?>
