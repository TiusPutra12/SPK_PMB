<?php
// app/includes/sidebar.php
$page = isset($_GET['page']) ? $_GET['page'] : 'beranda';
$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : 'camaba';

$isMasterData = in_array($page, ['user', 'kriteria', 'mahasiswa', 'alternatif']);
$isMahasiswaData = ($page == 'mahasiswa');
$isAnalisis = in_array($page, ['analisis']);
?>

<style>
    .sidebar-menu-transition {
        transition: max-height 0.3s ease-in-out, opacity 0.3s ease-in-out, margin-top 0.3s ease-in-out;
        overflow: hidden;
    }
    .sidebar-dropdown-closed {
        max-height: 0px;
        opacity: 0;
        margin-top: 0;
    }
    .sidebar-dropdown-open {
        max-height: 500px; 
        opacity: 1;
        margin-top: 0.25rem;
    }
    .hide-on-collapse {
        transition: opacity 0.2s;
    }
    .collapsed-sidebar {
        width: 5rem !important; 
    }
    .collapsed-sidebar .hide-on-collapse {
        display: none;
        opacity: 0;
    }
    .collapsed-sidebar .nav-item {
        justify-content: center;
        padding-left: 0;
        padding-right: 0;
    }
</style>

<!-- Tema Sidebar: Menggunakan gray-900 gelap agar ungu lebih menyala natural -->
<aside id="mainSidebar" class="w-64 bg-gray-900 h-full flex flex-col border-r border-gray-800 transition-all duration-300 overflow-y-auto overflow-x-hidden relative shadow-xl">
    
    <div class="h-20 shrink-0 flex items-center justify-between px-4 border-b border-gray-800 sticky top-0 bg-gray-900 z-20">
        <!-- FITUR BARU: Logo dan Teks Ungu -->
        <div class="flex items-center gap-2 hide-on-collapse">
            <img src="../../assets/images/image.png" alt="Logo SPK" class="w-8 h-8 object-contain drop-shadow-md">
            <h1 class="text-2xl font-bold tracking-wider text-white whitespace-nowrap">SPK<span class="text-purple-500">.PMB</span></h1>
        </div>
        
        <button onclick="toggleSidebar()" class="p-2 rounded-lg text-gray-400 hover:text-purple-400 hover:bg-gray-800 transition-colors focus:outline-none mx-auto lg:mx-0" title="Buka/Tutup Sidebar">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
    </div>
    
    <nav class="flex-1 px-3 py-6 space-y-2">
        
        <!-- BERANDA -->
        <a href="dashboard.php?page=beranda" class="nav-item flex items-center px-3 py-3 rounded-lg transition-all <?= ($page == 'beranda' || $page == '') ? 'bg-gradient-to-r from-purple-600 to-purple-500 text-white shadow-lg shadow-purple-500/30' : 'text-gray-400 hover:bg-gray-800 hover:text-purple-300' ?>" title="Beranda">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span class="ml-3 font-medium hide-on-collapse whitespace-nowrap">Beranda</span>
        </a>
        
        <!-- MASTER DATA -->
        <div class="relative pt-2">
            <button onclick="toggleDropdown('menuMaster', 'arrowMaster', event)" class="nav-item w-full flex items-center justify-between px-3 py-3 rounded-lg transition-all focus:outline-none <?= $isMasterData ? 'text-purple-400 bg-gray-800/80' : 'text-gray-400 hover:bg-gray-800 hover:text-purple-300' ?>" title="Master Data">
                <div class="flex items-center">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                    <span class="ml-3 font-medium hide-on-collapse whitespace-nowrap">Master Data</span>
                </div>
                <svg id="arrowMaster" class="w-4 h-4 hide-on-collapse transition-transform duration-300 <?= $isMasterData ? 'rotate-180' : '' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            
            <div id="menuMaster" class="sidebar-menu-transition <?= $isMasterData ? 'sidebar-dropdown-open' : 'sidebar-dropdown-closed' ?>">
                <div class="flex flex-col space-y-1 pl-4 pr-2 py-2 border-l-2 border-purple-500/30 ml-5 mt-1 hide-on-collapse">
                    
                    <a href="kriteria.php?page=kriteria" class="block px-4 py-2 text-sm rounded-lg transition-all <?= ($page == 'kriteria') ? 'text-purple-400 font-medium bg-purple-500/10' : 'text-gray-400 hover:text-purple-300 hover:bg-gray-800/50' ?> whitespace-nowrap">Data Kriteria</a>
                    
                    <!-- DATA MAHASISWA -->
                    <a href="mahasiswa.php?page=mahasiswa&jenis=mahasiswa" class="block px-4 py-2 text-sm rounded-lg transition-all <?= ($page == 'mahasiswa') ? 'text-purple-400 font-medium bg-purple-500/10' : 'text-gray-400 hover:text-purple-300 hover:bg-gray-800/50' ?> whitespace-nowrap">Data Mahasiswa</a>
                    
                    <a href="mahasiswa.php?page=alternatif&jenis=camaba" class="block px-4 py-2 text-sm rounded-lg transition-all <?= ($page == 'alternatif') ? 'text-purple-400 font-medium bg-purple-500/10' : 'text-gray-400 hover:text-purple-300 hover:bg-gray-800/50' ?> whitespace-nowrap">Set Alternatif</a>
                </div>
            </div>
        </div>
        
        <!-- ANALISIS NAIVE BAYES -->
        <div class="relative pt-2">
            <button onclick="toggleDropdown('menuAnalisis', 'arrowAnalisis', event)" class="nav-item w-full flex items-center justify-between px-3 py-3 rounded-lg transition-all focus:outline-none <?= $isAnalisis ? 'text-purple-400 bg-gray-800/80' : 'text-gray-400 hover:bg-gray-800 hover:text-purple-300' ?>" title="Analisis Naive Bayes">
                <div class="flex items-center">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    <span class="ml-3 font-medium hide-on-collapse whitespace-nowrap">Analisis Naive Bayes</span>
                </div>
                <svg id="arrowAnalisis" class="w-4 h-4 hide-on-collapse transition-transform duration-300 <?= $isAnalisis ? 'rotate-180' : '' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div id="menuAnalisis" class="sidebar-menu-transition <?= $isAnalisis ? 'sidebar-dropdown-open' : 'sidebar-dropdown-closed' ?>">
                <div class="flex flex-col space-y-1 pl-4 pr-2 py-2 border-l-2 border-purple-500/30 ml-5 mt-1 hide-on-collapse">
                    <a href="analisis.php?page=analisis" class="block px-4 py-2 text-sm rounded-lg transition-all <?= ($page == 'analisis') ? 'text-purple-400 font-medium bg-purple-500/10' : 'text-gray-400 hover:text-purple-300 hover:bg-gray-800/50' ?> whitespace-nowrap">Proses Klasifikasi</a>
                </div>
            </div>
        </div>
    </nav>
