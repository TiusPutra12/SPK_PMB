<?php
// app/pages/mahasiswa.php
require_once __DIR__ . '/../controllers/MahasiswaController.php';

$mhsCtrl = new MahasiswaController();
$mhsCtrl->handleRequest();

$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : '';
$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : 'camaba';

$wpData = $mhsCtrl->getMahasiswaListWithWP();
$pendaftar_list = $wpData['pendaftar_list'];
$kriteria_db = $wpData['kriteria_db'];
$nilai_raw_mhs = $wpData['nilai_raw'];

// FITUR BARU: Menarik Database Tabel Prodi untuk form tambah
$db_prodi_list = $mhsCtrl->getProdiList();

$list_prodi = array_values(array_filter(array_unique(array_column($pendaftar_list, 'prodi'))));
sort($list_prodi);
$list_tahun = array_values(array_filter(array_unique(array_column($pendaftar_list, 'tahun'))));
rsort($list_tahun);

if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

include '../includes/head.php'; 
/** @var string $style_css */
echo $style_css; 
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
                timer: 3000
            });
            <?php unset($_SESSION['success_msg']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_msg'])): ?>
            Swal.fire({
                icon: 'error',
                title: 'Gagal Menyimpan!',
                text: '<?= addslashes($_SESSION['error_msg']) ?>',
                confirmButtonColor: '#e11d48'
            });
            <?php unset($_SESSION['error_msg']); ?>
        <?php endif; ?>
    });
</script>

