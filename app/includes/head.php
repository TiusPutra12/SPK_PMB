<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
require_once '../includes/koneksi.php'; 
$page = isset($_GET['page']) ? $_GET['page'] : 'beranda';

$pages = [
    'beranda'   => "DASHBOARD",
    'user'      => "DATA USER",
    'kriteria'  => "DATA KRITERIA",
    'mahasiswa' => "DATA MAHASISWA",
    'alternatif' => "DATA ALTERNATIF",
    'analisa'   => "ANALISA WP"
];
$page_title = $pages[$page] ?? "SPK PENERIMAAN SISWA BARU";

$style_css = '<link rel="stylesheet" href="../../assets/css/style.css?v=' . time() . '">';
$login_css = '<link rel="stylesheet" href="../../assets/css/login.css?v=' . time() . '">';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard SPK PMB'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script> 
        tailwind.config = { 
            theme: { 
                extend: { 
                    colors: { 
                        dark: '#ffffffff', 
                        darker: '#030712', 
                        card: '#1F2937' 
                    } 
                } 
            } 
        }
    </script>
</head>

<body class="bg-darker text-gray-200 font-sans antialiased flex h-screen overflow-hidden">

<?php include '../includes/sidebar.php'; ?>
<div class="flex-1 flex flex-col overflow-y-auto bg-dark min-w-0">
    <header class="h-20 flex items-center justify-between px-8 bg-card border-b border-gray-700 sticky top-0 z-10 shadow-sm">
        <div class="flex items-center gap-3">
            <h2 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-pink-400 to-pink-600 tracking-wide">
                <?php echo "$page_title";?>
            </h2>
        </div>
        <div class="flex items-center gap-5">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-full bg-gradient-to-r from-pink-500 to-purple-600 flex items-center justify-center text-white text-base font-bold shadow-md">
                    <?= strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)); ?>
                </span>
                <div class="flex flex-col">
                    <span class="text-sm font-semibold text-gray-200 leading-tight">
                        <?= htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>
                    </span>
                    <span class="text-xs text-gray-400 font-medium">Administrator</span>
                </div>
            </div>
            <a href="logout.php" onclick="return confirm('Apakah Anda yakin ingin logout?');" class="inline-flex items-center gap-2 text-sm px-4 py-2 bg-gray-700/80 hover:bg-red-600 text-gray-200 hover:text-white rounded-lg transition-all font-medium border border-gray-600 hover:border-red-500 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Logout
            </a>
        </div>
    </header>
