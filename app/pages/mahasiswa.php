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
            $golongan_val = $data['golongan'] ?? 'Beasiswa';
            $editNilai = $nilai_raw_mhs[$id] ?? [];
            $penghasilan_val = $data['penghasilan_orangtua'] ?? '';
            $pekerjaan_ayah_val = $data['pekerjaan_ayah'] ?? '';
            $pekerjaan_ibu_val = $data['pekerjaan_ibu'] ?? '';
            $penghasilan_ayah_val = $data['penghasilan_ayah'] ?? '';
            $penghasilan_ibu_val = $data['penghasilan_ibu'] ?? '';
            $aset_kendaraan_val = $data['aset_kendaraan'] ?? '';
            $aset_bangunan_val = $data['aset_bangunan'] ?? '';
            $uang_makan_val = $data['uang_makan_harian'] ?? '';
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
                <div class="flex items-center justify-between w-full relative">
                    <div class="flex flex-col items-center relative z-10 cursor-pointer" onclick="goToStep(1)">
                        <div id="step-node-1" class="w-12 h-12 rounded-full border-4 border-purple-600 bg-purple-600 text-white font-bold text-base flex items-center justify-center shadow-md transition-all duration-300">1</div>
                        <span id="step-label-1" class="text-xs font-bold text-purple-700 mt-2 text-center transition-colors">Data Diri</span>
                    </div>
                    
                    <div class="flex-1 h-1 mx-2 sm:mx-4 bg-gray-200 rounded relative overflow-hidden"><div id="step-line-1" class="h-full bg-purple-600 w-0 transition-all duration-500"></div></div>
                    
                    <div class="flex flex-col items-center relative z-10 cursor-pointer" onclick="validateAndGoStep(2)">
                        <div id="step-node-2" class="w-12 h-12 rounded-full border-4 border-gray-300 bg-white text-gray-500 font-bold text-base flex items-center justify-center shadow-sm transition-all duration-300">2</div>
                        <span id="step-label-2" class="text-xs font-semibold text-gray-400 mt-2 text-center transition-colors">Input Nilai</span>
                    </div>
                    
                    <div id="tracker-line-3" class="flex-1 h-1 mx-2 sm:mx-4 bg-gray-200 rounded relative overflow-hidden"><div id="step-line-2" class="h-full bg-purple-600 w-0 transition-all duration-500"></div></div>
                    <div id="tracker-node-3" class="flex flex-col items-center relative z-10 cursor-pointer" onclick="validateAndGoStep(3)">
                        <div id="step-node-3" class="w-12 h-12 rounded-full border-4 border-gray-300 bg-white text-gray-500 font-bold text-base flex items-center justify-center shadow-sm transition-all duration-300">3</div>
                        <span id="step-label-3" class="text-xs font-semibold text-gray-400 mt-2 text-center transition-colors">Pengajuan Beasiswa</span>
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
                    <input type="hidden" name="nim" value="<?= htmlspecialchars($data['nim'] ?? '') ?>">
                    <input type="hidden" id="golonganInput" name="golongan" value="<?= htmlspecialchars($golongan_val) ?>">

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
                                ?>
                                    <div class="p-4 bg-white border border-gray-200 rounded-xl shadow-sm">
                                        <h4 class="font-bold text-gray-800 text-sm mb-3"><?= htmlspecialchars($krit['nama_kriteria']) ?> (C<?= $cNo ?>)</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="filter-item">
                                                <select name="kriteria_sub[<?= $idK ?>]" id="sub_select_<?= $idK ?>" class="filter-select w-full" onchange="updateSubScore(<?= $idK ?>)">
                                                    <option value="">-- Pilih Nilai --</option>
                                                    <?php foreach ($krit['subs'] as $sub): ?>
                                                        <option value="<?= $sub['id_sub'] ?>" data-score="<?= $sub['nilai'] ?>" <?= ($curSubId == $sub['id_sub']) ? 'selected' : '' ?>><?= htmlspecialchars($sub['nama_sub']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="filter-item">
                                                <input type="number" step="0.01" name="kriteria_nilai[<?= $idK ?>]" id="sub_nilai_<?= $idK ?>" value="<?= htmlspecialchars((string)$curNilai) ?>" class="filter-input w-full bg-gray-100 font-mono font-semibold" readonly placeholder="Skor Nilai (Otomatis)">
                                            </div>
                                        </div>
                                    </div>
                                <?php $cNo++; endforeach; ?>
                            </div>
                        </div>

                        <div class="flex justify-between items-center pt-2 gap-3 flex-wrap">
                            <button type="button" onclick="goToStep(1)" class="px-6 py-3 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 transition-colors">Kembali ke Data Diri</button>
                            <div class="flex items-center gap-3">
                                <button type="submit" name="<?= $isEdit ? 'simpan_edit' : 'simpan_tambah' ?>" onclick="setGolonganUmumDirect()" class="px-6 py-3 bg-white border-2 border-purple-600 text-purple-700 font-bold rounded-lg hover:bg-purple-50 transition-all flex items-center gap-2 shadow-sm cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Simpan (Jalur Reguler / Tanpa Beasiswa)</span>
                                </button>
                                <button type="button" id="btnLanjutKe3" onclick="validateAndGoStep(3)" class="px-7 py-3 bg-gradient-to-r from-amber-500 to-amber-600 text-white font-bold rounded-lg shadow-md hover:opacity-90 transition-all flex items-center gap-2 cursor-pointer">
                                    <span>Lanjut ke Proses 3: Pengajuan Beasiswa</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="section-step-3" class="space-y-6 hidden">
                        <div class="p-6 border border-amber-300 rounded-xl bg-amber-50/20">
                            <div class="flex items-center justify-between pb-4 border-b border-amber-200 mb-5">
                                <h3 class="text-sm font-bold uppercase tracking-wider text-amber-900 flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-amber-500 text-white text-xs font-bold flex items-center justify-center">3</span> 
                                    PROSES 3: PENGAJUAN BEASISWA & DATA SOSIAL EKONOMI
                                </h3>
                                <span id="badgeStatusBeasiswa" class="text-xs font-semibold <?= $golongan_val === "Umum" ? "bg-gray-100 text-gray-600 border-gray-200" : "bg-amber-100 text-amber-800 border-amber-200" ?> px-3 py-1 rounded-full border"><?= $golongan_val === "Umum" ? "Jalur Reguler / Tanpa Beasiswa (Opsional)" : "Kriteria Penilaian Ekonomi" ?></span>
                            </div>

                            <!-- Opsi Jalur Pengajuan Beasiswa -->
                            <div class="p-4 bg-white border border-amber-200 rounded-xl mb-6 shadow-sm">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div>
                                        <h4 class="font-bold text-gray-800 text-sm">Status Pengajuan Jalur Beasiswa</h4>
                                        <p class="text-xs text-gray-500 mt-0.5">Pilih apakah calon mahasiswa ini mendaftar melalui jalur seleksi beasiswa atau reguler/umum.</p>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="radio" name="ajukan_beasiswa" value="Beasiswa" <?= $golongan_val !== 'Umum' ? 'checked' : '' ?> onchange="updateGolonganFromRadio(this.value)" class="text-amber-600 focus:ring-amber-500 w-4 h-4">
                                            <span class="ml-2 text-sm font-bold text-amber-900">Ajukan Beasiswa</span>
                                        </label>
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="radio" name="ajukan_beasiswa" value="Umum" <?= $golongan_val === 'Umum' ? 'checked' : '' ?> onchange="updateGolonganFromRadio(this.value)" class="text-purple-600 focus:ring-purple-500 w-4 h-4">
                                            <span class="ml-2 text-sm font-medium text-gray-700">Jalur Reguler (Umum)</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Formulir Rincian Beasiswa -->
                            <div id="containerDetailBeasiswa" class="space-y-6 <?= $golongan_val === "Umum" ? "opacity-40 pointer-events-none" : "" ?>">
                                <!-- 1. Pekerjaan & Penghasilan Orang Tua (Ayah dan Ibu) -->
                                <div class="p-5 bg-white border border-gray-200 rounded-xl shadow-sm">
                                    <div class="flex items-center gap-2 pb-3 mb-4 border-b border-gray-100 text-gray-800 font-bold text-sm">
                                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        <span>1. Pekerjaan & Penghasilan Orang Tua (Ayah dan Ibu)</span>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                                        <!-- Ayah -->
                                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-200/80 space-y-3">
                                            <div class="font-bold text-xs uppercase tracking-wider text-purple-900 flex items-center gap-1.5">
                                                <span class="w-2 h-2 rounded-full bg-purple-600"></span> Data Ayah
                                            </div>
                                            <div class="filter-item">
                                                <label class="filter-label">Pekerjaan Ayah</label>
                                                <select name="pekerjaan_ayah" class="filter-select w-full bg-white">
                                                    <option value="">-- Pilih Pekerjaan Ayah --</option>
                                                    <?php 
                                                        $jobs_ayah = ['PNS / TNI / Polri', 'Pegawai BUMN / BUMD', 'Karyawan Swasta', 'Wiraswasta / Pedagang', 'Petani / Peternak / Nelayan', 'Buruh Harian Lepas', 'Pensiunan', 'Tidak Bekerja / Meninggal Dunia', 'Lainnya'];
                                                        foreach ($jobs_ayah as $job):
                                                    ?>
                                                        <option value="<?= htmlspecialchars($job) ?>" <?= $pekerjaan_ayah_val === $job ? 'selected' : '' ?>><?= htmlspecialchars($job) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="filter-item">
                                                <label class="filter-label">Gaji / Penghasilan Ayah per Bulan</label>
                                                <select name="penghasilan_ayah" id="penghasilanAyah" onchange="autoCalculatePenghasilan()" class="filter-select w-full bg-white">
                                                    <option value="">-- Pilih Penghasilan Ayah --</option>
                                                    <option value="Tidak Ada" <?= $penghasilan_ayah_val === 'Tidak Ada' ? 'selected' : '' ?>>Tidak Ada / Rp 0</option>
                                                    <option value="< Rp 1.500.000" <?= strpos($penghasilan_ayah_val, '< Rp 1.500.000') !== false ? 'selected' : '' ?>>&lt; Rp 1.500.000 / bulan</option>
                                                    <option value="Rp 1.500.000 - Rp 3.000.000" <?= strpos($penghasilan_ayah_val, '1.500.000 - Rp 3.000.000') !== false ? 'selected' : '' ?>>Rp 1.500.000 - Rp 3.000.000 / bulan</option>
                                                    <option value="Rp 3.000.000 - Rp 5.000.000" <?= strpos($penghasilan_ayah_val, '3.000.000 - Rp 5.000.000') !== false ? 'selected' : '' ?>>Rp 3.000.000 - Rp 5.000.000 / bulan</option>
                                                    <option value="> Rp 5.000.000" <?= strpos($penghasilan_ayah_val, '> Rp 5.000.000') !== false ? 'selected' : '' ?>>&gt; Rp 5.000.000 / bulan</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Ibu -->
                                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-200/80 space-y-3">
                                            <div class="font-bold text-xs uppercase tracking-wider text-pink-900 flex items-center gap-1.5">
                                                <span class="w-2 h-2 rounded-full bg-pink-500"></span> Data Ibu
                                            </div>
                                            <div class="filter-item">
                                                <label class="filter-label">Pekerjaan Ibu</label>
                                                <select name="pekerjaan_ibu" class="filter-select w-full bg-white">
                                                    <option value="">-- Pilih Pekerjaan Ibu --</option>
                                                    <?php 
                                                        $jobs_ibu = ['Ibu Rumah Tangga (IRT)', 'PNS / TNI / Polri', 'Pegawai BUMN / BUMD', 'Karyawan Swasta', 'Wiraswasta / Pedagang', 'Petani / Peternak / Nelayan', 'Buruh Harian Lepas', 'Pensiunan', 'Tidak Bekerja / Meninggal Dunia', 'Lainnya'];
                                                        foreach ($jobs_ibu as $job):
                                                    ?>
                                                        <option value="<?= htmlspecialchars($job) ?>" <?= $pekerjaan_ibu_val === $job ? 'selected' : '' ?>><?= htmlspecialchars($job) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="filter-item">
                                                <label class="filter-label">Gaji / Penghasilan Ibu per Bulan</label>
                                                <select name="penghasilan_ibu" id="penghasilanIbu" onchange="autoCalculatePenghasilan()" class="filter-select w-full bg-white">
                                                    <option value="">-- Pilih Penghasilan Ibu --</option>
                                                    <option value="Tidak Ada" <?= $penghasilan_ibu_val === 'Tidak Ada' ? 'selected' : '' ?>>Tidak Ada / Rp 0 (IRT)</option>
                                                    <option value="< Rp 1.500.000" <?= strpos($penghasilan_ibu_val, '< Rp 1.500.000') !== false ? 'selected' : '' ?>>&lt; Rp 1.500.000 / bulan</option>
                                                    <option value="Rp 1.500.000 - Rp 3.000.000" <?= strpos($penghasilan_ibu_val, '1.500.000 - Rp 3.000.000') !== false ? 'selected' : '' ?>>Rp 1.500.000 - Rp 3.000.000 / bulan</option>
                                                    <option value="Rp 3.000.000 - Rp 5.000.000" <?= strpos($penghasilan_ibu_val, '3.000.000 - Rp 5.000.000') !== false ? 'selected' : '' ?>>Rp 3.000.000 - Rp 5.000.000 / bulan</option>
                                                    <option value="> Rp 5.000.000" <?= strpos($penghasilan_ibu_val, '> Rp 5.000.000') !== false ? 'selected' : '' ?>>&gt; Rp 5.000.000 / bulan</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Total Penghasilan Gabungan (Untuk Naive Bayes) -->
                                    <div class="filter-item">
                                        <label class="filter-label">Total Penghasilan Orang Tua Gabungan (Dasar Analisis Klasifikasi Naive Bayes)</label>
                                        <select name="penghasilan_orangtua" id="penghasilanSelect" class="filter-select w-full bg-white">
                                            <option value="">-- Pilih Kisaran Total Penghasilan Gabungan --</option>
                                            <option value="< Rp 1.500.000 / bulan" <?= strpos($penghasilan_val, '1.500.000') !== false ? 'selected' : '' ?>>&lt; Rp 1.500.000 / bulan (Rendah / Prioritas Beasiswa)</option>
                                            <option value="Rp 1.500.000 - Rp 3.000.000 / bulan" <?= strpos($penghasilan_val, '3.000.000') !== false ? 'selected' : '' ?>>Rp 1.500.000 - Rp 3.000.000 / bulan (Sedang / Layak)</option>
                                            <option value="Rp 3.000.000 - Rp 5.000.000 / bulan" <?= strpos($penghasilan_val, '5.000.000') !== false ? 'selected' : '' ?>>Rp 3.000.000 - Rp 5.000.000 / bulan (Menengah)</option>
                                            <option value="> Rp 5.000.000 / bulan" <?= strpos($penghasilan_val, '> Rp 5.000.000') !== false ? 'selected' : '' ?>>&gt; Rp 5.000.000 / bulan (Mampu)</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- 2. Aset Keluarga -->
                                <div class="p-5 bg-white border border-gray-200 rounded-xl shadow-sm">
                                    <div class="flex items-center gap-2 pb-3 mb-4 border-b border-gray-100 text-gray-800 font-bold text-sm">
                                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                        <span>2. Aset Keluarga (Kendaraan & Bangunan Tempat Tinggal)</span>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="filter-item">
                                            <label class="filter-label">Kepemilikan Kendaraan</label>
                                            <select name="aset_kendaraan" class="filter-select w-full bg-white">
                                                <option value="">-- Pilih Kepemilikan Kendaraan --</option>
                                                <?php 
                                                    $kendaraan_opts = [
                                                        'Tidak Memiliki Kendaraan',
                                                        '1 Unit Sepeda Motor',
                                                        '2 Unit Sepeda Motor atau Lebih',
                                                        '1 Unit Mobil',
                                                        'Mobil dan Sepeda Motor'
                                                    ];
                                                    foreach ($kendaraan_opts as $k_opt):
                                                ?>
                                                    <option value="<?= htmlspecialchars($k_opt) ?>" <?= $aset_kendaraan_val === $k_opt ? 'selected' : '' ?>><?= htmlspecialchars($k_opt) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="filter-item">
                                            <label class="filter-label">Bangunan Tempat Tinggal</label>
                                            <select name="aset_bangunan" class="filter-select w-full bg-white">
                                                <option value="">-- Pilih Status Tempat Tinggal --</option>
                                                <?php 
                                                    $bangunan_opts = [
                                                        'Menumpang / Tinggal Bersama Kerabat',
                                                        'Sewa / Kontrak Rumah',
                                                        'Rumah Sendiri (Sederhana / Non-Permanen)',
                                                        'Rumah Sendiri (Permanen)',
                                                        'Rumah Sendiri (Mewah)'
                                                    ];
                                                    foreach ($bangunan_opts as $b_opt):
                                                ?>
                                                    <option value="<?= htmlspecialchars($b_opt) ?>" <?= $aset_bangunan_val === $b_opt ? 'selected' : '' ?>><?= htmlspecialchars($b_opt) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- 3. Uang Makan Satu Keluarga per Hari -->
                                <div class="p-5 bg-white border border-gray-200 rounded-xl shadow-sm">
                                    <div class="flex items-center gap-2 pb-3 mb-4 border-b border-gray-100 text-gray-800 font-bold text-sm">
                                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>3. Uang Makan Satu Keluarga per Hari</span>
                                    </div>

                                    <div class="filter-item">
                                        <label class="filter-label">Estimasi Pengeluaran Uang Makan Keluarga (Per Hari)</label>
                                        <select name="uang_makan_harian" class="filter-select w-full bg-white">
                                            <option value="">-- Pilih Estimasi Uang Makan Harian --</option>
                                            <?php 
                                                $makan_opts = [
                                                    '< Rp 30.000 / hari',
                                                    'Rp 30.000 - Rp 50.000 / hari',
                                                    'Rp 50.000 - Rp 100.000 / hari',
                                                    '> Rp 100.000 / hari'
                                                ];
                                                foreach ($makan_opts as $m_opt):
                                            ?>
                                                <option value="<?= htmlspecialchars($m_opt) ?>" <?= $uang_makan_val === $m_opt ? 'selected' : '' ?>><?= htmlspecialchars($m_opt) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between pt-2">
                            <button type="button" onclick="goToStep(2)" class="px-6 py-3 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 transition-colors">Kembali ke Proses 2</button>
                            <button type="submit" name="<?= $isEdit ? 'simpan_edit' : 'simpan_tambah' ?>" class="px-8 py-3 bg-gradient-to-r from-amber-500 to-amber-600 text-white font-bold rounded-lg shadow-md hover:opacity-90 transition-all flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Simpan Pendaftaran Mahasiswa</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <script>
            function setGolonganUmumDirect() {
                const golInput = document.getElementById('golonganInput');
                if (golInput) golInput.value = 'Umum';
                const radioUmum = document.querySelector('input[name="ajukan_beasiswa"][value="Umum"]');
                if (radioUmum) radioUmum.checked = true;
            }

            function updateGolonganFromRadio(val) {
                const golInput = document.getElementById('golonganInput');
                if (golInput) golInput.value = val;

                const detailContainer = document.getElementById('containerDetailBeasiswa');
                const badgeStatus = document.getElementById('badgeStatusBeasiswa');
                if (detailContainer) {
                    if (val === 'Umum') {
                        detailContainer.classList.add('opacity-40', 'pointer-events-none');
                        if (badgeStatus) {
                            badgeStatus.textContent = 'Jalur Reguler / Tanpa Beasiswa (Data Tidak Wajib)';
                            badgeStatus.className = 'text-xs font-semibold bg-gray-100 text-gray-600 px-3 py-1 rounded-full border border-gray-200';
                        }
                    } else {
                        detailContainer.classList.remove('opacity-40', 'pointer-events-none');
                        if (badgeStatus) {
                            badgeStatus.textContent = 'Kriteria Penilaian Ekonomi Aktif';
                            badgeStatus.className = 'text-xs font-semibold bg-amber-100 text-amber-800 px-3 py-1 rounded-full border border-amber-200';
                        }
                    }
                }
            }

            function autoCalculatePenghasilan() {
                const ayah = document.getElementById('penghasilanAyah')?.value || '';
                const ibu = document.getElementById('penghasilanIbu')?.value || '';
                const totalSelect = document.getElementById('penghasilanSelect');
                if (!totalSelect) return;

                if (ayah.includes('> Rp 5.000.000') || ibu.includes('> Rp 5.000.000')) {
                    totalSelect.value = '> Rp 5.000.000 / bulan';
                } else if (ayah.includes('3.000.000') || ibu.includes('3.000.000')) {
                    totalSelect.value = 'Rp 3.000.000 - Rp 5.000.000 / bulan';
                } else if (ayah.includes('1.500.000') || ibu.includes('1.500.000')) {
                    totalSelect.value = 'Rp 1.500.000 - Rp 3.000.000 / bulan';
                } else if (ayah.includes('< Rp 1.500.000') || ibu.includes('< Rp 1.500.000')) {
                    totalSelect.value = '< Rp 1.500.000 / bulan';
                }
            }

            function updateSubScore(idK) {
                const select = document.getElementById('sub_select_' + idK);
                const input = document.getElementById('sub_nilai_' + idK);
                const selectedOption = select.options[select.selectedIndex];
                if(selectedOption && selectedOption.value !== "") {
                    input.value = selectedOption.getAttribute('data-score');
                } else {
                    input.value = "";
                }
            }

            function goToStep(step) {
                document.getElementById('section-step-1').classList.add('hidden');
                document.getElementById('section-step-2').classList.add('hidden');
                document.getElementById('section-step-3').classList.add('hidden');
                
                document.getElementById('section-step-' + step).classList.remove('hidden');

                for(let i=1; i<=3; i++) {
                    let node = document.getElementById('step-node-'+i);
                    let label = document.getElementById('step-label-'+i);
                    let line = document.getElementById('step-line-'+i);
                    
                    if(!node) continue;

                    if (i === step) {
                        const activeBg = (i === 3) ? 'bg-amber-500 border-amber-500' : 'bg-purple-600 border-purple-600';
                        const textCol = (i === 3) ? 'text-amber-700' : 'text-purple-700';
                        node.className = `w-12 h-12 rounded-full border-4 ${activeBg} text-white font-bold text-base flex items-center justify-center shadow-md transition-all duration-300`;
                        label.className = `text-xs font-bold ${textCol} mt-2 text-center transition-colors`;
                    } else if (i < step) {
                        node.className = "w-12 h-12 rounded-full border-4 border-purple-600 bg-white text-purple-600 font-bold text-base flex items-center justify-center shadow-sm transition-all duration-300";
                        label.className = "text-xs font-bold text-purple-700 mt-2 text-center transition-colors";
                    } else {
                        node.className = "w-12 h-12 rounded-full border-4 border-gray-300 bg-white text-gray-500 font-bold text-base flex items-center justify-center shadow-sm transition-all duration-300";
                        label.className = "text-xs font-semibold text-gray-400 mt-2 text-center transition-colors";
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
                        perbedaan.push(`• <strong>Jenis Kelamin:</strong> NIK menandakan <em>${nikGenderText}</em>, tetapi di form dipilih <em>${inputGenderText}</em>.`);
                    }
                    if (!isDayMatch) {
                        perbedaan.push(`• <strong>Hari/Tanggal:</strong> NIK menandakan tanggal <em>${nikRealDay}</em>, sedangkan di form dipilih tanggal <em>${inputDay}</em>.`);
                    }
                    if (!isMonthMatch) {
                        perbedaan.push(`• <strong>Bulan Lahir:</strong> NIK menandakan bulan <em>${nikMonth}</em>, sedangkan di form dipilih bulan <em>${inputMonth}</em>.`);
                    }
                    if (!isYearMatch) {
                        perbedaan.push(`• <strong>Tahun Lahir:</strong> NIK berakhiran tahun <em>${nikYY}</em>, sedangkan di form dipilih tahun <em>${inputYear}</em>.`);
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
                        tglMsg.textContent = "✓ Sesuai dengan 6 digit tanggal NIK";
                        tglMsg.classList.remove('hidden');
                    } else {
                        nikMsg.className = "text-xs mt-1 text-rose-500 font-semibold flex items-center gap-1";
                        nikMsg.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> ' + (res.title || 'Tidak sesuai dengan NIK');
                        nikMsg.classList.remove('hidden');

                        tglMsg.className = "text-xs mt-1 text-rose-500 font-semibold";
                        tglMsg.textContent = "⚠ Tanggal lahir belum sesuai dengan 6 digit NIK";
                        tglMsg.classList.remove('hidden');
                    }
                } else if (nik.length === 16) {
                    nikMsg.className = "text-xs mt-1 text-purple-600 font-medium";
                    nikMsg.textContent = "✓ 16 digit NIK lengkap. Pilih tanggal lahir untuk pencocokan.";
                    nikMsg.classList.remove('hidden');
                    tglMsg.classList.add('hidden');
                }
            }

            function validateAndGoStep(targetStep) {
                if (targetStep === 2) {
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
                        return;
                    }

                    if (!nik) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Data Belum Lengkap',
                            text: 'NIK (Nomor Induk Kependudukan) wajib diisi!',
                            confirmButtonColor: '#9333ea'
                        });
                        return;
                    }

                    if (nik.length !== 16 || !/^\d{16}$/.test(nik)) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Format NIK Tidak Valid',
                            text: 'NIK harus berjumlah tepat 16 digit angka!',
                            confirmButtonColor: '#9333ea'
                        });
                        return;
                    }

                    if (!tglLahir) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Data Belum Lengkap',
                            text: 'Tanggal lahir calon mahasiswa wajib dipilih!',
                            confirmButtonColor: '#9333ea'
                        });
                        return;
                    }

                    // Eksekusi Validasi Kesesuaian NIK & Tanggal Lahir
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
                                <div>• <strong>6 Digit Awal (1-6):</strong> Kode Wilayah Pendaftaran</div>
                                <div>• <strong>6 Digit Tengah (7-12):</strong> Tanggal Lahir (DDMMYY), dengan <em>penambahan angka 40</em> pada tanggal untuk perempuan</div>
                                <div>• <strong>4 Digit Akhir (13-16):</strong> Nomor Urut Registrasi</div>
                            </div>
                        `;

                        Swal.fire({
                            icon: 'error',
                            title: 'Tanggal Lahir Tidak Sesuai!',
                            html: htmlMsg,
                            confirmButtonColor: '#9333ea',
                            confirmButtonText: 'Periksa Kembali'
                        });
                        return;
                    }
                }
                goToStep(targetStep);
            }

            document.addEventListener("DOMContentLoaded", function() {
                checkNikTanggalLive();

                const formPendaftaran = document.getElementById('formPendaftaran');
                if (formPendaftaran) {
                    formPendaftaran.addEventListener('submit', function(e) {
                        const nik = document.getElementById('nik') ? document.getElementById('nik').value.trim() : '';
                        const tglLahir = document.getElementById('tanggal_lahir') ? document.getElementById('tanggal_lahir').value : '';
                        const jk = document.getElementById('jenis_kelamin') ? document.getElementById('jenis_kelamin').value : 'L';

                        if (nik && tglLahir) {
                            const nikCheck = validateNikTanggalLahir(nik, tglLahir, jk);
                            if (!nikCheck.valid) {
                                e.preventDefault();
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Tanggal Lahir Tidak Sesuai!',
                                    text: 'Tanggal lahir tidak sesuai dengan NIK. Silakan periksa kembali sebelum menyimpan data.',
                                    confirmButtonColor: '#9333ea'
                                });
                                goToStep(1);
                                return false;
                            }
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
                if ($search !== '' && strpos(strtolower($item['nama']), $search) === false && strpos(strtolower($item['nim']), $search) === false) { continue; }
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
                    <label class="filter-label">Cari Nama / NIM</label>
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
                    'prodi'    => $item['prodi'] ?? '-',
                    'golongan' => $item['golongan'] ?? 'Umum',
                    'tahun'    => $item['tahun'] ?? '-',
                    'status'   => $item['status'] ?? 'Menunggu',
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
                                    <th class="w-24 text-center col-hide">GOLONGAN</th>
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
                                        <div class="text-xs text-gray-400 mt-0.5">NIM: <?= htmlspecialchars($item['nim'] ?? '-') ?></div>
                                    </td>
                                    <td class="col-hide"><span class="text-xs font-medium text-purple-700"><?= htmlspecialchars($item['prodi']) ?></span></td>
                                    <td class="text-center col-hide"><span class="inline-flex px-2 py-0.5 rounded text-xs border bg-gray-50 text-gray-600 border-gray-200"><?= htmlspecialchars($golongan) ?></span></td>
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
                                <div><div class="text-gray-400 text-[10px] font-semibold uppercase tracking-wider mb-0.5">NIM</div><div id="dNim" class="font-mono font-semibold text-gray-800 text-[11px]"></div></div>
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
                setT('detailSubheader', item.prodi + ' \u2022 Tahun ' + item.tahun + ' \u2022 NIM: ' + (item.nim || '-'));

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
                setT('dNim',     item.nim);
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