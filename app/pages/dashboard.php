<?php 

require_once __DIR__ . '/../controllers/DashboardController.php';

// Include head and initialize controller
include '../includes/head.php'; 
echo $style_css; 

$dashboardController = new DashboardController($koneksi);
$dashboardController->handleRequest();
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if (isset($_SESSION['success_msg'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '<?= addslashes($_SESSION['success_msg']) ?>',
                confirmButtonColor: '#9333ea',
                timer: 2500
            });
            <?php unset($_SESSION['success_msg']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_msg'])): ?>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '<?= addslashes($_SESSION['error_msg']) ?>',
                confirmButtonColor: '#9333ea'
            });
            <?php unset($_SESSION['error_msg']); ?>
        <?php endif; ?>
    });
</script>

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
            <div class="stat-value"><?= count($pendaftar_list) ?></div>
            <span class="stat-meta text-emerald-600">Calon Mahasiswa Baru</span>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-title">KRITERIA PENILAIAN</span>
                <div class="stat-icon emerald">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                    </svg>
                </div>
            </div>
            <div class="stat-value"><?= count($kriteria_list) ?></div>
            <span class="stat-meta text-emerald-600">Kriteria Pembobotan</span>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-title">JALUR BEASISWA</span>
                <div class="stat-icon amber">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                    </svg>
                </div>
            </div>
            <div class="stat-value"><?= $total_beasiswa ?></div>
            <span class="stat-meta text-emerald-600">Penerima Beasiswa</span>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-title">PROGRAM STUDI</span>
                <div class="stat-icon blue">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
                    </svg>
                </div>
            </div>
            <div class="stat-value"><?= count($prodi_list) ?></div>
            <span class="stat-meta text-emerald-600">Pilihan Jurusan</span>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span>Statistik Penerimaan Mahasiswa</span>
                </div>
                <div class="chart-legend">
                    <span class="legend-item"><span class="legend-dot purple"></span> Total Pendaftar</span>
                    <span class="legend-item"><span class="legend-dot emerald"></span> Diterima</span>
                    <span class="legend-item"><span class="legend-dot amber"></span> Beasiswa</span>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="chartTahunMasuk"></canvas>
            </div>
        </div>

        <div class="table-card flex flex-col">
            <div class="table-card-header flex items-center justify-between">
                <div class="table-card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span>Program Studi</span>
                    <span class="table-badge-counter"><?= count($prodi_list) ?> Prodi</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-400 italic hidden sm:block">* Klik baris untuk detail & aksi</span>
                    <!-- Tombol Tambah Program Studi Baru -->
                    <button type="button" onclick="modalTambahProdi()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-purple-600 hover:bg-purple-700 rounded-lg shadow-sm transition-all focus:outline-none cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span>Tambah Prodi</span>
                    </button>
                </div>
            </div>

            <!-- Tabel Interaktif Program Studi -->
            <div class="flex-1 overflow-y-auto max-h-[370px]">
                <table class="custom-table w-full">
                    <thead>
                        <tr>
                            <th class="w-12 text-center">No</th>
                            <th>Nama Program Studi</th>
                            <th class="w-20 text-center">Status</th>
                            <th class="w-20 text-center">Detail</th>
                        </tr>
                    </thead>
                    <tbody id="prodiTableBody">
                        <?php if (!empty($prodi_list)): ?>
                            <?php foreach ($prodi_list as $idx => $p):
                                $id_p       = (int)($p['id_program'] ?? $p['id_prodi']);
                                $nama_p     = htmlspecialchars($p['nama_program'] ?? $p['nama_prodi']);
                                $no         = $idx + 1;
                            ?>
                            <!-- Baris Utama -->
                            <tr class="data-row hover:bg-purple-50/50 transition-colors cursor-pointer"
                                onclick="toggleProdiDetail(<?= $id_p ?>)">
                                <td class="text-center font-bold text-purple-600"><?= $no ?></td>
                                <td class="font-semibold text-gray-900"><?= $nama_p ?></td>
                                <td class="text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">Aktif</span>
                                </td>
                                <td class="text-center" onclick="event.stopPropagation();">
                                    <button type="button"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 bg-purple-50 hover:bg-purple-100 text-purple-700 text-xs font-semibold rounded-full border border-purple-200 transition-colors"
                                        onclick="toggleProdiDetail(<?= $id_p ?>)">
                                        <svg id="chevron-prodi-<?= $id_p ?>" xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>

                            <!-- Baris Detail (Expand) -->
                            <tr id="detail-row-prodi-<?= $id_p ?>" style="display: none;" class="bg-gray-50/70 border-b border-purple-100/60">
                                <td colspan="4" class="p-0">
                                    <div id="detail-content-prodi-<?= $id_p ?>" class="grid grid-rows-[0fr] transition-[grid-template-rows] duration-300 ease-in-out">
                                        <div class="overflow-hidden">
                                            <div class="p-5 pl-10 bg-gradient-to-r from-purple-50/40 via-white to-purple-50/40 border-l-4 border-purple-500">
                                                <div class="flex items-center justify-between mb-3">
                                                    <h4 class="text-xs font-bold uppercase tracking-wider text-purple-900 flex items-center gap-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        Detail Program Studi
                                                    </h4>
                                                </div>

                                                <div class="grid grid-cols-2 gap-4 mb-4">
                                                    <div class="p-3 bg-white rounded-lg border border-purple-100 shadow-sm">
                                                        <span class="text-xs text-gray-400 font-medium block mb-1">ID Program</span>
                                                        <span class="text-sm font-bold text-purple-700 font-mono">#<?= str_pad($id_p, 3, '0', STR_PAD_LEFT) ?></span>
                                                    </div>
                                                    <div class="p-3 bg-white rounded-lg border border-purple-100 shadow-sm">
                                                        <span class="text-xs text-gray-400 font-medium block mb-1">Jenjang</span>
                                                        <span class="text-sm font-bold text-gray-800">Strata 1 (S1)</span>
                                                    </div>
                                                    <div class="p-3 bg-white rounded-lg border border-purple-100 shadow-sm col-span-2">
                                                        <span class="text-xs text-gray-400 font-medium block mb-1">Nama Lengkap Program Studi</span>
                                                        <span class="text-sm font-semibold text-gray-800"><?= $nama_p ?></span>
                                                    </div>
                                                </div>

                                                <!-- Aksi Edit & Hapus -->
                                                <div class="flex items-center gap-2 pt-2 border-t border-purple-100">
                                                    <button type="button"
                                                        onclick="modalEditProdi(<?= $id_p ?>, '<?= addslashes($p['nama_program'] ?? $p['nama_prodi']) ?>')"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg transition-colors">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                        </svg>
                                                        Edit Nama
                                                    </button>
                                                    <button type="button"
                                                        onclick="konfirmasiHapusProdi(<?= $id_p ?>, '<?= addslashes($p['nama_program'] ?? $p['nama_prodi']) ?>')"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 rounded-lg transition-colors">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                        Hapus
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-gray-400 italic text-sm">
                                    Belum ada program studi yang terdaftar. Silakan tambahkan terlebih dahulu.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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

