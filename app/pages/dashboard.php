<?php
// app/pages/dashboard.php
session_start();

// Validasi menggunakan parameter 'login' sesuai controller kita
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Panggil database dan atur routing
require_once '../includes/koneksi.php'; 
$page = isset($_GET['page']) ? $_GET['page'] : 'beranda';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard SPK PMB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { dark: '#111827', darker: '#030712', card: '#1F2937' } } } }
    </script>
</head>
<body class="bg-darker text-gray-200 font-sans antialiased flex h-screen overflow-hidden">
    
    <!-- Memanggil sidebar dari folder includes -->
    <?php include '../includes/sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-y-auto bg-dark">
        <header class="h-16 flex items-center justify-between px-8 bg-card border-b border-gray-700">
            <h2 class="text-xl font-semibold bg-clip-text text-transparent bg-gradient-to-r from-pink-400 to-pink-600">
                DSS Penerimaan Mahasiswa Baru
            </h2>
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-gray-300">Halo, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
                <a href="logout.php" class="text-sm px-3 py-1.5 bg-gray-700 hover:bg-red-500 hover:text-white rounded transition-colors">Logout</a>
            </div>
        </header>

        <main class="p-8">
            <?php
            // Memuat konten secara dinamis
            if ($page == 'beranda') {
                echo '<h1 class="text-3xl font-bold text-white mb-2">Dashboard Utama</h1>';
                echo '<p class="text-gray-400">Selamat datang di Sistem Pendukung Keputusan Penerimaan Mahasiswa Baru.</p>';
            } else {
                $file = $page . '.php';
                if (file_exists($file)) {
                    include $file;
                } else {
                    echo "<h1 class='text-2xl font-bold text-red-500'>Halaman tidak ditemukan!</h1>";
                }
            }
            ?>
        </main>
    </div>
</body>
</html>