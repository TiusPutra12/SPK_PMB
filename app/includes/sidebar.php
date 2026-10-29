<?php
$page = isset($_GET['page']) ? $_GET['page'] : 'beranda';

// Cek status aktif halaman untuk membuka dropdown secara otomatis
$isMasterData = in_array($page, ['user', 'kriteria', 'mahasiswa', 'alternatif']);
$isAnalisis = in_array($page, ['analisis1', 'analisis2', 'analisis3']);
?>
<aside class="w-64 bg-card h-full flex flex-col border-r border-gray-700 transition-all">
    <div class="h-16 flex items-center justify-center border-b border-gray-700">
        <h1 class="text-2xl font-bold tracking-wider text-white">SPK<span class="text-pink-500">.PMB</span></h1>
    </div>
    
    <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
        <a href="?page=beranda" class="block px-4 py-3 rounded-lg transition-all <?= ($page == 'beranda' || $page == '') ? 'bg-gradient-to-r from-pink-500 to-pink-600 text-white shadow-lg shadow-pink-500/30' : 'text-gray-400 hover:bg-gray-800 hover:text-pink-400' ?>">Beranda</a>
        
        <!-- Menu Dropdown Master Data -->
        <div class="relative pt-2">
            <button onclick="toggleDropdown('menuMaster', 'arrowMaster')" class="w-full flex items-center justify-between px-4 py-3 rounded-lg transition-all focus:outline-none <?= $isMasterData ? 'text-pink-400 bg-gray-800' : 'text-gray-400 hover:bg-gray-800 hover:text-pink-400' ?>">
                <span class="font-medium">Master Data</span>
                <svg id="arrowMaster" class="w-4 h-4 transition-transform duration-300 <?= $isMasterData ? 'rotate-180' : '' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            
            <div id="menuMaster" class="overflow-hidden transition-all duration-300 <?= $isMasterData ? 'max-h-64 opacity-100 mt-1' : 'max-h-0 opacity-0' ?>">
                <div class="flex flex-col space-y-1 pl-4 pr-2 py-2 border-l-2 border-gray-700 ml-4 mt-1">
                    <a href="?page=user" class="block px-4 py-2 text-sm rounded-lg transition-all <?= ($page == 'user') ? 'text-pink-400 font-medium' : 'text-gray-400 hover:text-pink-400 hover:bg-gray-800/50' ?>">Data User</a>
                    <a href="?page=kriteria" class="block px-4 py-2 text-sm rounded-lg transition-all <?= ($page == 'kriteria') ? 'text-pink-400 font-medium' : 'text-gray-400 hover:text-pink-400 hover:bg-gray-800/50' ?>">Data Kriteria</a>
                    <a href="?page=mahasiswa" class="block px-4 py-2 text-sm rounded-lg transition-all <?= ($page == 'mahasiswa') ? 'text-pink-400 font-medium' : 'text-gray-400 hover:text-pink-400 hover:bg-gray-800/50' ?>">Data Mahasiswa</a>
                    <a href="?page=alternatif" class="block px-4 py-2 text-sm rounded-lg transition-all <?= ($page == 'alternatif') ? 'text-pink-400 font-medium' : 'text-gray-400 hover:text-pink-400 hover:bg-gray-800/50' ?>">Set Alternatif</a>
                </div>
            </div>
        </div>
        
        <!-- Menu Dropdown Analisis WP -->
        <div class="relative pt-2">
            <button onclick="toggleDropdown('menuAnalisis', 'arrowAnalisis')" class="w-full flex items-center justify-between px-4 py-3 rounded-lg transition-all focus:outline-none <?= $isAnalisis ? 'text-pink-400 bg-gray-800' : 'text-gray-400 hover:bg-gray-800 hover:text-pink-400' ?>">
                <span class="font-medium">Analisis WP</span>
                <svg id="arrowAnalisis" class="w-4 h-4 transition-transform duration-300 <?= $isAnalisis ? 'rotate-180' : '' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            
            <div id="menuAnalisis" class="overflow-hidden transition-all duration-300 <?= $isAnalisis ? 'max-h-64 opacity-100 mt-1' : 'max-h-0 opacity-0' ?>">
                <div class="flex flex-col space-y-1 pl-4 pr-2 py-2 border-l-2 border-gray-700 ml-4 mt-1">
                    <a href="?page=analisis1" class="block px-4 py-2 text-sm rounded-lg transition-all <?= ($page == 'analisis1') ? 'text-pink-400 font-medium' : 'text-gray-400 hover:text-pink-400 hover:bg-gray-800/50' ?>">Analisis 1</a>
                    <a href="?page=analisis2" class="block px-4 py-2 text-sm rounded-lg transition-all <?= ($page == 'analisis2') ? 'text-pink-400 font-medium' : 'text-gray-400 hover:text-pink-400 hover:bg-gray-800/50' ?>">Analisis 2</a>
                    <a href="?page=analisis3" class="block px-4 py-2 text-sm rounded-lg transition-all <?= ($page == 'analisis3') ? 'text-pink-400 font-medium' : 'text-gray-400 hover:text-pink-400 hover:bg-gray-800/50' ?>">Analisis 3</a>
                </div>
            </div>
        </div>
    </nav>
</aside>

<script>
    function toggleDropdown(menuId, arrowId) {
        const menu = document.getElementById(menuId);
        const arrow = document.getElementById(arrowId);
        
        if (menu.classList.contains('max-h-0')) {
            // Membuka dropdown terpilih
            menu.classList.remove('max-h-0', 'opacity-0');
            menu.classList.add('max-h-64', 'opacity-100', 'mt-1');
            arrow.classList.add('rotate-180');
        } else {
            // Menutup dropdown terpilih
            menu.classList.add('max-h-0', 'opacity-0');
            menu.classList.remove('max-h-64', 'opacity-100', 'mt-1');
            arrow.classList.remove('rotate-180');
        }
    }
</script>