function toggleProdiDetail(id) {
    const detailRow     = document.getElementById('detail-row-prodi-' + id);
    const detailContent = document.getElementById('detail-content-prodi-' + id);
    const chevron       = document.getElementById('chevron-prodi-' + id);
    if (!detailRow || !detailContent) return;

    const isClosed = detailRow.style.display === 'none' || detailContent.classList.contains('grid-rows-[0fr]');

    // Tutup semua baris lain dulu
    document.querySelectorAll('[id^="detail-row-prodi-"]').forEach(row => {
        const rId = row.id.replace('detail-row-prodi-', '');
        const c   = document.getElementById('detail-content-prodi-' + rId);
        const ch  = document.getElementById('chevron-prodi-' + rId);
        if (row !== detailRow && row.style.display !== 'none') {
            if (c) { c.classList.remove('grid-rows-[1fr]'); c.classList.add('grid-rows-[0fr]'); }
            if (ch) ch.classList.remove('rotate-180');
            setTimeout(() => { row.style.display = 'none'; }, 300);
        }
    });

    if (isClosed) {
        detailRow.style.display = '';
        requestAnimationFrame(() => {
            detailContent.classList.remove('grid-rows-[0fr]');
            detailContent.classList.add('grid-rows-[1fr]');
            if (chevron) chevron.classList.add('rotate-180');
        });
    } else {
        detailContent.classList.remove('grid-rows-[1fr]');
        detailContent.classList.add('grid-rows-[0fr]');
        if (chevron) chevron.classList.remove('rotate-180');
        setTimeout(() => {
            if (detailContent.classList.contains('grid-rows-[0fr]')) {
                detailRow.style.display = 'none';
            }
        }, 300);
    }
}

