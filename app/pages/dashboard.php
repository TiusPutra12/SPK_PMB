<?php 
include '../includes/head.php'; 
echo $style_css; 

$json_path = __DIR__ . '/../data/pendaftar.json';
$pendaftar_list = [];
if (file_exists($json_path)) {
    $pendaftar_list = json_decode(file_get_contents($json_path), true) ?? [];
}

$list_prodi = array_values(array_unique(array_column($pendaftar_list, 'prodi')));
    sort($list_prodi);
$list_tahun = array_values(array_unique(array_column($pendaftar_list, 'tahun')));
    rsort($list_tahun);
?>

    <main class="p-8 flex-1">
        <div class="stats-grid">
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
                    <span class="stat-unit">Mahasiswa</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <span class="stat-title">TIDAK DITERIMA</span>
                    <div class="stat-icon pink">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>
                </div>
                <div class="stat-card-body">
                    <?php 
                    $tidak_diterima = count(array_filter($pendaftar_list, fn($p) => ($p['status'] ?? '') === 'Tidak Diterima'));
                    ?>
                    <span class="stat-value"><?= $tidak_diterima ?></span>
                    <span class="stat-unit">Mahasiswa</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <span class="stat-title">RATA-RATA VEKTOR V</span>
                    <div class="stat-icon indigo">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                    </div>
                </div>
                <div class="stat-card-body">
                    <span class="stat-value purple-text">0.824</span>
                    <span class="stat-unit">Indeks WP</span>
                </div>
            </div>
        </div>

        <div class="filter-section">
            <div class="filter-grid">
                <div class="filter-item">
                    <label for="filterProdi" class="filter-label">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Program Studi
                    </label>
                    <select id="filterProdi" class="filter-select">
                        <option value="">-- Semua Program Studi --</option>
                        <?php foreach ($list_prodi as $prodi): ?>
                            <option value="<?= htmlspecialchars($prodi) ?>"><?= htmlspecialchars($prodi) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-item">
                    <label for="filterTahun" class="filter-label">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Tahun Akademik
                    </label>
                    <select id="filterTahun" class="filter-select">
                        <option value="">-- Semua Tahun --</option>
                        <?php foreach ($list_tahun as $tahun): ?>
                            <option value="<?= htmlspecialchars($tahun) ?>"><?= htmlspecialchars($tahun) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-item">
                    <label for="searchNama" class="filter-label">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Cari Nama Pendaftar
                    </label>
                    <input type="text" id="searchNama" class="filter-input" placeholder="Ketik nama mahasiswa...">
                </div>

                <div class="filter-item" style="flex: 0 0 auto; min-width: auto;">
                    <button type="button" id="btnResetFilter" class="btn-reset-filter" title="Reset semua filter">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Reset Filter
                    </button>
                </div>

            </div>
        </div>

        <div class="dashboard-split-container" id="splitContainer">
            <div class="master-table-col" id="masterTableCol">
                <div class="table-card">
                    <div class="table-card-header">
                        <div class="table-card-title">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span>Daftar Pendaftar</span>
                            <span id="counterData" class="table-badge-counter"><?= count($pendaftar_list) ?> Data</span>
                        </div>
                    </div>

                    <div class="table-scroll-container">
                        <table class="custom-table" id="tableMahasiswa">
                            <thead>
                                <tr>
                                    <th class="w-12 text-center col-hide-on-split">No</th>
                                    <th class="col-nama">Nama Mahasiswa</th>
                                    <th class="col-hide-on-split">Program Studi</th>
                                    <th class="w-28 text-center col-hide-on-split">Tahun</th>
                                    <th class="w-32 text-center col-hide-on-split">Status</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyMahasiswa">
                                <?php 
                                $no = 1;
                                if (!empty($pendaftar_list)):
                                    foreach ($pendaftar_list as $item): 
                                        $id = $item['id'];
                                        $status = $item['status'] ?? 'Diterima';
                                        $status_class = ($status === 'Tidak Diterima') ? 'tidak-diterima' : (($status === 'Cadangan') ? 'cadangan' : 'diterima');
                                ?>
                                    <tr class="data-row" 
                                        data-id="<?= $id ?>"
                                        data-nama="<?= htmlspecialchars(strtolower($item['nama'])) ?>"
                                        data-prodi="<?= htmlspecialchars($item['prodi']) ?>"
                                        data-tahun="<?= htmlspecialchars($item['tahun']) ?>"
                                        onclick="selectStudent(<?= $id ?>)">
                                        
                                        <td class="text-center font-semibold text-gray-500 row-number col-hide-on-split"><?= $no++ ?></td>
                                        
                                        <td class="col-nama">
                                            <div class="font-semibold text-gray-900 leading-snug"><?= htmlspecialchars($item['nama']) ?></div>
                                            <div class="text-xs text-gray-400 mt-0.5">NIM: <?= htmlspecialchars($item['nisn'] ?? '-') ?></div>
                                        </td>
                                        
                                        <td class="col-hide-on-split">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-purple-50 text-purple-700 border border-purple-200">
                                                <?= htmlspecialchars($item['prodi']) ?>
                                            </span>
                                        </td>
                                        
                                        <td class="text-center font-medium text-gray-600 col-hide-on-split">
                                            <?= htmlspecialchars($item['tahun']) ?>
                                        </td>
                                        
                                        <td class="text-center col-hide-on-split">
                                            <span class="status-pill <?= $status_class ?>">
                                                <?= htmlspecialchars($status) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php 
                                    endforeach;
                                else: 
                                ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic">
                                            Belum ada data pendaftar yang tersedia.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div id="noDataNotice" class="hidden px-6 py-12 text-center text-gray-400 italic">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Tidak ditemukan data calon mahasiswa yang sesuai.
                    </div>
                </div>
            </div>

            <div class="detail-sidebar-col" id="detailSidebarCol">
                <div class="detail-main-card">
                    <div class="detail-main-header">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 text-white font-bold flex items-center justify-center text-sm shadow" id="detailAvatar">
                                A
                            </span>
                            <div>
                                <h3 class="font-bold text-gray-900 text-base leading-snug" id="detailNamaHeader">
                                    Nama Mahasiswa
                                </h3>
                                <p class="text-xs text-gray-500" id="detailSubHeader">
                                    Program Studi &bull; Tahun
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span id="detailBadgeHeader" class="status-pill diterima">Diterima</span>
                            <button type="button" class="btn-close-detail" onclick="closeStudentDetail()" title="Tutup Detail (Kembali ke Tabel)">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="detail-body-container" id="detailBodyContainer">
                        <?php 
                        foreach ($pendaftar_list as $item): 
                            $id = $item['id'];
                            $identitas = $item['identitas'] ?? [];
                            $nilai = $item['nilai'] ?? [];
                            $status = $item['status'] ?? 'Diterima';
                            $status_class = ($status === 'Tidak Diterima') ? 'tidak-diterima' : (($status === 'Cadangan') ? 'cadangan' : 'diterima');
                        ?>
                            <div class="student-detail-pane hidden" id="pane-<?= $id ?>"
                                 data-nama="<?= htmlspecialchars($item['nama']) ?>"
                                 data-inisial="<?= strtoupper(substr($item['nama'], 0, 1)) ?>"
                                 data-sub="<?= htmlspecialchars($item['prodi'] . ' • Tahun ' . $item['tahun'] . ' • NIM: ' . ($item['nisn'] ?? '-')) ?>"
                                 data-status="<?= htmlspecialchars($status) ?>"
                                 data-statusclass="<?= $status_class ?>">
                                
                                <div class="detail-split-grid">
                                    <div class="detail-panel">
                                        <div class="panel-header bg-purple-50/70 border-b border-purple-100">
                                            <div class="panel-title text-purple-900 font-bold">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                Identitas Lengkap
                                            </div>
                                            <span class="text-xs font-semibold px-2 py-0.5 rounded bg-white text-purple-700 border border-purple-200">
                                                <?= htmlspecialchars($identitas['jalur'] ?? 'Reguler') ?>
                                            </span>
                                        </div>
                                        <div class="panel-body">
                                            <div class="identitas-grid">
                                                <div class="id-field">
                                                    <span class="id-label">NIM / NISN</span>
                                                    <span class="id-val font-mono text-purple-700"><?= htmlspecialchars($item['nisn'] ?? '-') ?></span>
                                                </div>
                                                <div class="id-field">
                                                    <span class="id-label">NIK (KTP/KK)</span>
                                                    <span class="id-val font-mono"><?= htmlspecialchars($identitas['nik'] ?? '-') ?></span>
                                                </div>
                                                <div class="id-field">
                                                    <span class="id-label">Jenis Kelamin</span>
                                                    <span class="id-val"><?= htmlspecialchars($identitas['jenis_kelamin'] ?? '-') ?></span>
                                                </div>
                                                <div class="id-field">
                                                    <span class="id-label">Tempat, Tgl Lahir</span>
                                                    <span class="id-val"><?= htmlspecialchars(($identitas['tempat_lahir'] ?? '') . ', ' . ($identitas['tanggal_lahir'] ?? '')) ?></span>
                                                </div>
                                                <div class="id-field">
                                                    <span class="id-label">Asal Sekolah</span>
                                                    <span class="id-val"><?= htmlspecialchars($identitas['asal_sekolah'] ?? '-') ?></span>
                                                </div>
                                                <div class="id-field">
                                                    <span class="id-label">Jurusan Asal</span>
                                                    <span class="id-val"><?= htmlspecialchars($identitas['jurusan_asal'] ?? '-') ?></span>
                                                </div>
                                                <div class="id-field">
                                                    <span class="id-label">No. HP / WhatsApp</span>
                                                    <span class="id-val font-mono"><?= htmlspecialchars($identitas['no_hp'] ?? '-') ?></span>
                                                </div>
                                                <div class="id-field">
                                                    <span class="id-label">Email</span>
                                                    <span class="id-val text-blue-600 underline font-normal text-xs"><?= htmlspecialchars($identitas['email'] ?? '-') ?></span>
                                                </div>
                                                <div class="id-field" style="grid-column: 1 / -1;">
                                                    <span class="id-label">Alamat Lengkap</span>
                                                    <span class="id-val font-normal text-gray-700 text-xs mt-0.5 leading-relaxed"><?= htmlspecialchars($identitas['alamat'] ?? '-') ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="detail-panel">
                                        <div class="panel-header bg-emerald-50/70 border-b border-emerald-100">
                                            <div class="panel-title text-emerald-900 font-bold">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                                </svg>
                                                Rincian Nilai (SPK WP)
                                            </div>
                                            <span class="text-xs font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">
                                                Ranking #<?= htmlspecialchars($nilai['peringkat'] ?? '-') ?>
                                            </span>
                                        </div>
                                        <div class="panel-body">
                                            <table class="nilai-subtable">
                                                <thead>
                                                    <tr>
                                                        <th>Kriteria</th>
                                                        <th class="text-center w-14">Bobot</th>
                                                        <th class="text-center w-16">Nilai</th>
                                                        <th>Ket.</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($nilai['kriteria'])): ?>
                                                        <?php foreach ($nilai['kriteria'] as $krit): ?>
                                                            <tr>
                                                                <td class="font-medium text-gray-800">
                                                                    <span class="text-purple-600 font-bold mr-1"><?= htmlspecialchars($krit['kode']) ?></span>
                                                                    <?= htmlspecialchars($krit['nama']) ?>
                                                                </td>
                                                                <td class="text-center text-xs font-semibold text-gray-500">
                                                                    <?= htmlspecialchars($krit['bobot']) ?>
                                                                </td>
                                                                <td class="text-center">
                                                                    <span class="score-badge"><?= number_format($krit['nilai'], 1) ?></span>
                                                                </td>
                                                                <td class="text-xs text-gray-600">
                                                                    <?= htmlspecialchars($krit['keterangan']) ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>

                                            <div class="spk-summary-box">
                                                <div class="spk-item">
                                                    <div class="spk-item-label">Vektor S</div>
                                                    <div class="spk-item-value"><?= htmlspecialchars($nilai['vektor_s'] ?? '-') ?></div>
                                                </div>
                                                <div class="spk-item">
                                                    <div class="spk-item-label">Vektor V</div>
                                                    <div class="spk-item-value text-indigo-600"><?= htmlspecialchars($nilai['vektor_v'] ?? '-') ?></div>
                                                </div>
                                                <div class="spk-item">
                                                    <div class="spk-item-label">Keputusan</div>
                                                    <div class="mt-0.5">
                                                        <span class="status-pill <?= $status_class ?> text-xs">
                                                            <?= htmlspecialchars($status) ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="../../assets/js/dashboard.js?v=<?= time() ?>"></script>
</body>
</html>