<main class="p-8 flex-1 overflow-y-auto">
    
    <?php if ($aksi === 'tambah' || $aksi === 'edit'): ?>
        <?php 
            $isEdit = ($aksi === 'edit');
            $id = $isEdit ? (int)$_GET['id'] : 0;
            $data = $isEdit ? ($mhsCtrl->getMahasiswaDetail($id) ?? []) : [];
            $editNilai = $nilai_raw_mhs[$id] ?? [];

            // Menentukan kriteria mana yang diinput manual (C1: Nilai Rapor, C4: Nilai TKA, C5: Skor Bahasa Inggris)
            $manual_kriteria_ids = [];
            $dropdown_kriteria_ids = [];
            $kriteria_meta = [];
            $cCounter = 1;
            foreach ($kriteria_db as $idK => $krit) {
                $isManual = ($cCounter === 1 || $cCounter === 4 || $cCounter === 5)
                    || in_array($idK, [1, 4, 5])
                    || stripos($krit['nama_kriteria'], 'Rapor') !== false
                    || stripos($krit['nama_kriteria'], 'TKA') !== false
                    || stripos($krit['nama_kriteria'], 'Inggris') !== false;

                if ($isManual) {
                    $manual_kriteria_ids[] = (int)$idK;
                } else {
                    $dropdown_kriteria_ids[] = (int)$idK;
                }

                $kriteria_meta[$idK] = [
                    'cNo' => $cCounter,
                    'nama' => $krit['nama_kriteria'],
                    'isManual' => $isManual
                ];
                $cCounter++;
            }
        ?>

        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800"><?= $isEdit ? 'Edit Pendaftaran Calon Mahasiswa' : 'Pendaftaran Calon Mahasiswa Baru' ?></h2>
                <p class="text-sm text-gray-500 mt-1">Lengkapi proses pendaftaran melalui langkah berurutan di bawah ini.</p>
            </div>
            <a href="mahasiswa.php?page=mahasiswa&jenis=camaba" class="px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition-colors">Kembali ke Daftar</a>
        </div>

        <div class="mb-8 max-w-4xl mx-auto">
            <div class="bg-white p-6 rounded-2xl border border-gray-200/80 shadow-sm">
                <!-- UI Progress Tracker -->
                <div class="flex items-center justify-between w-full relative max-w-md mx-auto">
                    <div class="flex flex-col items-center relative z-10 cursor-pointer" onclick="goToStep(1)">
                        <div id="step-node-1" class="w-12 h-12 rounded-full border-4 border-purple-600 bg-purple-600 text-white font-bold text-base flex items-center justify-center shadow-md transition-all duration-300">1</div>
                        <span id="step-label-1" class="text-xs font-bold text-purple-700 mt-2 text-center transition-colors">Data Diri</span>
                    </div>
                    
                    <div class="flex-1 h-1 mx-4 sm:mx-8 bg-gray-200 rounded relative overflow-hidden"><div id="step-line-1" class="h-full bg-purple-600 w-0 transition-all duration-500"></div></div>
                    
                    <div class="flex flex-col items-center relative z-10 cursor-pointer" onclick="validateAndGoStep(2)">
                        <div id="step-node-2" class="w-12 h-12 rounded-full border-4 border-gray-300 bg-white text-gray-500 font-bold text-base flex items-center justify-center shadow-sm transition-all duration-300">2</div>
                        <span id="step-label-2" class="text-xs font-semibold text-gray-400 mt-2 text-center transition-colors">Input Nilai</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="table-card max-w-4xl mx-auto">
            <div class="p-8">
                <!-- FORM MULTI-TAHAP -->
                <form id="formPendaftaran" action="" method="post">
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="id_mahasiswa" value="<?= $data['id_mahasiswa'] ?>">
                        <input type="hidden" name="status" value="<?= htmlspecialchars($data['status'] ?? 'Menunggu') ?>">
                    <?php endif; ?>
                    

                    <div id="section-step-1" class="space-y-6">
                        <div class="p-6 border border-purple-300 rounded-xl bg-purple-50/20">
                            <div class="flex items-center justify-between pb-4 border-b border-purple-200 mb-5">
                                <h3 class="text-sm font-bold uppercase tracking-wider text-purple-900 flex items-center gap-2"><span class="w-6 h-6 rounded-full bg-purple-200 text-purple-800 text-xs font-bold flex items-center justify-center">1</span> PROSES 1: IDENTITAS & DATA DIRI</h3>
                            </div>
                            
                            <div class="grid grid-cols-1 gap-5 mb-5">
                                <div class="filter-item">
                                    <label class="filter-label">Nama Lengkap <span class="text-red-500">*</span></label>
                                    <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($data['nama_mahasiswa'] ?? '') ?>" required class="filter-input w-full bg-white" placeholder="Masukkan nama lengkap">
                                </div>
                            </div>
                            
                            <div class="pt-5 border-t border-purple-200">
                                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Informasi Kontak & Domisili</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- KUNCI PERBAIKAN: Maxlength NIK diatur 16 -->
                                    <div class="filter-item">
                                        <label class="filter-label">NIK (Nomor Induk Kependudukan) <span class="text-red-500">*</span></label>
                                        <input type="text" id="nik" name="nik" maxlength="16" value="<?= htmlspecialchars($data['nik'] ?? '') ?>" oninput="this.value=this.value.replace(/[^0-9]/g,''); checkNikTanggalLive();" class="filter-input w-full bg-white font-mono" placeholder="16 digit NIK">
                                        <p id="nikStatusMsg" class="text-xs mt-1 text-gray-500 hidden"></p>
                                    </div>
                                    
                                    <div class="filter-item">
                                        <label class="filter-label">Jenis Kelamin <span class="text-red-500">*</span></label>
                                        <select id="jenis_kelamin" name="jenis_kelamin" onchange="checkNikTanggalLive();" class="filter-select w-full bg-white">
                                            <option value="L" <?= ($data['jenis_kelamin'] ?? 'L') == 'L' ? 'selected' : '' ?>>Laki-laki (L)</option>
                                            <option value="P" <?= ($data['jenis_kelamin'] ?? '') == 'P' ? 'selected' : '' ?>>Perempuan (P)</option>
                                        </select>
                                    </div>

                                    <!-- KUNCI PERBAIKAN: Memisahkan Input Tempat & Kalender (Date) -->
                                    <div class="filter-item md:col-span-2">
                                        <label class="filter-label">Tempat, Tanggal Lahir <span class="text-red-500">*</span></label>
                                        <div class="flex gap-2">
                                            <?php 
                                                $ttl_db = $data['tempat_tanggal_lahir'] ?? ''; 
                                                $tmp_lahir = ''; $tgl_lahir = '';
                                                if ($ttl_db !== '' && $ttl_db !== '-') {
                                                    $parts = explode(', ', $ttl_db);
                                                    if(count($parts) == 2) {
                                                        $tmp_lahir = $parts[0];
                                                        $tgl_lahir = $parts[1];
                                                    } else {
                                                        $tmp_lahir = $ttl_db;
                                                    }
                                                }
                                                if ($tgl_lahir !== '' && preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $tgl_lahir, $m)) {
                                                    $tgl_lahir = $m[3] . '-' . $m[2] . '-' . $m[1];
                                                }
                                            ?>
                                            <input type="text" id="tempat_lahir" name="tempat_lahir" value="<?= htmlspecialchars($tmp_lahir) ?>" class="filter-input w-1/2 bg-white" placeholder="Tempat (Mis: Denpasar)">
                                            <!-- type="date" otomatis memunculkan kalender browser tanpa merusak kemampuan input manual dari keyboard -->
                                            <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="<?= htmlspecialchars($tgl_lahir) ?>" onchange="checkNikTanggalLive();" class="filter-input w-1/2 bg-white text-gray-600 cursor-pointer">
                                        </div>
                                        <p id="tglStatusMsg" class="text-xs mt-1 text-gray-500 hidden"></p>
                                    </div>

                                    <div class="filter-item"><label class="filter-label">Asal Sekolah</label><input type="text" name="asal_sekolah" value="<?= htmlspecialchars($data['asal_sekolah'] ?? '') ?>" class="filter-input w-full bg-white" placeholder="Contoh: SMA Negeri 1 Jakarta"></div>
                                    
                                    <!-- KUNCI PERBAIKAN: Program Studi kini menggunakan Dropdown Triatma Mulya -->
                                    <div class="filter-item">
                                        <label class="filter-label">Program Studi Tujuan</label>
                                        <select name="jurusan_asal" class="filter-select w-full bg-white">
                                            <option value="">-- Pilih Program Studi (UNTRI) --</option>
                                            <?php foreach ($db_prodi_list as $prodi_item): ?>
                                                <option value="<?= htmlspecialchars($prodi_item) ?>" <?= (($data['jurusan_asal'] ?? '') === $prodi_item) ? 'selected' : '' ?>><?= htmlspecialchars($prodi_item) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="filter-item"><label class="filter-label">No. HP</label><input type="text" name="no_hp" maxlength="15" value="<?= htmlspecialchars($data['no_hp'] ?? '') ?>" class="filter-input w-full bg-white" placeholder="Contoh: 08123456789"></div>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end pt-2">
                            <button type="button" onclick="validateAndGoStep(2)" class="px-8 py-3 bg-gradient-to-r from-purple-600 to-purple-500 text-white font-bold rounded-lg shadow-md hover:opacity-90 transition-all flex items-center gap-2">Lanjut ke Proses 2: Input Nilai</button>
                        </div>
                    </div>

                    <div id="section-step-2" class="space-y-6 hidden">
                        <div class="p-6 border border-purple-300 rounded-xl bg-purple-50/20">
                            <div class="flex items-center justify-between pb-4 border-b border-purple-200 mb-5">
                                <h3 class="text-sm font-bold uppercase tracking-wider text-purple-900 flex items-center gap-2"><span class="w-6 h-6 rounded-full bg-purple-600 text-white text-xs font-bold flex items-center justify-center">2</span> PROSES 2: INPUT NILAI PENILAIAN</h3>
                            </div>
                            
                            <div class="space-y-4">
                                <?php 
                                $cNo = 1;
                                foreach ($kriteria_db as $idK => $krit): 
                                    $curSubId = $editNilai[$idK]['id_sub'] ?? 0;
                                    $curNilai = $editNilai[$idK]['nilai'] ?? '';
                                    $isManual = in_array($idK, $manual_kriteria_ids);
                                ?>
                                    <div class="p-5 bg-white border border-gray-200 rounded-xl shadow-sm hover:border-purple-300 transition-all">
                                        <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-100">
                                            <h4 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                                                <span class="w-2.5 h-2.5 rounded-full <?= $isManual ? 'bg-purple-600' : 'bg-blue-500' ?>"></span>
                                                <span><?= htmlspecialchars($krit['nama_kriteria']) ?> (C<?= $cNo ?>)</span>
                                            </h4>
                                            <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold <?= $isManual ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-blue-50 text-blue-700 border border-blue-200' ?>">
                                                <?= $isManual ? 'Input Nilai Manual' : 'Pilihan Kategori' ?>
                                            </span>
                                        </div>

                                        <?php if ($isManual): ?>
                                            <div class="filter-item">
                                                <label class="filter-label flex items-center justify-between">
                                                    <span>Nilai <?= htmlspecialchars($krit['nama_kriteria']) ?> <span class="text-red-500">*</span></span>
                                                    <span class="text-[11px] text-gray-400 font-normal">
                                                        <?= (stripos($krit['nama_kriteria'], 'Inggris') !== false || $cNo === 5) ? 'Skor Tes / TOEFL (contoh: 380, 450)' : 'Skala: 0 - 100' ?>
                                                    </span>
                                                </label>
                                                <input type="number" 
                                                       step="0.01" 
                                                       min="0" 
                                                       <?= (stripos($krit['nama_kriteria'], 'Inggris') !== false || $cNo === 5) ? '' : 'max="100"' ?> 
                                                       name="kriteria_nilai[<?= $idK ?>]" 
                                                       id="sub_nilai_<?= $idK ?>" 
                                                       value="<?= htmlspecialchars((string)$curNilai) ?>" 
                                                       class="filter-input w-full bg-white font-mono font-bold text-purple-900 border-purple-300 focus:ring-purple-500 text-base" 
                                                       placeholder="<?= (stripos($krit['nama_kriteria'], 'Inggris') !== false || $cNo === 5) ? 'Masukkan skor bahasa Inggris (misal: 450)' : 'Masukkan nilai ' . htmlspecialchars(strtolower($krit['nama_kriteria'])) . ' (misal: 85.50)' ?>" 
                                                       oninput="autoMatchSubKriteria(<?= $idK ?>)">
                                                
                                                <!-- Dropdown Sub Kriteria disembunyikan (nilai otomatis dihitung dan disimpan) -->
                                                <select name="kriteria_sub[<?= $idK ?>]" 
                                                        id="sub_select_<?= $idK ?>" 
                                                        class="hidden">
                                                    <option value="">-- Kategori Otomatis Sesuai Nilai --</option>
                                                    <?php foreach ($krit['subs'] as $sub): ?>
                                                        <option value="<?= $sub['id_sub'] ?>" 
                                                                data-score="<?= $sub['nilai'] ?>" 
                                                                data-nama="<?= htmlspecialchars($sub['nama_sub']) ?>" 
                                                                <?= ($curSubId == $sub['id_sub']) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($sub['nama_sub']) ?> (Skor SPK: <?= $sub['nilai'] ?>)
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        <?php else: ?>
                                            <div class="filter-item">
                                                <label class="filter-label flex items-center justify-between">
                                                    <span>Tingkat Prestasi <?= htmlspecialchars($krit['nama_kriteria']) ?> <span class="text-red-500">*</span></span>
                                                    <span class="text-[11px] text-gray-400 font-normal">Pilih opsi capaian</span>
                                                </label>
                                                <select name="kriteria_sub[<?= $idK ?>]" id="sub_select_<?= $idK ?>" class="filter-select w-full bg-white text-gray-800 font-medium" onchange="updateSubScore(<?= $idK ?>)">
                                                    <option value="">-- Pilih Tingkat Prestasi --</option>
                                                    <?php foreach ($krit['subs'] as $sub): ?>
                                                        <option value="<?= $sub['id_sub'] ?>" data-score="<?= $sub['nilai'] ?>" data-nama="<?= htmlspecialchars($sub['nama_sub']) ?>" <?= ($curSubId == $sub['id_sub']) ? 'selected' : '' ?>><?= htmlspecialchars($sub['nama_sub']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <!-- Skor Nilai Otomatis disembunyikan (nilai otomatis tersimpan ke sistem) -->
                                                <input type="hidden" name="kriteria_nilai[<?= $idK ?>]" id="sub_nilai_<?= $idK ?>" value="<?= htmlspecialchars((string)$curNilai) ?>">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php $cNo++; endforeach; ?>
                            </div>

                            <!-- HASIL GABUNGAN SEMUA SKOR NILAI PENILAIAN -->
                            <div class="p-6 bg-gradient-to-r from-purple-50/70 via-white to-purple-50/70 border-2 border-purple-300 rounded-2xl shadow-sm mt-6">
                                <div class="flex items-center justify-between pb-3.5 mb-4 border-b border-purple-200 flex-wrap gap-2">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center shadow-md">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-gray-900 text-sm tracking-wider uppercase">Hasil Gabungan Skor Nilai Penilaian</h4>
                                            <p class="text-xs text-gray-500">Hasil gabungan dan kalkulasi otomatis dari seluruh kriteria penilaian mahasiswa.</p>
                                        </div>
                                    </div>
                                    <span id="badge_status_gabungan" class="text-xs font-semibold px-3 py-1 rounded-full bg-gray-100 text-gray-600 border border-gray-200">
                                        Menunggu Input Nilai
                                    </span>
                                </div>

                                <!-- Field Menampilkan Hasil Gabungan Semua Skor Nilai -->
                                <div class="mb-5">
                                    <label class="filter-label text-xs font-bold text-purple-900 mb-1.5 flex items-center justify-between">
                                        <span>Hasil Gabungan Skor Nilai (Otomatis)</span>
                                        <span class="text-[11px] text-purple-600 font-semibold" id="text_terisi_counter">0/<?= count($kriteria_db) ?> Kriteria Terisi</span>
                                    </label>
                                    <div class="relative">
                                        <input type="text" id="field_hasil_gabungan" name="field_hasil_gabungan" readonly 
                                               class="filter-input w-full bg-white font-mono font-bold text-base sm:text-lg text-purple-900 border-2 border-purple-300 shadow-sm px-4 py-3 rounded-xl focus:outline-none" 
                                               placeholder="Hasil gabungan skor nilai akan otomatis dihitung setelah nilai diinput...">
                                    </div>
                                </div>

                                <!-- Rincian Akumulasi & Statistik -->
                                <div id="breakdown_gabungan_container" class="space-y-4 pt-3 border-t border-purple-100 hidden">
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <div class="p-3.5 bg-white rounded-xl border border-purple-100 shadow-sm text-center">
                                            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block mb-1">Total Akumulasi Skor</span>
                                            <span id="stat_total_skor" class="text-xl font-black text-purple-800 font-mono">-</span>
                                        </div>
                                        <div class="p-3.5 bg-white rounded-xl border border-purple-100 shadow-sm text-center">
                                            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block mb-1">Nilai Akhir Terbobot (SPK)</span>
                                            <span id="stat_skor_terbobot" class="text-xl font-black text-emerald-600 font-mono">-</span>
                                        </div>
                                        <div class="p-3.5 bg-white rounded-xl border border-purple-100 shadow-sm text-center">
                                            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block mb-1">Rata-rata Skor Kriteria</span>
                                            <span id="stat_rata_skor" class="text-xl font-black text-blue-600 font-mono">-</span>
                                        </div>
                                    </div>

                                    <div>
                                        <span class="text-xs font-bold text-gray-700 uppercase tracking-wider block mb-2">Rincian Skor Tiap Kriteria:</span>
                                        <div id="pills_breakdown_skor" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                                            <!-- Diisi secara otomatis oleh JavaScript -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between items-center pt-2 gap-3 flex-wrap">
                            <button type="button" onclick="goToStep(1)" class="px-6 py-3 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 transition-colors">Kembali ke Data Diri</button>
                            <button type="submit" name="<?= $isEdit ? 'simpan_edit' : 'simpan_tambah' ?>" class="px-8 py-3 bg-gradient-to-r from-purple-600 to-purple-500 text-white font-bold rounded-lg shadow-md hover:opacity-90 transition-all flex items-center gap-2 cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Simpan Data Mahasiswa</span>
                            </button>
                        </div>
                    </div>
                    </div>
                </form>
            </div>
        </div>
        
        <script>
            function updateSubScore(idK) {
                const select = document.getElementById('sub_select_' + idK);
                const input = document.getElementById('sub_nilai_' + idK);
                if (!select || !input) return;
                const selectedOption = select.options[select.selectedIndex];
                if(selectedOption && selectedOption.value !== "") {
                    input.value = selectedOption.getAttribute('data-score');
                } else {
                    input.value = "";
                }
                calculateCombinedScore();
            }

            // Sinkronisasi otomatis Sub Kriteria berdasarkan Nilai Angka Manual (C1, C4, C5)
            function autoMatchSubKriteria(idK) {
                const input = document.getElementById('sub_nilai_' + idK);
                const select = document.getElementById('sub_select_' + idK);
                const badge = document.getElementById('kategori_badge_' + idK);
                const scoreInd = document.getElementById('score_indicator_' + idK);

                if (!input || !select) return;

                const rawVal = input.value.trim();
                if (rawVal === '' || isNaN(rawVal)) {
                    select.value = '';
                    if (badge) badge.innerHTML = '';
                    if (scoreInd) scoreInd.textContent = '';
                    calculateCombinedScore();
                    return;
                }

                const val = parseFloat(rawVal);
                let matchedSubId = null;
                let matchedSubName = '';
                let matchedScore = '';

                const options = Array.from(select.options).filter(opt => opt.value !== "");

                // 1. Cek pola range "A <= nilai < B" atau "A - B"
                for (let opt of options) {
                    const name = opt.getAttribute('data-nama') || opt.text;
                    const rangeMatch = name.match(/(\d+(?:\.\d+)?)\s*<=\s*(?:nilai|[a-zA-Z]+)?\s*<\s*(\d+(?:\.\d+)?)/i);
                    if (rangeMatch) {
                        const min = parseFloat(rangeMatch[1]);
                        const max = parseFloat(rangeMatch[2]);
                        if (val >= min && val < max) {
                            matchedSubId = opt.value;
                            matchedSubName = name;
                            matchedScore = opt.getAttribute('data-score');
                            break;
                        }
                    }
                    const dashMatch = name.match(/^(\d+(?:\.\d+)?)\s*-\s*(\d+(?:\.\d+)?)$/);
                    if (dashMatch) {
                        const min = parseFloat(dashMatch[1]);
                        const max = parseFloat(dashMatch[2]);
                        if (val >= min && val <= max) {
                            matchedSubId = opt.value;
                            matchedSubName = name;
                            matchedScore = opt.getAttribute('data-score');
                            break;
                        }
                    }
                }

                // 2. Cek pola batas atas ">= X" atau "> X"
                if (!matchedSubId) {
                    for (let opt of options) {
                        const name = opt.getAttribute('data-nama') || opt.text;
                        const gteMatch = name.match(/>=\s*(\d+(?:\.\d+)?)/);
                        if (gteMatch) {
                            const threshold = parseFloat(gteMatch[1]);
                            if (val >= threshold) {
                                matchedSubId = opt.value;
                                matchedSubName = name;
                                matchedScore = opt.getAttribute('data-score');
                                break;
                            }
                        }
                        const gtMatch = name.match(/^>\s*(\d+(?:\.\d+)?)/);
                        if (gtMatch) {
                            const threshold = parseFloat(gtMatch[1]);
                            if (val > threshold) {
                                matchedSubId = opt.value;
                                matchedSubName = name;
                                matchedScore = opt.getAttribute('data-score');
                                break;
                            }
                        }
                    }
                }

                // 3. Cek pola batas bawah "<= X" atau "< X"
                if (!matchedSubId) {
                    for (let opt of options) {
                        const name = opt.getAttribute('data-nama') || opt.text;
                        const ltMatch = name.match(/^<\s*(\d+(?:\.\d+)?)/);
                        if (ltMatch) {
                            const threshold = parseFloat(ltMatch[1]);
                            if (val < threshold) {
                                matchedSubId = opt.value;
                                matchedSubName = name;
                                matchedScore = opt.getAttribute('data-score');
                                break;
                            }
                        }
                        const lteMatch = name.match(/<=\s*(\d+(?:\.\d+)?)/);
                        if (lteMatch && !name.includes('>')) {
                            const threshold = parseFloat(lteMatch[1]);
                            if (val <= threshold) {
                                matchedSubId = opt.value;
                                matchedSubName = name;
                                matchedScore = opt.getAttribute('data-score');
                                break;
                            }
                        }
                    }
                }

                // 4. Fallback khusus kriteria
                if (!matchedSubId) {
                    if (idK === 1) { // Nilai Rapor
                        matchedSubId = val >= 80 ? '3' : (val >= 70 ? '2' : '1');
                    } else if (idK === 4) { // Nilai TKA
                        matchedSubId = val >= 75 ? '12' : (val >= 65 ? '11' : '10');
                    } else if (idK === 5) { // Skor Bhs Inggris
                        matchedSubId = val >= 350 ? '15' : (val >= 300 ? '14' : '13');
                    }
                    if (matchedSubId) {
                        const targetOpt = options.find(o => o.value == matchedSubId);
                        if (targetOpt) {
                            matchedSubName = targetOpt.getAttribute('data-nama') || targetOpt.text;
                            matchedScore = targetOpt.getAttribute('data-score');
                        }
                    }
                }

                if (matchedSubId) {
                    select.value = matchedSubId;
                    if (scoreInd && matchedScore) {
                        scoreInd.textContent = 'Bobot SPK: ' + matchedScore;
                    }
                    if (badge) {
                        badge.className = "text-xs mt-1.5 font-semibold text-emerald-600 flex items-center gap-1";
                        badge.innerHTML = `<svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Kategori: <strong>${matchedSubName}</strong> (Skor SPK: ${matchedScore || '-'})`;
                    }
                } else {
                    select.value = '';
                    if (badge) badge.innerHTML = '';
                    if (scoreInd) scoreInd.textContent = '';
                }

                calculateCombinedScore();
            }

            function onManualSubSelectChange(idK) {
                const select = document.getElementById('sub_select_' + idK);
                const scoreInd = document.getElementById('score_indicator_' + idK);
                const badge = document.getElementById('kategori_badge_' + idK);
                if (!select) return;
                const opt = select.options[select.selectedIndex];
                if (opt && opt.value !== '') {
                    const score = opt.getAttribute('data-score');
                    const name = opt.getAttribute('data-nama') || opt.text;
                    if (scoreInd && score) scoreInd.textContent = 'Bobot SPK: ' + score;
                    if (badge) {
                        badge.className = "text-xs mt-1.5 font-semibold text-emerald-600 flex items-center gap-1";
                        badge.innerHTML = `<svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Kategori: <strong>${name}</strong> (Skor SPK: ${score || '-'})`;
                    }
                } else {
                    if (scoreInd) scoreInd.textContent = '';
                    if (badge) badge.innerHTML = '';
                }
                calculateCombinedScore();
            }

            // Menghitung dan menampilkan Hasil Gabungan dari Semua Skor Nilai
            function calculateCombinedScore() {
                const kriteriaDb = <?= json_encode($kriteria_db) ?>;
                const manualIds = <?= json_encode($manual_kriteria_ids) ?>;

                let totalSubScore = 0;
                let weightedScore = 0;
                let totalBobot = 0;
                let filledCount = 0;
                const totalCriteria = Object.keys(kriteriaDb).length;
                let breakdown = [];

                let cNo = 1;
                for (let idK in kriteriaDb) {
                    const idInt = parseInt(idK, 10);
                    const krit = kriteriaDb[idK];
                    const bobot = parseFloat(krit.bobot) || 0;
                    totalBobot += bobot;

                    const isManual = manualIds.includes(idInt);
                    const sel = document.getElementById('sub_select_' + idK);
                    const inp = document.getElementById('sub_nilai_' + idK);

                    let currentScore = null;
                    let displayLabel = '-';
                    let rawVal = '';

                    if (isManual) {
                        const valStr = inp ? inp.value.trim() : '';
                        rawVal = valStr;
                        if (valStr !== '' && !isNaN(valStr) && sel && sel.selectedIndex >= 0) {
                            const opt = sel.options[sel.selectedIndex];
                            if (opt && opt.value !== '') {
                                currentScore = parseFloat(opt.getAttribute('data-score'));
                                displayLabel = (opt.getAttribute('data-nama') || opt.text).replace(/\s*\(Skor.*?\)/i, '');
                            }
                        }
                    } else {
                        if (sel && sel.selectedIndex >= 0) {
                            const opt = sel.options[sel.selectedIndex];
                            if (opt && opt.value !== '') {
                                currentScore = parseFloat(opt.getAttribute('data-score'));
                                displayLabel = opt.text;
                                rawVal = displayLabel;
                            }
                        }
                    }

                    if (currentScore !== null && !isNaN(currentScore)) {
                        filledCount++;
                        totalSubScore += currentScore;
                        weightedScore += (bobot / 100) * currentScore;
                        breakdown.push({
                            cNo: cNo,
                            nama: krit.nama_kriteria,
                            rawVal: rawVal,
                            score: currentScore,
                            bobot: bobot,
                            displayLabel: displayLabel
                        });
                    }

                    cNo++;
                }

                const fieldEl = document.getElementById('field_hasil_gabungan');
                const badgeEl = document.getElementById('badge_status_gabungan');
                const counterEl = document.getElementById('text_terisi_counter');
                const breakdownContainer = document.getElementById('breakdown_gabungan_container');
                const totalScoreEl = document.getElementById('stat_total_skor');
                const weightedScoreEl = document.getElementById('stat_skor_terbobot');
                const avgScoreEl = document.getElementById('stat_rata_skor');
                const pillsContainer = document.getElementById('pills_breakdown_skor');

                if (!fieldEl) return;

                if (counterEl) {
                    counterEl.textContent = `${filledCount}/${totalCriteria} Kriteria Terisi`;
                }

                if (filledCount === 0) {
                    fieldEl.value = '';
                    if (badgeEl) {
                        badgeEl.className = 'text-xs font-semibold px-3 py-1 rounded-full bg-gray-100 text-gray-500 border border-gray-200';
                        badgeEl.textContent = 'Belum Ada Nilai Terisi';
                    }
                    if (breakdownContainer) breakdownContainer.classList.add('hidden');
                    return;
                }

                const totalScoreFmt = totalSubScore.toFixed(2);
                const weightedPercent = (weightedScore * 100).toFixed(2);
                const avgScoreFmt = (totalSubScore / filledCount).toFixed(2);

                if (filledCount < totalCriteria) {
                    fieldEl.value = `Progres (${filledCount}/${totalCriteria} Kriteria Terisi) | Total Skor: ${totalScoreFmt} | Nilai Terbobot: ${weightedPercent}%`;
                    if (badgeEl) {
                        badgeEl.className = 'text-xs font-semibold px-3 py-1 rounded-full bg-amber-100 text-amber-800 border border-amber-200';
                        badgeEl.textContent = `Sebagian Terisi (${filledCount}/${totalCriteria})`;
                    }
                } else {
                    fieldEl.value = `Total Skor: ${totalScoreFmt} | Nilai Akhir Terbobot (SPK): ${weightedPercent}% (Rata-rata: ${avgScoreFmt})`;
                    if (badgeEl) {
                        badgeEl.className = 'text-xs font-semibold px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200';
                        badgeEl.textContent = 'Γ£ô Lengkap: Semua Kriteria Terisi';
                    }
                }

                if (totalScoreEl) totalScoreEl.textContent = totalScoreFmt;
                if (weightedScoreEl) weightedScoreEl.textContent = weightedPercent + '%';
                if (avgScoreEl) avgScoreEl.textContent = avgScoreFmt;

                if (pillsContainer) {
                    let htmlPills = '';
                    breakdown.forEach(item => {
                        htmlPills += `
                            <div class="p-3 bg-white rounded-xl border border-purple-100 shadow-sm flex flex-col justify-between">
                                <div class="flex items-center justify-between text-[11px] text-gray-500 mb-1">
                                    <span class="font-bold text-purple-700">C${item.cNo} (Bobot ${item.bobot}%)</span>
                                    <span class="text-gray-400 font-mono text-[10px] truncate max-w-[90px]">${item.displayLabel}</span>
                                </div>
                                <div class="flex items-baseline justify-between mt-1">
                                    <span class="text-xs font-semibold text-gray-700 truncate max-w-[130px]" title="${item.nama}">${item.nama}</span>
                                    <span class="font-mono font-bold text-sm text-purple-900 bg-purple-50 px-2 py-0.5 rounded-lg border border-purple-200">${item.score.toFixed(2)}</span>
                                </div>
                            </div>
                        `;
                    });
                    pillsContainer.innerHTML = htmlPills;
                }

                if (breakdownContainer) breakdownContainer.classList.remove('hidden');
            }

            function goToStep(step) {
                const s1 = document.getElementById('section-step-1');
                const s2 = document.getElementById('section-step-2');
                if (s1) s1.classList.add('hidden');
                if (s2) s2.classList.add('hidden');
                
                const cur = document.getElementById('section-step-' + step);
                if (cur) cur.classList.remove('hidden');

                for(let i=1; i<=2; i++) {
                    let node = document.getElementById('step-node-'+i);
                    let label = document.getElementById('step-label-'+i);
                    let line = document.getElementById('step-line-'+i);
                    
                    if(!node) continue;

                    if (i === step) {
                        node.className = "w-12 h-12 rounded-full border-4 border-purple-600 bg-purple-600 text-white font-bold text-base flex items-center justify-center shadow-md transition-all duration-300";
                        if (label) label.className = "text-xs font-bold text-purple-700 mt-2 text-center transition-colors";
                    } else if (i < step) {
                        node.className = "w-12 h-12 rounded-full border-4 border-purple-600 bg-white text-purple-600 font-bold text-base flex items-center justify-center shadow-sm transition-all duration-300";
                        if (label) label.className = "text-xs font-bold text-purple-700 mt-2 text-center transition-colors";
                    } else {
                        node.className = "w-12 h-12 rounded-full border-4 border-gray-300 bg-white text-gray-500 font-bold text-base flex items-center justify-center shadow-sm transition-all duration-300";
                        if (label) label.className = "text-xs font-semibold text-gray-400 mt-2 text-center transition-colors";
                    }

                    if(line) {
                        line.style.width = (i < step) ? '100%' : '0%';
                    }
                }
            }

            // Validasi Kesesuaian NIK dan Tanggal Lahir (Aturan Dukcapil)
            function validateNikTanggalLahir(nik, tglLahir, jk) {
                if (!nik || nik.length !== 16 || !/^\d{16}$/.test(nik)) {
                    return {
                        valid: false,
                        title: 'Format NIK Tidak Sesuai',
                        message: 'NIK harus berjumlah tepat 16 digit angka.'
                    };
                }

                if (!tglLahir) {
                    return {
                        valid: false,
                        title: 'Tanggal Lahir Belum Dipilih',
                        message: 'Silakan pilih tanggal lahir calon mahasiswa terlebih dahulu.'
                    };
                }

                const parts = tglLahir.split('-');
                if (parts.length !== 3) {
                    return {
                        valid: false,
                        title: 'Format Tanggal Tidak Valid',
                        message: 'Format tanggal lahir tidak valid.'
                    };
                }

                const inputYear = parseInt(parts[0], 10);
                const inputMonth = parseInt(parts[1], 10);
                const inputDay = parseInt(parts[2], 10);

                const inputYY = String(inputYear).slice(-2);
                const inputMM = String(inputMonth).padStart(2, '0');
                const inputDD = String(inputDay).padStart(2, '0');

                // Ekstrak 6 digit tanggal lahir dari NIK (index 6 s.d 11)
                const nikDayRaw = parseInt(nik.substr(6, 2), 10);
                const nikMM = nik.substr(8, 2);
                const nikMonth = parseInt(nikMM, 10);
                const nikYY = nik.substr(10, 2);

                let nikGender = '';
                let nikRealDay = 0;

                // Ketentuan: Penambahan angka 40 untuk perempuan pada digit tanggal
                if (nikDayRaw > 40 && nikDayRaw <= 71) {
                    nikGender = 'P';
                    nikRealDay = nikDayRaw - 40;
                } else if (nikDayRaw >= 1 && nikDayRaw <= 31) {
                    nikGender = 'L';
                    nikRealDay = nikDayRaw;
                } else {
                    return {
                        valid: false,
                        title: 'Tanggal Lahir NIK Tidak Valid',
                        message: `Digit tanggal pada NIK (${nik.substr(6, 2)}) tidak valid. Sesuai aturan Dukcapil, digit tanggal bernilai 01-31 (Laki-laki) atau 41-71 (Perempuan).`
                    };
                }

                if (nikMonth < 1 || nikMonth > 12) {
                    return {
                        valid: false,
                        title: 'Bulan Lahir NIK Tidak Valid',
                        message: `Digit bulan pada NIK (${nikMM}) tidak valid (harus 01-12).`
                    };
                }

                const isDayMatch = (inputDay === nikRealDay);
                const isMonthMatch = (inputMM === nikMM);
                const isYearMatch = (inputYY === nikYY);
                const isGenderMatch = (jk === nikGender);

                if (!isDayMatch || !isMonthMatch || !isYearMatch || !isGenderMatch) {
                    const nikDayFormatted = String(nikRealDay).padStart(2, '0');
                    const nikTglFormatted = `${nikDayFormatted}-${nikMM}-${nikYY}`;
                    const inputTglFormatted = `${inputDD}-${inputMM}-${inputYear}`;
                    const nikGenderText = (nikGender === 'P') ? 'Perempuan (Tanggal + 40)' : 'Laki-laki';
                    const inputGenderText = (jk === 'P') ? 'Perempuan' : 'Laki-laki';

                    let perbedaan = [];
                    if (!isGenderMatch) {
                        perbedaan.push(`├óΓé¼┬ó <strong>Jenis Kelamin:</strong> NIK menandakan <em>${nikGenderText}</em>, tetapi di form dipilih <em>${inputGenderText}</em>.`);
                    }
                    if (!isDayMatch) {
                        perbedaan.push(`├óΓé¼┬ó <strong>Hari/Tanggal:</strong> NIK menandakan tanggal <em>${nikRealDay}</em>, sedangkan di form dipilih tanggal <em>${inputDay}</em>.`);
                    }
                    if (!isMonthMatch) {
                        perbedaan.push(`├óΓé¼┬ó <strong>Bulan Lahir:</strong> NIK menandakan bulan <em>${nikMonth}</em>, sedangkan di form dipilih bulan <em>${inputMonth}</em>.`);
                    }
                    if (!isYearMatch) {
                        perbedaan.push(`├óΓé¼┬ó <strong>Tahun Lahir:</strong> NIK berakhiran tahun <em>${nikYY}</em>, sedangkan di form dipilih tahun <em>${inputYear}</em>.`);
                    }

                    return {
                        valid: false,
                        title: 'Tanggal Lahir Tidak Sesuai NIK!',
                        message: 'Tanggal lahir yang dimasukkan tidak sesuai dengan NIK.',
                        perbedaan: perbedaan.join('<br>'),
                        nikTgl: nikTglFormatted,
                        nikGender: nikGenderText,
                        inputTgl: inputTglFormatted,
                        inputGender: inputGenderText
                    };
                }

                return {
                    valid: true,
                    nikRealDay,
                    nikMonth,
                    nikYY,
                    nikGender
                };
            }

            function checkNikTanggalLive() {
                const nik = document.getElementById('nik')?.value.trim() || '';
                const tgl = document.getElementById('tanggal_lahir')?.value || '';
                const jk = document.getElementById('jenis_kelamin')?.value || 'L';
                const nikMsg = document.getElementById('nikStatusMsg');
                const tglMsg = document.getElementById('tglStatusMsg');

                if (!nikMsg || !tglMsg) return;

                if (nik.length === 0) {
                    nikMsg.classList.add('hidden');
                    tglMsg.classList.add('hidden');
                    return;
                }

                if (nik.length < 16) {
                    nikMsg.className = "text-xs mt-1 text-gray-400";
                    nikMsg.textContent = `${nik.length}/16 digit NIK`;
                    nikMsg.classList.remove('hidden');
                    tglMsg.classList.add('hidden');
                    return;
                }

                if (nik.length === 16 && tgl) {
                    const res = validateNikTanggalLahir(nik, tgl, jk);
                    if (res.valid) {
                        nikMsg.className = "text-xs mt-1 text-emerald-600 font-semibold flex items-center gap-1";
                        nikMsg.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Sesuai dengan tanggal lahir & jenis kelamin';
                        nikMsg.classList.remove('hidden');

                        tglMsg.className = "text-xs mt-1 text-emerald-600 font-semibold";
                        tglMsg.textContent = "├ó┼ôΓÇ£ Sesuai dengan 6 digit tanggal NIK";
                        tglMsg.classList.remove('hidden');
                    } else {
                        nikMsg.className = "text-xs mt-1 text-rose-500 font-semibold flex items-center gap-1";
                        nikMsg.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> ' + (res.title || 'Tidak sesuai dengan NIK');
                        nikMsg.classList.remove('hidden');

                        tglMsg.className = "text-xs mt-1 text-rose-500 font-semibold";
                        tglMsg.textContent = "├ó┼í┬á Tanggal lahir belum sesuai dengan 6 digit NIK";
                        tglMsg.classList.remove('hidden');
                    }
                } else if (nik.length === 16) {
                    nikMsg.className = "text-xs mt-1 text-purple-600 font-medium";
                    nikMsg.textContent = "├ó┼ôΓÇ£ 16 digit NIK lengkap. Pilih tanggal lahir untuk pencocokan.";
                    nikMsg.classList.remove('hidden');
                    tglMsg.classList.add('hidden');
                }
            }

            function validateStep1() {
                const nama = document.getElementById('nama') ? document.getElementById('nama').value.trim() : '';
                const nik = document.getElementById('nik') ? document.getElementById('nik').value.trim() : '';
                const tglLahir = document.getElementById('tanggal_lahir') ? document.getElementById('tanggal_lahir').value : '';
                const jk = document.getElementById('jenis_kelamin') ? document.getElementById('jenis_kelamin').value : 'L';

                if (!nama) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Belum Lengkap',
                        text: 'Nama Lengkap calon mahasiswa wajib diisi!',
                        confirmButtonColor: '#9333ea'
                    });
                    return false;
                }

                if (!nik) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Belum Lengkap',
                        text: 'NIK (Nomor Induk Kependudukan) wajib diisi!',
                        confirmButtonColor: '#9333ea'
                    });
                    return false;
                }

                if (nik.length !== 16 || !/^\d{16}$/.test(nik)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Format NIK Tidak Valid',
                        text: 'NIK harus berjumlah tepat 16 digit angka!',
                        confirmButtonColor: '#9333ea'
                    });
                    return false;
                }

                if (!tglLahir) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Belum Lengkap',
                        text: 'Tanggal lahir calon mahasiswa wajib dipilih!',
                        confirmButtonColor: '#9333ea'
                    });
                    return false;
                }

                const nikCheck = validateNikTanggalLahir(nik, tglLahir, jk);
                if (!nikCheck.valid) {
                    let htmlMsg = `
                        <div class="text-sm text-gray-700 text-left mb-3">
                            Tanggal lahir yang Anda masukkan <strong class="text-rose-600">tidak sesuai</strong> dengan NIK (Nomor Induk Kependudukan).
                        </div>
                    `;

                    if (nikCheck.perbedaan) {
                        htmlMsg += `
                            <div class="p-3 text-xs text-left bg-red-50 text-red-800 border border-red-200 rounded-lg mb-3 leading-relaxed">
                                <div class="font-bold text-red-900 border-b border-red-200 pb-1 mb-1.5">Rincian Perbedaan:</div>
                                ${nikCheck.perbedaan}
                            </div>
                        `;
                    }

                    htmlMsg += `
                        <div class="p-3 text-xs text-left bg-gray-50 text-gray-600 border border-gray-200 rounded-lg space-y-1">
                            <div class="font-bold text-gray-800 border-b border-gray-200 pb-1 mb-1">Ketentuan Format 16 Digit NIK:</div>
                            <div>ΓÇó <strong>6 Digit Awal (1-6):</strong> Kode Wilayah Pendaftaran</div>
                            <div>ΓÇó <strong>6 Digit Tengah (7-12):</strong> Tanggal Lahir (DDMMYY), dengan <em>penambahan angka 40</em> pada tanggal untuk perempuan</div>
                            <div>ΓÇó <strong>4 Digit Akhir (13-16):</strong> Nomor Urut Registrasi</div>
                        </div>
                    `;

                    Swal.fire({
                        icon: 'error',
                        title: 'Tanggal Lahir Tidak Sesuai!',
                        html: htmlMsg,
                        confirmButtonColor: '#9333ea',
                        confirmButtonText: 'Periksa Kembali'
                    });
                    return false;
                }

                return true;
            }

            function validateStep2() {
                const manualIds = <?= json_encode($manual_kriteria_ids) ?>;
                const dropdownIds = <?= json_encode($dropdown_kriteria_ids) ?>;
                const kriteriaMeta = <?= json_encode($kriteria_meta) ?>;

                let missing = [];

                manualIds.forEach(function(idK) {
                    const input = document.getElementById('sub_nilai_' + idK);
                    const val = input ? input.value.trim() : '';
                    const meta = kriteriaMeta[idK] || { nama: 'Kriteria ' + idK, cNo: idK };

                    if (val === '' || isNaN(val)) {
                        missing.push(meta.nama + ' (C' + meta.cNo + ') belum diisi');
                    } else {
                        const num = parseFloat(val);
                        if (num < 0) {
                            missing.push(meta.nama + ' (C' + meta.cNo + ') tidak boleh bernilai negatif');
                        } else if ((idK === 1 || idK === 4 || meta.cNo === 1 || meta.cNo === 4) && num > 100) {
                            missing.push(meta.nama + ' (C' + meta.cNo + ') bernilai melebihi batas 100');
                        }
                    }
                });

                dropdownIds.forEach(function(idK) {
                    const select = document.getElementById('sub_select_' + idK);
                    const val = select ? select.value.trim() : '';
                    const meta = kriteriaMeta[idK] || { nama: 'Kriteria ' + idK, cNo: idK };

                    if (val === '') {
                        missing.push(meta.nama + ' (C' + meta.cNo + ') belum dipilih');
                    }
                });

                if (missing.length > 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Nilai Penilaian Belum Lengkap',
                        html: '<div class="text-left text-sm text-gray-700 mb-2">Silakan periksa kembali nilai yang Anda input pada Proses 2:</div>'
                            + '<div class="p-3 text-xs text-left bg-amber-50 text-amber-900 border border-amber-200 rounded-lg leading-relaxed space-y-1 font-medium">'
                            + 'ΓÇó ' + missing.join('<br>ΓÇó ')
                            + '</div>',
                        confirmButtonColor: '#9333ea'
                    });
                    return false;
                }

                return true;
            }

            function validateAndGoStep(targetStep) {
                if (targetStep === 2) {
                    if (!validateStep1()) return;
                }
                goToStep(targetStep);
            }

            document.addEventListener("DOMContentLoaded", function() {
                checkNikTanggalLive();

                // Inisialisasi awal nilai manual (termasuk saat Edit pendaftar)
                const manualIdsInit = <?= json_encode($manual_kriteria_ids) ?>;
                manualIdsInit.forEach(function(idK) {
                    const input = document.getElementById('sub_nilai_' + idK);
                    if (input && input.value.trim() !== '') {
                        autoMatchSubKriteria(idK);
                    }
                });

                const dropdownIdsInit = <?= json_encode($dropdown_kriteria_ids) ?>;
                dropdownIdsInit.forEach(function(idK) {
                    updateSubScore(idK);
                });

                // Hitung kalkulasi gabungan seluruh skor nilai
                calculateCombinedScore();

                const formPendaftaran = document.getElementById('formPendaftaran');
                if (formPendaftaran) {
                    formPendaftaran.addEventListener('submit', function(e) {
                        if (!validateStep1()) {
                            e.preventDefault();
                            goToStep(1);
                            return false;
                        }
                        if (!validateStep2()) {
                            e.preventDefault();
                            goToStep(2);
                            return false;
                        }
                    });
                }
            });
        </script>

    <?php else: ?>
        <!-- ====================== DAFTAR MAHASISWA & FILTER ====================== -->
        <?php
            $search = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';
            $filter_prodi = isset($_GET['prodi']) ? $_GET['prodi'] : '';
            $filter_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : '';
            $filter_status = isset($_GET['status']) ? $_GET['status'] : '';
            
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $halaman = isset($_GET['hal']) ? (int)$_GET['hal'] : 1;
            
            $filtered_list = [];
            foreach ($pendaftar_list as $item) {
                if ($jenis === 'mahasiswa' && $item['status'] !== 'Diterima') { continue; }
                if ($search !== '' && strpos(strtolower($item['nama']), $search) === false && false) { continue; }
                if ($filter_prodi !== '' && $item['prodi'] !== $filter_prodi) { continue; }
                if ($filter_tahun !== '' && $item['tahun'] !== $filter_tahun) { continue; }
                if ($jenis === 'camaba' && $filter_status !== '' && $item['status'] !== $filter_status) { continue; }
                $filtered_list[] = $item;
            }

            $totalData = count($filtered_list);
            $totalPages = ceil($totalData / $limit);
            $offset = ($halaman - 1) * $limit;
            $paginated_list = array_slice($filtered_list, $offset, $limit);
        ?>

        <?php if ($jenis === 'camaba'): ?>
        <div class="flex justify-end mb-6">
            <a href="mahasiswa.php?page=mahasiswa&aksi=tambah&jenis=camaba" class="px-5 py-2.5 bg-gradient-to-r from-purple-600 to-purple-500 text-white font-medium rounded-lg shadow-sm text-sm hover:opacity-90">+ Tambah Pendaftar Baru</a>
        </div>
        <?php endif; ?>

        <div class="filter-section mb-6">
            <div class="filter-grid" style="align-items: flex-end;">
                
                <div class="filter-item" style="flex: 1; min-width: 140px;">
                    <label class="filter-label">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg> 
                        Program Studi
                    </label>
                    <select id="filterProdi" class="filter-select">
                        <option value="">-- Semua Prodi --</option>
                        <?php foreach ($list_prodi as $prodi_item): ?>
                            <option value="<?= htmlspecialchars($prodi_item) ?>" <?= $filter_prodi === $prodi_item ? 'selected' : '' ?>><?= htmlspecialchars($prodi_item) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-item" style="flex: 1; min-width: 120px;">
                    <label class="filter-label">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg> 
                        Tahun
                    </label>
                    <select id="filterTahun" class="filter-select">
                        <option value="">-- Semua --</option>
                        <?php foreach ($list_tahun as $tahun_item): ?>
                            <option value="<?= htmlspecialchars($tahun_item) ?>" <?= $filter_tahun === $tahun_item ? 'selected' : '' ?>><?= htmlspecialchars($tahun_item) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($jenis === 'camaba'): ?>
                <div class="filter-item" style="flex: 1; min-width: 160px;">
                    <label class="filter-label">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" /></svg> 
                        Status Pendaftar
                    </label>
                    <select id="filterStatus" class="filter-select">
                        <option value="">-- Semua Data --</option>
                        <option value="Menunggu" <?= $filter_status === 'Menunggu' ? 'selected' : '' ?>>Menunggu Analisis</option>
                        <option value="Diterima" <?= $filter_status === 'Diterima' ? 'selected' : '' ?>>Diterima (Lolos)</option>
                        <option value="Ditolak" <?= $filter_status === 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
                    </select>
                </div>
                <?php endif; ?>

                <div class="filter-item" style="flex: 1.5; min-width: 200px;">
                    <label class="filter-label">Cari Nama</label>
                    <div class="flex gap-2">
                        <input type="text" id="searchInput" value="<?= htmlspecialchars($search) ?>" class="filter-input w-full" placeholder="Ketik pencarian...">
                        <button type="button" id="btnResetFilter" class="btn-reset-filter" onclick="window.location.href='mahasiswa.php?page=mahasiswa&jenis=<?= $jenis ?>'">Reset</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== TABEL + DETAIL PANEL ==================== -->
        <?php
            $allData = [];
            foreach ($filtered_list as $item) {
                $allData[] = [
                    'id'       => $item['id'],
                    'nama'     => $item['nama'],
                    'nim'      => $item['nim'] ?? '-',
                    'prodi'       => $item['prodi'] ?? '-',
                    'golongan'    => $item['golongan'] ?? 'Umum',
                    'tahun'       => $item['tahun'] ?? '-',
                    'status'      => $item['status'] ?? 'Menunggu',
                    'total_bobot' => $item['total_bobot'] ?? null,
                    'detail'   => $item['detail'] ?? [],
                    'nilai'    => isset($nilai_raw_mhs[$item['id']]) ? $nilai_raw_mhs[$item['id']] : [],
                ];
            }
            $kriteriaLabels = [];
            foreach ($kriteria_db as $idK => $krit) {
                $kriteriaLabels[$idK] = [
                    'nama'  => $krit['nama_kriteria'],
                    'bobot' => $krit['wj'] ?? 0,
                    'subs'  => $krit['subs'] ?? [],
                ];
            }
        ?>

        <style>
            /* ---- Transition wrapper ---- */
            #mainPanelWrapper {
                display: flex;
                gap: 1rem;
                align-items: flex-start;
                width: 100%;
            }

            /* ---- Table panel ---- */
            #tablePanel {
                flex: 1 1 100%;
                min-width: 0;
                transition: flex 0.4s cubic-bezier(0.4,0,0.2,1), max-width 0.4s cubic-bezier(0.4,0,0.2,1);
                max-width: 100%;
                overflow: hidden;
            }
            #mainPanelWrapper.detail-open #tablePanel {
                flex: 0 0 300px;
                max-width: 300px;
            }

            /* ---- Detail panel ---- */
            #detailPanel {
                flex: 0 0 0px;
                max-width: 0;
                overflow: hidden;
                transition: flex 0.4s cubic-bezier(0.4,0,0.2,1), max-width 0.4s cubic-bezier(0.4,0,0.2,1), opacity 0.35s ease;
                opacity: 0;
            }
            #mainPanelWrapper.detail-open #detailPanel {
                flex: 1 1 0;
                max-width: 9999px;
                opacity: 1;
            }

            /* ---- Compact table columns when panel is open ---- */
            #mainPanelWrapper.detail-open .col-hide { display: none; }
            #mainPanelWrapper.detail-open .col-prodi-text { display: none; }

            /* ---- Data row hover ---- */
            .data-row { cursor: pointer; transition: background 0.15s; }
            .data-row:hover td { background: #f5f3ff; }
            .data-row.active-row td { background: #ede9fe; }
        </style>

        <div id="mainPanelWrapper">

            <!-- TABLE PANEL (full width by default) -->
            <div id="tablePanel">
                <div class="table-card w-full">
                    <div class="table-card-header">
                        <div class="table-card-title">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span><?= $jenis === 'mahasiswa' ? 'Daftar Mahasiswa Resmi (Lolos)' : 'Daftar Calon Mahasiswa' ?></span>
                            <span id="counterData" class="table-badge-counter"><?= $totalData ?> Data</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Tampilkan:</span>
                            <select id="limitSelect" class="bg-white border border-gray-200 text-gray-700 text-xs rounded-md focus:ring-purple-500 focus:border-purple-500 p-1.5 shadow-sm font-medium">
                                <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
                                <option value="20" <?= $limit == 20 ? 'selected' : '' ?>>20</option>
                                <option value="30" <?= $limit == 30 ? 'selected' : '' ?>>30</option>
                                <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                            </select>
                        </div>
                    </div>

                    <div class="table-scroll-container">
                        <table class="custom-table" id="tableMahasiswa">
                            <thead>
                                <tr>
                                    <th class="w-10 text-center">NO</th>
                                    <th>NAMA MAHASISWA</th>
                                    <th class="col-hide">PROGRAM STUDI</th>
                                    <th class="w-20 text-center col-hide">TAHUN</th>
                                    <th class="w-28 text-center">STATUS</th>
                                    <th class="w-24 text-center">AKSI</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <?php
                                $no = $offset + 1;
                                if (!empty($paginated_list)):
                                    foreach ($paginated_list as $item):
                                        $id     = $item['id'];
                                        $status = $item['status'] ?? 'Menunggu';
                                        if ($status === 'Diterima')     $status_class = 'diterima';
                                        elseif ($status === 'Ditolak')  $status_class = 'tidak-diterima';
                                        else                            $status_class = 'bg-blue-100 text-blue-700 border border-blue-200';
                                        $golongan = $item['golongan'] ?? 'Umum';
                                ?>
                                <tr class="data-row" data-id="<?= $id ?>" onclick="showDetail(<?= $id ?>)">
                                    <td class="text-center font-semibold text-gray-400 text-xs"><?= $no++ ?></td>
                                    <td>
                                        <div class="font-semibold text-gray-900 leading-snug text-sm"><?= htmlspecialchars($item['nama']) ?></div>
                                        <?php if (isset($item['total_bobot']) && $item['total_bobot'] !== null): ?>
                                            <div class="text-xs text-purple-600 font-medium mt-0.5">Total Skor: <?= number_format((float)$item['total_bobot'], 4) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="col-hide"><span class="text-xs font-medium text-purple-700"><?= htmlspecialchars($item['prodi']) ?></span></td>
                                    <td class="text-center col-hide font-medium text-gray-600 text-sm"><?= htmlspecialchars($item['tahun']) ?></td>
                                    <td class="text-center"><span class="status-pill <?= $status_class ?>"><?= htmlspecialchars($status) ?></span></td>
                                    <td class="text-center" onclick="event.stopPropagation()">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <a href="mahasiswa.php?page=mahasiswa&aksi=edit&id=<?= $id ?>&jenis=<?= $jenis ?>" class="p-1.5 text-purple-600 bg-purple-50 rounded hover:bg-purple-100 transition-colors" title="Edit">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            </a>
                                            <a href="mahasiswa.php?page=mahasiswa&aksi=hapus&id=<?= $id ?>&jenis=<?= $jenis ?>" onclick="return confirm('Yakin hapus data ini?');" class="p-1.5 text-red-500 bg-red-50 rounded hover:bg-red-100 transition-colors" title="Hapus">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr><td colspan="7" class="px-6 py-12 text-center text-gray-400 italic">Data tidak ditemukan.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div id="paginationContainer" class="p-4 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4 bg-white rounded-b-xl">
                        <span class="text-sm text-gray-500 font-medium">
                            Menampilkan <span class="font-bold text-gray-900"><?= $totalData > 0 ? $offset + 1 : 0 ?></span>
                            &ndash; <span class="font-bold text-gray-900"><?= min($offset + $limit, $totalData) ?></span>
                            dari <span class="font-bold text-gray-900"><?= $totalData ?></span> data
                        </span>
                        <?php if ($totalPages > 1): ?>
                        <div class="flex items-center space-x-1">
                            <a href="mahasiswa.php?page=mahasiswa&jenis=<?= $jenis ?>&hal=<?= max(1,$halaman-1) ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>&prodi=<?= urlencode($filter_prodi) ?>&tahun=<?= urlencode($filter_tahun) ?>&status=<?= urlencode($filter_status) ?>" class="px-3 py-1.5 text-sm font-medium text-gray-500 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 <?= $halaman<=1?'pointer-events-none opacity-50':'' ?>">Sebelumnya</a>
                            <?php for($i=1;$i<=$totalPages;$i++): ?>
                            <a href="mahasiswa.php?page=mahasiswa&jenis=<?= $jenis ?>&hal=<?= $i ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>&prodi=<?= urlencode($filter_prodi) ?>&tahun=<?= urlencode($filter_tahun) ?>&status=<?= urlencode($filter_status) ?>" class="px-3 py-1.5 text-sm font-medium border rounded-lg transition-colors <?= $halaman==$i?'bg-purple-50 text-purple-700 border-purple-200':'bg-white text-gray-500 border-gray-200 hover:bg-gray-50' ?>"><?= $i ?></a>
                            <?php endfor; ?>
                            <a href="mahasiswa.php?page=mahasiswa&jenis=<?= $jenis ?>&hal=<?= min($totalPages,$halaman+1) ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>&prodi=<?= urlencode($filter_prodi) ?>&tahun=<?= urlencode($filter_tahun) ?>&status=<?= urlencode($filter_status) ?>" class="px-3 py-1.5 text-sm font-medium text-gray-500 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 <?= $halaman>=$totalPages?'pointer-events-none opacity-50':'' ?>">Selanjutnya</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div><!-- /table-card -->
            </div><!-- /tablePanel -->

            <!-- DETAIL PANEL (hidden, slides in from right) -->
            <div id="detailPanel">
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" style="min-width:500px;">
                    <!-- Header -->
                    <div class="flex items-start justify-between px-5 py-3.5 border-b border-gray-100 bg-gray-50/70">
                        <div class="flex items-center gap-3">
                            <div id="detailAvatar" class="w-10 h-10 rounded-full bg-purple-600 text-white font-bold text-base flex items-center justify-center shrink-0"></div>
                            <div>
                                <div id="detailNama" class="font-bold text-gray-900 text-sm leading-snug"></div>
                                <div id="detailSubheader" class="text-[11px] text-gray-500 mt-0.5"></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span id="detailStatusPill" class="text-xs font-bold px-2.5 py-0.5 rounded-full"></span>
                            <a id="detailEditBtn" href="#" class="text-xs font-medium px-2.5 py-1.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                Edit
                            </a>
                            <button onclick="closeDetail()" class="text-gray-400 hover:text-gray-600 p-1 rounded transition-colors" title="Tutup">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="p-4 grid grid-cols-1 xl:grid-cols-2 gap-3">
                        <!-- Identitas -->
                        <div class="border border-gray-100 rounded-xl p-4 bg-white">
                            <div class="flex items-center gap-1.5 text-xs font-bold text-gray-700 mb-3 pb-2 border-b border-gray-100">
                                <svg class="w-3.5 h-3.5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Identitas Lengkap
                            </div>
                            <div class="grid grid-cols-2 gap-x-3 gap-y-2.5 text-xs">
                                <div><div class="text-gray-400 text-[10px] font-semibold uppercase tracking-wider mb-0.5">Total Skor</div><div id="dNim" class="font-mono font-semibold text-purple-700 text-[11px]"></div></div>
                                <div><div class="text-gray-400 text-[10px] font-semibold uppercase tracking-wider mb-0.5">NIK (KTP/KK)</div><div id="dNik" class="font-mono font-semibold text-gray-800 text-[11px]"></div></div>
                                <div><div class="text-gray-400 text-[10px] font-semibold uppercase tracking-wider mb-0.5">Jenis Kelamin</div><div id="dJK" class="text-gray-700"></div></div>
                                <div><div class="text-gray-400 text-[10px] font-semibold uppercase tracking-wider mb-0.5">Tempat, Tgl Lahir</div><div id="dTTL" class="text-gray-700"></div></div>
                                <div><div class="text-gray-400 text-[10px] font-semibold uppercase tracking-wider mb-0.5">Asal Sekolah</div><div id="dSekolah" class="text-gray-700"></div></div>
                                <div><div class="text-gray-400 text-[10px] font-semibold uppercase tracking-wider mb-0.5">Program Studi</div><div id="dProdi" class="text-purple-700 font-semibold"></div></div>
                                <div><div class="text-gray-400 text-[10px] font-semibold uppercase tracking-wider mb-0.5">No. HP</div><div id="dHp" class="text-gray-700"></div></div>
                                <div><div class="text-gray-400 text-[10px] font-semibold uppercase tracking-wider mb-0.5">Email</div><div id="dEmail" class="text-blue-600 break-all text-[10px]"></div></div>
                                <div class="col-span-2"><div class="text-gray-400 text-[10px] font-semibold uppercase tracking-wider mb-0.5">Alamat</div><div id="dAlamat" class="text-gray-700"></div></div>
                            </div>
                        </div>

                        <!-- Rincian Nilai -->
                        <div class="border border-gray-100 rounded-xl p-4 bg-white">
                            <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-100">
                                <div class="flex items-center gap-1.5 text-xs font-bold text-gray-700">
                                    <svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                    Rincian Nilai
                                </div>
                                <span id="dRanking" class="text-[10px] font-bold text-green-700 bg-green-100 px-2 py-0.5 rounded-full hidden"></span>
                            </div>
                            <table class="w-full text-xs">
                                <thead>
                                    <tr class="text-gray-400 text-[10px] uppercase tracking-wider border-b border-gray-100">
                                        <th class="text-left pb-1.5 font-semibold">Kriteria</th>
                                        <th class="text-center pb-1.5 font-semibold w-12">Bobot</th>
                                        <th class="text-center pb-1.5 font-semibold w-12">Nilai</th>
                                        <th class="text-left pb-1.5 font-semibold">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody id="dNilaiBody">
                                    <tr><td colspan="4" class="text-center text-gray-400 italic py-5 text-xs">Memuat data nilai...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-4 pb-3 flex items-center gap-2 border-t border-gray-100 pt-3">
                        <a id="detailDeleteBtn" href="#" onclick="return confirm('Yakin ingin menghapus data ini?');" class="text-xs font-medium px-3 py-1.5 bg-red-50 text-red-600 border border-red-200 rounded-lg hover:bg-red-100 transition-colors flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Hapus Data
                        </a>
                    </div>
                </div>
            </div><!-- /detailPanel -->

        </div><!-- /mainPanelWrapper -->
        <script>
        (function () {
            var jenisVal     = "<?= $jenis ?>";
            var allData      = <?= json_encode(array_values($allData), JSON_UNESCAPED_UNICODE) ?>;
            var kriteriaInfo = <?= json_encode($kriteriaLabels, JSON_UNESCAPED_UNICODE) ?>;

            var wrapper      = document.getElementById('mainPanelWrapper');
            var tableBody    = document.getElementById('tableBody');
            var pagCont      = document.getElementById('paginationContainer');
            var counterData  = document.getElementById('counterData');
            var searchInput  = document.getElementById('searchInput');
            var limitSelect  = document.getElementById('limitSelect');
            var prodiSelect  = document.getElementById('filterProdi');
            var tahunSelect  = document.getElementById('filterTahun');
            var statusSelect = document.getElementById('filterStatus');
            var fetchTimeout = null;

            /* ---- attach klik baris tabel ---- */
            function attachRowClicks() {
                document.querySelectorAll('#tableBody .data-row').forEach(function (row) {
                    row.onclick = function () { showDetail(parseInt(this.dataset.id, 10)); };
                });
            }

            /* ---- live search/filter via fetch ---- */
            function fetchLive() {
                if (!tableBody) return;
                var s  = searchInput  ? encodeURIComponent(searchInput.value)  : '';
                var l  = limitSelect  ? limitSelect.value  : 10;
                var p  = prodiSelect  ? encodeURIComponent(prodiSelect.value)  : '';
                var t  = tahunSelect  ? encodeURIComponent(tahunSelect.value)  : '';
                var st = statusSelect ? encodeURIComponent(statusSelect.value) : '';

                tableBody.innerHTML =
                    '<tr><td colspan="7" class="px-6 py-12 text-center text-gray-400 italic">'
                    + '<svg class="animate-spin w-6 h-6 mx-auto text-purple-400 mb-2" fill="none" viewBox="0 0 24 24">'
                    + '<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>'
                    + '<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>'
                    + '</svg>Memuat data...</td></tr>';

                fetch('mahasiswa.php?page=mahasiswa&jenis=' + jenisVal
                    + '&search=' + s + '&limit=' + l
                    + '&prodi=' + p + '&tahun=' + t + '&status=' + st)
                    .then(function (r) { return r.text(); })
                    .then(function (html) {
                        var doc = new DOMParser().parseFromString(html, 'text/html');
                        var nb  = doc.getElementById('tableBody');
                        if (nb) tableBody.innerHTML = nb.innerHTML;
                        var np  = doc.getElementById('paginationContainer');
                        if (np && pagCont) pagCont.innerHTML = np.innerHTML;
                        var nc  = doc.getElementById('counterData');
                        if (nc && counterData) counterData.innerHTML = nc.innerHTML;
                        attachRowClicks();
                    });
            }

            /* ---- show detail + animasi slide ---- */
            function showDetail(id) {
                var item = null;
                for (var i = 0; i < allData.length; i++) {
                    if (allData[i].id == id) { item = allData[i]; break; }
                }
                if (!item || !wrapper) return;

                document.querySelectorAll('#tableBody .data-row').forEach(function (el) {
                    el.classList.toggle('active-row', parseInt(el.dataset.id, 10) === id);
                });

                populateDetail(item);

                if (!wrapper.classList.contains('detail-open')) {
                    wrapper.classList.add('detail-open');
                }
            }

            function populateDetail(item) {
                var words = item.nama.trim().split(' ');
                var initials = (words.map(function (w) { return w[0] || ''; }).slice(0, 2).join('')).toUpperCase();
                var setT = function (elId, val) {
                    var e = document.getElementById(elId);
                    if (e) e.textContent = val || '-';
                };

                setT('detailAvatar',    initials);
                setT('detailNama',      item.nama);
                setT('detailSubheader', item.prodi + (item.total_bobot !== null && item.total_bobot !== undefined ? ' \u2022 Total Skor: ' + parseFloat(item.total_bobot).toFixed(4) : ''));

                var pill = document.getElementById('detailStatusPill');
                if (pill) {
                    pill.textContent = item.status;
                    pill.className   = 'text-xs font-bold px-2.5 py-0.5 rounded-full '
                        + (item.status === 'Diterima' ? 'bg-green-100 text-green-700'
                        :  item.status === 'Ditolak'  ? 'bg-red-100 text-red-700'
                        :                               'bg-blue-100 text-blue-700');
                }

                var editBtn = document.getElementById('detailEditBtn');
                if (editBtn) editBtn.href = 'mahasiswa.php?page=mahasiswa&aksi=edit&id=' + item.id + '&jenis=' + jenisVal;
                var delBtn = document.getElementById('detailDeleteBtn');
                if (delBtn) delBtn.href  = 'mahasiswa.php?page=mahasiswa&aksi=hapus&id=' + item.id + '&jenis=' + jenisVal;

                var d = item.detail || {};
                setT('dNim',     (item.total_bobot !== null && item.total_bobot !== undefined) ? parseFloat(item.total_bobot).toFixed(4) : '-');
                setT('dNik',     d.nik);
                var jk = d.jenis_kelamin;
                setT('dJK',      jk === 'L' ? 'Laki-laki' : jk === 'P' ? 'Perempuan' : jk);
                setT('dTTL',     d.tempat_tanggal_lahir);
                setT('dSekolah', d.asal_sekolah);
                setT('dProdi',   item.prodi);
                setT('dHp',      d.no_hp);
                setT('dEmail',   d.email);
                setT('dAlamat',  d.alamat);

                /* -- baris nilai -- */
                var nilaiMap = item.nilai || {};
                var rows = [];
                var cNo  = 1;
                for (var idK in kriteriaInfo) {
                    var krit     = kriteriaInfo[idK];
                    var entry    = nilaiMap[idK] || {};
                    var idSub    = entry.id_sub;
                    var nilaiVal = entry.nilai;
                    var subLabel = '-';
                    if (idSub && krit.subs) {
                        for (var si = 0; si < krit.subs.length; si++) {
                            if (krit.subs[si].id_sub == idSub) { subLabel = krit.subs[si].nama_sub; break; }
                        }
                    }
                    var bobot = Math.round((krit.bobot || 0) * 100);
                    var nd    = (nilaiVal !== undefined && nilaiVal !== null) ? nilaiVal : '-';
                    var nc    = nilaiVal >= 85 ? 'font-bold text-green-700 bg-green-50 px-1.5 py-0.5 rounded'
                              : nilaiVal >= 70 ? 'font-semibold text-blue-700' : 'text-gray-600';
                    rows.push(
                        '<tr class="border-t border-gray-50">'
                        + '<td class="py-1.5 pr-2 text-gray-700"><span class="text-purple-600 font-bold mr-0.5">C' + cNo + '</span>' + krit.nama + '</td>'
                        + '<td class="text-center py-1.5 text-gray-500">' + bobot + '%</td>'
                        + '<td class="text-center py-1.5"><span class="' + nc + '">' + nd + '</span></td>'
                        + '<td class="py-1.5 text-gray-500 text-[10px]">' + subLabel + '</td>'
                        + '</tr>'
                    );
                    cNo++;
                }
                var dNilaiBody = document.getElementById('dNilaiBody');
                if (dNilaiBody) dNilaiBody.innerHTML = rows.length
                    ? rows.join('')
                    : '<tr><td colspan="4" class="text-center text-gray-400 italic py-5">Belum ada data nilai.</td></tr>';

                /* -- ranking -- */
                var withNilai = allData.filter(function (x) { return x.nilai && Object.keys(x.nilai).length > 0; });
                withNilai.sort(function (a, b) { return calcScore(b.nilai) - calcScore(a.nilai); });
                var rank = -1;
                for (var ri = 0; ri < withNilai.length; ri++) {
                    if (withNilai[ri].id == item.id) { rank = ri + 1; break; }
                }
                var dRank = document.getElementById('dRanking');
                if (dRank) {
                    if (rank > 0) { dRank.textContent = 'Ranking #' + rank; dRank.classList.remove('hidden'); }
                    else           { dRank.classList.add('hidden'); }
                }
            }

            function calcScore(nm) {
                var s = 1;
                for (var idK in kriteriaInfo) {
                    var krit = kriteriaInfo[idK];
                    var val  = (nm[idK] && nm[idK].nilai) ? nm[idK].nilai : 1;
                    s *= Math.pow(val > 0 ? val : 1, krit.bobot || 0);
                }
                return s;
            }

            function closeDetail() {
                if (!wrapper) return;
                wrapper.classList.remove('detail-open');
                document.querySelectorAll('#tableBody .data-row').forEach(function (el) {
                    el.classList.remove('active-row');
                });
            }

            /* expose ke HTML onclick */
            window.showDetail  = showDetail;
            window.closeDetail = closeDetail;

            /* pasang event listener filter */
            if (searchInput)  searchInput.addEventListener('input',  function () { clearTimeout(fetchTimeout); fetchTimeout = setTimeout(fetchLive, 300); });
            if (limitSelect)  limitSelect.addEventListener('change', fetchLive);
            if (prodiSelect)  prodiSelect.addEventListener('change', fetchLive);
            if (tahunSelect)  tahunSelect.addEventListener('change', fetchLive);
            if (statusSelect) statusSelect.addEventListener('change', fetchLive);

            /* pasang klik baris awal */
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', attachRowClicks);
            } else {
                attachRowClicks();
            }

        }());
        </script>
    <?php endif; ?>
</main>
</div> 
</body>
</html>
