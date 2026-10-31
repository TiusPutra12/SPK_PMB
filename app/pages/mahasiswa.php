<?php
require_once __DIR__ . '/../controllers/MahasiswaController.php';

$mhsCtrl = new MahasiswaController();
$mhsCtrl->handleRequest();

$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : '';
$wpData = $mhsCtrl->getMahasiswaListWithWP();
$pendaftar_list = $wpData['pendaftar_list'];
$kriteria_db = $wpData['kriteria_db'];
$nilai_raw_mhs = $wpData['nilai_raw'];

$list_prodi = array_values(array_filter(array_unique(array_column($pendaftar_list, 'prodi'))));
sort($list_prodi);
$list_tahun = array_values(array_filter(array_unique(array_column($pendaftar_list, 'tahun'))));
rsort($list_tahun);

include '../includes/head.php'; 
echo $style_css; 
?>

<main class="p-8 flex-1 overflow-y-auto">
    
    <?php if ($aksi === 'tambah' || $aksi === 'edit'): ?>
        <?php 
            $isEdit = ($aksi === 'edit');
            $id = $isEdit ? (int)$_GET['id'] : 0;
            $data = $isEdit ? ($mhsCtrl->getMahasiswaDetail($id) ?? []) : [];
            $golongan_val = $data['golongan'] ?? 'Umum';
            $editNilai = $nilai_raw_mhs[$id] ?? [];
            $penghasilan_val = $data['penghasilan_orangtua'] ?? '';
        ?>

        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800"><?= $isEdit ? 'Edit Pendaftaran Calon Mahasiswa' : 'Pendaftaran Calon Mahasiswa Baru' ?></h2>
                <p class="text-sm text-gray-500 mt-1">Lengkapi proses pendaftaran melalui 3 langkah berurutan di bawah ini.</p>
            </div>
            <a href="mahasiswa.php?page=mahasiswa" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition-colors">
                Kembali ke Daftar
            </a>
        </div>

        <div class="mb-8 max-w-4xl mx-auto">
            <div class="bg-white p-6 rounded-2xl border border-gray-200/80 shadow-sm">
                <div class="flex items-center justify-between w-full relative">
                    
                    <div class="flex flex-col items-center relative z-10 cursor-pointer" onclick="goToStep(1)">
                        <div id="step-node-1" class="w-12 h-12 rounded-full border-4 border-purple-600 bg-purple-600 text-white font-bold text-base flex items-center justify-center shadow-md transition-all duration-300">
                            1
                        </div>
                        <span id="step-label-1" class="text-xs font-bold text-purple-700 mt-2 text-center transition-colors">Data Diri</span>
                    </div>
                        <div class="flex-1 h-1 mx-2 sm:mx-4 bg-gray-200 rounded relative overflow-hidden">
                        <div id="step-line-1" class="h-full bg-purple-600 w-0 transition-all duration-500"></div>
                    </div>
                    <div class="flex flex-col items-center relative z-10 cursor-pointer" onclick="validateAndGoStep2()">
                        <div id="step-node-2" class="w-12 h-12 rounded-full border-4 border-gray-300 bg-white text-gray-500 font-bold text-base flex items-center justify-center shadow-sm transition-all duration-300">
                            2
                        </div>
                        <span id="step-label-2" class="text-xs font-semibold text-gray-400 mt-2 text-center transition-colors">Input Nilai</span>
                    </div>
                    <div class="flex-1 h-1 mx-2 sm:mx-4 bg-gray-200 rounded relative overflow-hidden">
                        <div id="step-line-2" class="h-full bg-purple-600 w-0 transition-all duration-500"></div>
                    </div>
                    <div class="flex flex-col items-center relative z-10 cursor-pointer" onclick="validateAndGoStep3()">
                        <div id="step-node-3" class="w-12 h-12 rounded-full border-4 border-gray-300 bg-white text-gray-500 font-bold text-base flex items-center justify-center shadow-sm transition-all duration-300">
                            3
                        </div>
                        <span id="step-label-3" class="text-xs font-semibold text-gray-400 mt-2 text-center transition-colors">Beasiswa</span>
                    </div>
                    <div class="flex-1 h-1 mx-2 sm:mx-4 bg-gray-200 rounded relative overflow-hidden">
                        <div id="step-line-3" class="h-full bg-emerald-500 w-0 transition-all duration-500"></div>
                    </div>
                    <div class="flex flex-col items-center relative z-10">
                        <div id="step-node-4" class="w-12 h-12 rounded-full border-4 border-gray-300 bg-white text-gray-400 font-bold text-xs flex items-center justify-center shadow-sm transition-all duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span id="step-label-4" class="text-xs font-semibold text-gray-400 mt-2 text-center transition-colors">Selesai</span>
                    </div>

                </div>
            </div>
        </div>
        <div class="table-card max-w-4xl">
            <div class="p-6">
                <form id="formPendaftaran" action="" method="post">
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="id_mahasiswa" value="<?= $data['id_mahasiswa'] ?>">
                    <?php endif; ?>

                    <div id="section-step-1" class="space-y-6">
                        <div class="flex items-center justify-between pb-3 border-b border-purple-100">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-purple-900 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-purple-100 text-purple-700 text-xs font-bold flex items-center justify-center">1</span>
                                Proses 1: Identitas & Data Diri Calon Mahasiswa
                            </h3>
                            <span class="text-xs font-medium text-gray-500">Langkah 1 dari 3</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="filter-item">
                                <label class="filter-label">NIM / Nomor Pendaftaran <span class="text-red-500">*</span></label>
                                <input type="text" id="nim" name="nim" value="<?= htmlspecialchars($data['nim'] ?? '') ?>" required class="filter-input w-full" placeholder="Contoh: 220101001">
                            </div>
                            <div class="filter-item">
                                <label class="filter-label">Nama Lengkap <span class="text-red-500">*</span></label>
                                <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($data['nama_mahasiswa'] ?? '') ?>" required class="filter-input w-full" placeholder="Masukkan nama lengkap">
                            </div>
                            <div class="filter-item">
                                <label class="filter-label">Golongan Mahasiswa <span class="text-red-500">*</span></label>
                                <select name="golongan" id="golonganSelect" required class="filter-select w-full" onchange="toggleBeasiswaAutoNotice()">
                                    <option value="Umum" <?= $golongan_val == 'Umum' ? 'selected' : '' ?>>Umum</option>
                                    <option value="Beasiswa" <?= $golongan_val == 'Beasiswa' ? 'selected' : '' ?>>Beasiswa</option>
                                </select>
                            </div>
                            <div class="filter-item">
                                <label class="filter-label">Status Kelulusan PMB <span class="text-red-500">*</span></label>
                                <select name="status" required class="filter-select w-full">
                                    <option value="Diterima" <?= ($data['status'] ?? '') == 'Diterima' ? 'selected' : '' ?>>Diterima</option>
                                    <option value="Ditolak" <?= ($data['status'] ?? 'Ditolak') == 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
                                </select>
                            </div>
                        </div>
                        <div class="pt-4 border-t border-gray-100">
                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Informasi Kontak & Domisili</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="filter-item">
                                    <label class="filter-label">NIK (Nomor Induk Kependudukan)</label>
                                    <input type="text" name="nik" maxlength="16" value="<?= htmlspecialchars($data['nik'] ?? '') ?>" class="filter-input w-full" placeholder="16 digit NIK KTP/KK">
                                </div>
                                <div class="filter-item">
                                    <label class="filter-label">Jenis Kelamin</label>
                                    <select name="jenis_kelamin" class="filter-select w-full">
                                        <option value="L" <?= ($data['jenis_kelamin'] ?? 'L') == 'L' ? 'selected' : '' ?>>Laki-laki (L)</option>
                                        <option value="P" <?= ($data['jenis_kelamin'] ?? '') == 'P' ? 'selected' : '' ?>>Perempuan (P)</option>
                                    </select>
                                </div>
                                <div class="filter-item">
                                    <label class="filter-label">Tempat, Tanggal Lahir</label>
                                    <input type="text" name="tempat_tanggal_lahir" value="<?= htmlspecialchars($data['tempat_tanggal_lahir'] ?? '') ?>" class="filter-input w-full" placeholder="Contoh: Jakarta, 12 Mei 2004">
                                </div>
                                <div class="filter-item">
                                    <label class="filter-label">Asal Sekolah</label>
                                    <input type="text" name="asal_sekolah" value="<?= htmlspecialchars($data['asal_sekolah'] ?? '') ?>" class="filter-input w-full" placeholder="Contoh: SMA Negeri 1 Jakarta">
                                </div>
                                <div class="filter-item">
                                    <label class="filter-label">Jurusan / Program Studi</label>
                                    <input type="text" name="jurusan_asal" value="<?= htmlspecialchars($data['jurusan_asal'] ?? '') ?>" class="filter-input w-full" placeholder="Contoh: Teknik Informatika / IPA">
                                </div>
                                <div class="filter-item">
                                    <label class="filter-label">No. HP / WhatsApp</label>
                                    <input type="text" name="no_hp" maxlength="15" value="<?= htmlspecialchars($data['no_hp'] ?? '') ?>" class="filter-input w-full" placeholder="Contoh: 08123456789">
                                </div>
                                <div class="filter-item md:col-span-2">
                                    <label class="filter-label">Email</label>
                                    <input type="email" name="email" value="<?= htmlspecialchars($data['email'] ?? '') ?>" class="filter-input w-full" placeholder="Contoh: mahasiswa@email.com">
                                </div>
                                <div class="filter-item md:col-span-2">
                                    <label class="filter-label">Alamat Lengkap</label>
                                    <textarea name="alamat" rows="2" class="filter-input w-full" placeholder="Masukkan alamat domisili lengkap..."><?= htmlspecialchars($data['alamat'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between items-center pt-5 border-t border-gray-100">
                            <a href="mahasiswa.php?page=mahasiswa" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors text-sm">
                                Batal
                            </a>
                            <button type="button" onclick="validateAndGoStep2()" class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white font-medium rounded-lg shadow-sm transition-all text-sm flex items-center gap-2">
                                <span>Lanjut ke Proses 2: Input Nilai</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div id="section-step-2" class="space-y-6 hidden">
                        <div class="flex items-center justify-between pb-3 border-b border-purple-100">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-purple-900 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-purple-600 text-white text-xs font-bold flex items-center justify-center">2</span>
                                Proses 2: Input Nilai Kriteria Penilaian SPK (Tabel Nilai)
                            </h3>
                            <span class="text-xs font-medium text-gray-500">Langkah 2 dari 3</span>
                        </div>

                        <div class="bg-blue-50/60 border border-blue-200 rounded-xl p-4 text-xs text-blue-900 leading-relaxed flex items-start gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <span class="font-bold block mb-0.5">Petunjuk Penilaian Weight Product (WP):</span>
                                Pilih opsi <strong>Sub Kriteria</strong> untuk setiap kriteria penilaian (C1 s.d C<?= count($kriteria_db) ?>). Nilai bobot sub kriteria akan otomatis terisi dan disimpan ke tabel <code>nilai</code>.
                            </div>
                        </div>

                        <div class="space-y-4">
                            <?php 
                            $cNo = 1;
                            foreach ($kriteria_db as $idK => $krit): 
                                $curSubId = $editNilai[$idK]['id_sub'] ?? 0;
                                $curNilai = $editNilai[$idK]['nilai'] ?? '';
                            ?>
                                <div class="p-4 bg-gray-50/70 border border-gray-200 rounded-xl hover:border-purple-200 transition-colors">
                                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-3">
                                        <div class="flex items-center gap-2">
                                            <span class="px-2.5 py-1 bg-purple-100 text-purple-800 font-bold font-mono text-xs rounded-md">
                                                C<?= $cNo ?>
                                            </span>
                                            <div>
                                                <h4 class="font-bold text-gray-900 text-sm"><?= htmlspecialchars($krit['nama_kriteria']) ?></h4>
                                                <div class="text-xs text-gray-400">
                                                    Sifat: <span class="font-semibold text-gray-600"><?= htmlspecialchars($krit['jenis']) ?></span> &bull; 
                                                    Bobot: <span class="font-semibold text-gray-600"><?= $krit['bobot'] ?>%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="filter-item">
                                            <label class="filter-label">Pilih Klasifikasi / Sub Kriteria <span class="text-red-500">*</span></label>
                                            <select name="kriteria_sub[<?= $idK ?>]" id="sub_select_<?= $idK ?>" class="filter-select w-full" onchange="updateSubScore(<?= $idK ?>)">
                                                <option value="">-- Pilih Sub Kriteria --</option>
                                                <?php foreach ($krit['subs'] as $sub): ?>
                                                    <option value="<?= $sub['id_sub'] ?>" data-score="<?= $sub['nilai'] ?>" <?= ($curSubId == $sub['id_sub']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($sub['nama_sub']) ?> (Bobot: <?= number_format($sub['nilai'], 2) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="filter-item">
                                            <label class="filter-label">Nilai Terpilih / Skor Bobot</label>
                                            <input type="number" step="0.01" name="kriteria_nilai[<?= $idK ?>]" id="sub_nilai_<?= $idK ?>" value="<?= htmlspecialchars((string)$curNilai) ?>" class="filter-input w-full bg-gray-100 font-mono font-bold text-purple-800" placeholder="Skor otomatis terisi">
                                        </div>
                                    </div>
                                </div>
                            <?php 
                                $cNo++;
                            endforeach; 
                            ?>
                        </div>

                        <div class="flex flex-col sm:flex-row justify-between items-center gap-3 pt-5 border-t border-gray-100">
                            <button type="button" onclick="goToStep(1)" class="w-full sm:w-auto px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors text-sm flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                <span>Kembali ke Proses 1</span>
                            </button>
                            <div class="flex items-center gap-2 w-full sm:w-auto">
                                <button type="submit" name="<?= $isEdit ? 'simpan_edit' : 'simpan_tambah' ?>" class="w-full sm:w-auto px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg text-sm transition-colors" title="Simpan langsung tanpa pengajuan beasiswa">
                                    Lewati Proses 3 & Simpan
                                </button>
                                <button type="button" onclick="validateAndGoStep3()" class="w-full sm:w-auto px-6 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-medium rounded-lg shadow-sm transition-all text-sm flex items-center justify-center gap-2">
                                    <span>Lanjut ke Proses 3: Beasiswa</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                        <div id="section-step-3" class="space-y-6 hidden">
                        <div class="flex items-center justify-between pb-3 border-b border-amber-200">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-amber-900 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-amber-500 text-white text-xs font-bold flex items-center justify-center">3</span>
                                Proses 3: Pengajuan Beasiswa (Opsional / Bisa Dilewati)
                            </h3>
                            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-300">
                                Langkah 3 dari 3 (Bisa Dilewati)
                            </span>
                        </div>

                        <div class="bg-amber-50/70 border border-amber-200 rounded-xl p-4 text-xs text-amber-900 leading-relaxed flex items-start gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div>
                                <span class="font-bold block mb-0.5">Informasi Pengajuan Beasiswa:</span>
                                Tahap ini khusus calon mahasiswa yang mengajukan beasiswa (golongan <strong>Beasiswa</strong>). Silakan isi kisaran <strong>Penghasilan Orang Tua</strong> sebagai dasar pertimbangan kelayakan. Jika pendaftaran reguler/umum, Anda dapat langsung mengklik tombol <strong>"Lewati & Simpan Langsung"</strong>.
                            </div>
                        </div>

                        <div class="p-5 bg-white border border-gray-200 rounded-xl shadow-sm space-y-5">
                            <div class="filter-item">
                                <label class="filter-label flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Penghasilan Orang Tua (Per Bulan)</span>
                                    <span class="text-xs text-gray-400 font-normal">(Pilih kisaran atau isi nominal)</span>
                                </label>
                                <select name="penghasilan_select" id="penghasilanSelect" class="filter-select w-full" onchange="syncPenghasilanInput()">
                                    <option value="">-- Pilih Kisaran Penghasilan Orang Tua --</option>
                                    <option value="< Rp 1.500.000 / bulan" <?= strpos($penghasilan_val, '1.500.000') !== false ? 'selected' : '' ?>>&lt; Rp 1.500.000 / bulan (Sangat Rendah)</option>
                                    <option value="Rp 1.500.000 - Rp 3.000.000 / bulan" <?= strpos($penghasilan_val, '3.000.000') !== false ? 'selected' : '' ?>>Rp 1.500.000 - Rp 3.000.000 / bulan (Rendah)</option>
                                    <option value="Rp 3.000.000 - Rp 5.000.000 / bulan" <?= strpos($penghasilan_val, '5.000.000') !== false ? 'selected' : '' ?>>Rp 3.000.000 - Rp 5.000.000 / bulan (Menengah)</option>
                                    <option value="> Rp 5.000.000 / bulan" <?= strpos($penghasilan_val, '> Rp 5.000.000') !== false ? 'selected' : '' ?>>&gt; Rp 5.000.000 / bulan (Mampu)</option>
                                </select>
                            </div>

                            <div class="filter-item">
                                <label class="filter-label">Catatan / Detail Nominal Penghasilan Orang Tua</label>
                                <input type="text" name="penghasilan_orangtua" id="penghasilanInput" value="<?= htmlspecialchars($penghasilan_val) ?>" class="filter-input w-full" placeholder="Contoh: Rp 2.000.000 / bulan (Buruh harian)">
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row justify-between items-center gap-3 pt-5 border-t border-gray-100">
                            <button type="button" onclick="goToStep(2)" class="w-full sm:w-auto px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors text-sm flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                <span>Kembali ke Proses 2</span>
                            </button>
                            <div class="flex items-center gap-2 w-full sm:w-auto">
                                <button type="submit" name="<?= $isEdit ? 'simpan_edit' : 'simpan_tambah' ?>" onclick="clearPenghasilan()" class="w-full sm:w-auto px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors text-sm text-center">
                                    Lewati (Tanpa Beasiswa)
                                </button>
                                <button type="submit" name="<?= $isEdit ? 'simpan_edit' : 'simpan_tambah' ?>" class="w-full sm:w-auto px-6 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-medium rounded-lg shadow-sm transition-all text-sm flex items-center justify-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Simpan Pengajuan Beasiswa</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <script src="../../assets/js/mahasiswa.js?v=<?= time() ?>"></script>

    <?php else: ?>
        <div class="flex justify-end mb-6">
            <a href="mahasiswa.php?page=mahasiswa&aksi=tambah" class="px-5 py-2.5 bg-gradient-to-r from-pink-500 to-pink-600 hover:from-pink-600 hover:to-pink-700 text-white font-medium rounded-lg shadow-sm transition-all flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Tambah Mahasiswa Baru
            </a>
        </div>

        <div class="filter-section">
            <div class="filter-grid">
                <div class="filter-item">
                    <label for="filterProdi" class="filter-label">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Program Studi / Jurusan
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
                        Tahun Angkatan
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
                        Cari Nama / NIM Mahasiswa
                    </label>
                    <input type="text" id="searchNama" class="filter-input" placeholder="Ketik nama atau NIM mahasiswa...">
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
                            <span>Daftar Mahasiswa</span>
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
                                    <th class="w-24 text-center col-hide-on-split">Golongan</th>
                                    <th class="w-24 text-center col-hide-on-split">Tahun</th>
                                    <th class="w-28 text-center col-hide-on-split">Status</th>
                                    <th class="w-28 text-center col-hide-on-split">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyMahasiswa">
                                <?php 
                                $no = 1;
                                if (!empty($pendaftar_list)):
                                    foreach ($pendaftar_list as $item): 
                                        $id = $item['id'];
                                        $status = $item['status'] ?? 'Ditolak';
                                        $status_class = ($status === 'Diterima') ? 'diterima' : 'tidak-diterima';
                                        $golongan = $item['golongan'] ?? 'Umum';
                                        $golongan_class = ($golongan === 'Beasiswa') ? 'bg-amber-100 text-amber-800 border-amber-300' : 'bg-gray-100 text-gray-700 border-gray-200';
                                ?>
                                    <tr class="data-row" 
                                        data-id="<?= $id ?>"
                                        data-nama="<?= htmlspecialchars(strtolower($item['nama'] . ' ' . $item['nim'])) ?>"
                                        data-prodi="<?= htmlspecialchars($item['prodi']) ?>"
                                        data-tahun="<?= htmlspecialchars($item['tahun']) ?>"
                                        onclick="selectStudent(<?= $id ?>)">
                                        
                                        <td class="text-center font-semibold text-gray-500 row-number col-hide-on-split"><?= $no++ ?></td>
                                        
                                        <td class="col-nama">
                                            <div class="font-semibold text-gray-900 leading-snug"><?= htmlspecialchars($item['nama']) ?></div>
                                            <div class="text-xs text-gray-400 mt-0.5">NIM: <?= htmlspecialchars($item['nim'] ?? '-') ?></div>
                                        </td>
                                        
                                        <td class="col-hide-on-split">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-purple-50 text-purple-700 border border-purple-200">
                                                <?= htmlspecialchars($item['prodi']) ?>
                                            </span>
                                        </td>

                                        <td class="text-center col-hide-on-split">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold border <?= $golongan_class ?>">
                                                <?= htmlspecialchars($golongan) ?>
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
                                        <td class="text-center col-hide-on-split" onclick="event.stopPropagation();">
                                            <div class="flex items-center justify-center gap-2">
                                                <a href="mahasiswa.php?page=mahasiswa&aksi=edit&id=<?= $id ?>" class="p-1.5 text-blue-600 bg-blue-50 rounded hover:bg-blue-100 transition-colors" title="Edit Data">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                                </a>
                                                <a href="mahasiswa.php?page=mahasiswa&aksi=hapus&id=<?= $id ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');" class="p-1.5 text-red-600 bg-red-50 rounded hover:bg-red-100 transition-colors" title="Hapus Data">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php 
                                    endforeach;
                                else: 
                                ?>
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-gray-400 italic">
                                            Belum ada data mahasiswa di database.
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
                            $status = $item['status'] ?? 'Ditolak';
                            $status_class = ($status === 'Diterima') ? 'diterima' : 'tidak-diterima';
                            $gol = $item['golongan'] ?? 'Umum';
                        ?>
                            <div class="student-detail-pane hidden" id="pane-<?= $id ?>"
                                 data-nama="<?= htmlspecialchars($item['nama']) ?>"
                                 data-inisial="<?= strtoupper(substr($item['nama'], 0, 1)) ?>"
                                 data-sub="<?= htmlspecialchars($item['prodi'] . ' • Tahun ' . $item['tahun'] . ' • NIM: ' . ($item['nim'] ?? '-')) ?>"
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
                                                Golongan: <?= htmlspecialchars($gol) ?>
                                            </span>
                                        </div>
                                        <div class="panel-body">
                                            <div class="identitas-grid">
                                                <div class="id-field">
                                                    <span class="id-label">NIM</span>
                                                    <span class="id-val font-mono text-purple-700"><?= htmlspecialchars($item['nim'] ?? '-') ?></span>
                                                </div>
                                                <div class="id-field">
                                                    <span class="id-label">Golongan</span>
                                                    <span class="id-val font-semibold text-purple-800"><?= htmlspecialchars($gol) ?></span>
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
                                                    <span class="id-val"><?= htmlspecialchars($identitas['tempat_tanggal_lahir'] ?? '-') ?></span>
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
                                                    <span class="id-label">Penghasilan Orang Tua</span>
                                                    <span class="id-val font-medium text-amber-700"><?= htmlspecialchars($identitas['penghasilan'] ?? '-') ?></span>
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
                                                Ranking #<?= htmlspecialchars((string)($nilai['peringkat'] ?? '-')) ?>
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
                                                                    <span class="score-badge"><?= number_format((float)$krit['nilai'], 2) ?></span>
                                                                </td>
                                                                <td class="text-xs text-gray-600">
                                                                    <?= htmlspecialchars($krit['keterangan']) ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="4" class="text-center text-gray-400 italic py-3">Belum ada nilai diinput</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>

                                            <div class="spk-summary-box">
                                                <div class="spk-item">
                                                    <div class="spk-item-label">Vektor S</div>
                                                    <div class="spk-item-value"><?= htmlspecialchars($nilai['vektor_s'] ?? '0') ?></div>
                                                </div>
                                                <div class="spk-item">
                                                    <div class="spk-item-label">Vektor V</div>
                                                    <div class="spk-item-value text-indigo-600"><?= htmlspecialchars($nilai['vektor_v'] ?? '0') ?></div>
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
        <script src="../../assets/js/mahasiswa.js?v=<?= time() ?>"></script>
    <?php endif; ?>
</main>
</div> 
</body>
</html>
