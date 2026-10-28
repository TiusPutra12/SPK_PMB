<?php
// Deklarasi ini ditambahkan agar VS Code tidak mendeteksi 'Undefined variable'
$page = isset($_GET['page']) ? $_GET['page'] : 'beranda';
?>
<aside class="w-64 bg-card h-full flex flex-col border-r border-gray-700 transition-all">
    <div class="h-16 flex items-center justify-center border-b border-gray-700">
        <h1 class="text-2xl font-bold tracking-wider text-white">SPK<span class="text-pink-500">.PMB</span></h1>
    </div>
    
    <nav class="flex-1 px-4 py-6 space-y-2">
        <a href="?page=beranda" class="block px-4 py-3 rounded-lg transition-all <?= ($page == 'beranda' || $page == '') ? 'bg-gradient-to-r from-pink-500 to-pink-600 text-white shadow-lg shadow-pink-500/30' : 'text-gray-400 hover:bg-gray-800 hover:text-pink-400' ?>">Beranda</a>
        
        <div class="pt-4 pb-2">
            <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Master Data</p>
        </div>
        <a href="?page=user" class="block px-4 py-3 rounded-lg transition-all <?= ($page == 'user') ? 'text-pink-400 bg-gray-800' : 'text-gray-400 hover:bg-gray-800 hover:text-pink-400' ?>">Data User</a>
        <a href="?page=kriteria" class="block px-4 py-3 rounded-lg transition-all <?= ($page == 'kriteria') ? 'text-pink-400 bg-gray-800' : 'text-gray-400 hover:bg-gray-800 hover:text-pink-400' ?>">Data Kriteria</a>
        <a href="?page=mahasiswa" class="block px-4 py-3 rounded-lg transition-all <?= ($page == 'mahasiswa') ? 'text-pink-400 bg-gray-800' : 'text-gray-400 hover:bg-gray-800 hover:text-pink-400' ?>">Data Mahasiswa</a>
        <a href="?page=alternatif" class="block px-4 py-3 rounded-lg transition-all <?= ($page == 'alternatif') ? 'text-pink-400 bg-gray-800' : 'text-gray-400 hover:bg-gray-800 hover:text-pink-400' ?>">Set Alternatif</a>
        
        <div class="pt-4 pb-2">
            <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Perhitungan</p>
        </div>
        <a href="?page=analisa" class="block px-4 py-3 rounded-lg transition-all <?= ($page == 'analisa') ? 'bg-gradient-to-r from-pink-500 to-pink-600 text-white shadow-lg shadow-pink-500/30' : 'text-gray-400 hover:bg-gray-800 hover:text-pink-400' ?>">Analisa WP</a>
    </nav>
</aside>