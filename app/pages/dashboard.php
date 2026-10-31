<?php 

include '../includes/head.php'; 
echo $style_css; 

require_once __DIR__ . '/../controllers/DashboardController.php';

$dashboardController = new DashboardController($koneksi);
$dashboardData       = $dashboardController->getDashboardData();

$kriteria_list  = $dashboardData['kriteria_list'];
$prodi_list     = $dashboardData['prodi_list'];
$pendaftar_list = $dashboardData['pendaftar_list'];
$tahun_stat     = $dashboardData['tahun_stat'];
$total_beasiswa = $dashboardData['total_beasiswa'];
$chart_labels   = $dashboardData['chart_labels'];
$chart_total    = $dashboardData['chart_total'];
$chart_diterima = $dashboardData['chart_diterima'];
$chart_beasiswa = $dashboardData['chart_beasiswa'];
?>

<main class="p-8 flex-1 overflow-y-auto">
    <div class="stats-grid mb-8">
        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-title">TOTAL PENDAFTAR</span>
                <div class="stat-icon purple">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                    </svg>
                </div>
            </div>
            <div class="stat-card-body">
                <span class="stat-value"><?= number_format(count($pendaftar_list), 0, ',', '.') ?></span>
                <span class="stat-unit">Mahasiswa</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-title">DITERIMA (LOLOS)</span>
                <div class="stat-icon teal">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                    </svg>
                </div>
            </div>
            <div class="stat-card-body">
                <?php 
                $diterima = count(array_filter($pendaftar_list, fn($p) => ($p['status'] ?? '') === 'Diterima'));
                ?>
                <span class="stat-value"><?= $diterima ?></span>
                <span class="stat-unit">Calon Mahasiswa</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-title">TIDAK DITERIMA</span>
                <div class="stat-icon red">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                </div>
            </div>
            <div class="stat-card-body">
                <?php 
                $ditolak = count(array_filter($pendaftar_list, fn($p) => ($p['status'] ?? '') === 'Ditolak'));
                ?>
                <span class="stat-value"><?= $ditolak ?></span>
                <span class="stat-unit">Calon Mahasiswa</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-title">JUMLAH PENERIMA BEASISWA</span>
                <div class="stat-icon purple">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                    </svg>
                </div>
            </div>
            <div class="stat-card-body">
                <span class="stat-value purple-text"><?= number_format($total_beasiswa, 0, ',', '.') ?></span>
                <span class="stat-unit">Penerima Beasiswa</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="lg:col-span-2 table-card">
            <div class="table-card-header">
                <div class="table-card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                    </svg>
                    <span>Grafik Mahasiswa Berdasarkan Tahun Masuk</span>
                </div>
                <div class="flex items-center gap-4 text-xs font-semibold flex-wrap">
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-blue-700 inline-block"></span>
                        <span class="text-gray-700">Total Terdaftar</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-emerald-600 inline-block"></span>
                        <span class="text-gray-700">Diterima</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span>
                        <span class="text-gray-700">Penerima Beasiswa</span>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="relative w-full" style="height: 290px;">
                    <canvas id="chartTahunMasuk"></canvas>
                </div>
            </div>
        </div>

        <div class="table-card flex flex-col">
            <div class="table-card-header">
                <div class="table-card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span>Program Studi</span>
                    <span class="table-badge-counter"><?= count($prodi_list) ?> Prodi</span>
                </div>
            </div>
            <div class="p-4 flex-1 overflow-y-auto max-h-[340px]">
                <div class="divide-y divide-gray-100">
                    <?php foreach ($prodi_list as $idx => $p): ?>
                        <div class="py-3 px-2 flex items-center justify-between hover:bg-purple-50/50 rounded-lg transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="w-7 h-7 rounded-full bg-purple-100 text-purple-700 text-xs font-bold flex items-center justify-center font-mono">
                                    <?= htmlspecialchars($p['id_prodi']) ?>
                                </span>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-800 leading-snug">
                                        <?= htmlspecialchars($p['nama_prodi']) ?>
                                    </h4>
                                    <span class="text-xs text-gray-400">ID: <?= htmlspecialchars($p['id_prodi']) ?> &bull; Strata 1 (S1)</span>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Aktif
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
window.dashboardChartData = {
    labels: <?= json_encode($chart_labels) ?>,
    total: <?= json_encode($chart_total) ?>,
    diterima: <?= json_encode($chart_diterima) ?>,
    beasiswa: <?= json_encode($chart_beasiswa) ?>
};
</script>

<script src="../../assets/js/dashboard.js?v=<?= time() ?>"></script>
</body>
</html>