function _sendProdiAjax(formData, successCb, failCb) {
    fetch('dashboard.php?page=beranda', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) successCb(data);
        else failCb(data.message);
    })
    .catch(() => failCb('Gagal menghubungi server.'));
}

function modalTambahProdi() {
    Swal.fire({
        title: 'Tambah Program Studi',
        html: `<div class="text-left">
                   <label class="block text-sm font-medium text-gray-700 mb-1">Nama Program Studi</label>
                   <input id="swal-nama-prodi" class="swal2-input" style="margin:0;width:100%;box-sizing:border-box;" placeholder="Contoh: S1 Desain Komunikasi Visual">
               </div>`,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Simpan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#9333ea',
        cancelButtonColor: '#6b7280',
        preConfirm: () => {
            const v = document.getElementById('swal-nama-prodi').value.trim();
            if (!v) { Swal.showValidationMessage('Nama program studi tidak boleh kosong!'); return false; }
            return v;
        }
    }).then(result => {
        if (!result.isConfirmed) return;
        const fd = new FormData();
        fd.append('aksi', 'tambah_prodi');
        fd.append('nama_program', result.value);
        _sendProdiAjax(fd,
            data => Swal.fire({ icon:'success', title:'Berhasil!', text: data.message, confirmButtonColor:'#9333ea', timer:2000 })
                        .then(() => location.reload()),
            msg  => Swal.fire({ icon:'error', title:'Gagal!', text: msg, confirmButtonColor:'#9333ea' })
        );
    });
}

function modalEditProdi(id, namaLama) {
    Swal.fire({
        title: 'Edit Program Studi',
        html: `<div class="text-left">
                   <label class="block text-sm font-medium text-gray-700 mb-1">Nama Baru Program Studi</label>
                   <input id="swal-edit-prodi" class="swal2-input" style="margin:0;width:100%;box-sizing:border-box;" value="${namaLama}">
               </div>`,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Simpan Perubahan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6b7280',
        preConfirm: () => {
            const v = document.getElementById('swal-edit-prodi').value.trim();
            if (!v) { Swal.showValidationMessage('Nama program studi tidak boleh kosong!'); return false; }
            return v;
        }
    }).then(result => {
        if (!result.isConfirmed) return;
        const fd = new FormData();
        fd.append('aksi', 'edit_prodi');
        fd.append('id_program', id);
        fd.append('nama_program', result.value);
        _sendProdiAjax(fd,
            data => Swal.fire({ icon:'success', title:'Berhasil!', text: data.message, confirmButtonColor:'#9333ea', timer:2000 })
                        .then(() => location.reload()),
            msg  => Swal.fire({ icon:'error', title:'Gagal!', text: msg, confirmButtonColor:'#9333ea' })
        );
    });
}

function konfirmasiHapusProdi(id, nama) {
    Swal.fire({
        icon: 'warning',
        title: 'Hapus Program Studi?',
        html: `Apakah Anda yakin ingin menghapus program studi:<br><strong class="text-red-600">${nama}</strong>?<br><small class="text-gray-500">Tindakan ini tidak dapat dibatalkan.</small>`,
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
    }).then(result => {
        if (!result.isConfirmed) return;
        const fd = new FormData();
        fd.append('aksi', 'hapus_prodi');
        fd.append('id_program', id);
        _sendProdiAjax(fd,
            data => Swal.fire({ icon:'success', title:'Terhapus!', text: data.message, confirmButtonColor:'#9333ea', timer:2000 })
                        .then(() => location.reload()),
            msg  => Swal.fire({ icon:'error', title:'Gagal!', text: msg, confirmButtonColor:'#9333ea' })
        );
    });
}
</script>

<script src="../../assets/js/dashboard.js?v=<?= time() ?>"></script>
</body>
</html>