</aside>

<script>
    function toggleDropdown(menuId, arrowId, event) {
        if(event) { event.stopPropagation(); }
        
        const sidebar = document.getElementById('mainSidebar');
        if (sidebar.classList.contains('collapsed-sidebar')) {
            toggleSidebar();
            return;
        }

        const menu = document.getElementById(menuId);
        const arrow = document.getElementById(arrowId);
        
        if (menu.classList.contains('sidebar-dropdown-closed')) {
            menu.classList.remove('sidebar-dropdown-closed');
            menu.classList.add('sidebar-dropdown-open');
            arrow.classList.add('rotate-180');
        } else {
            menu.classList.remove('sidebar-dropdown-open');
            menu.classList.add('sidebar-dropdown-closed');
            arrow.classList.remove('rotate-180');
        }
    }

    function toggleSidebar() {
        const sidebar = document.getElementById('mainSidebar');
        sidebar.classList.toggle('collapsed-sidebar');
        
        if (sidebar.classList.contains('collapsed-sidebar')) {
            const openMenus = document.querySelectorAll('.sidebar-dropdown-open');
            openMenus.forEach(menu => {
                menu.classList.remove('sidebar-dropdown-open');
                menu.classList.add('sidebar-dropdown-closed');
            });
            const rotatedArrows = document.querySelectorAll('.rotate-180');
            rotatedArrows.forEach(arrow => {
                arrow.classList.remove('rotate-180');
            });
        }
    }
</script>