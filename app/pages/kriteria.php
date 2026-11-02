<?php
require_once __DIR__ . '/../controllers/KriteriaController.php';

$kriteriaCtrl = new KriteriaController();
$kriteriaCtrl->handleRequest();

$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : '';
$dataKriteria = $kriteriaCtrl->getKriteriaList();
$totalBobot = array_sum(array_column($dataKriteria, 'bobot'));

include '../includes/head.php'; 
echo $style_css; 
?>

<main class="p-8 flex-1 overflow-y-auto">
    <?php if ($aksi === 'tambah'): ?>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Tambah Data Kriteria</h2>
        </div>
        
        <div class="table-card max-w-2xl">
            <div class="p-6">
                <form action="" method="post" class="space-y-5">
                    <div class="filter-item">
                        <label class="filter-label">Nama Kriteria</label>
                        <input type="text" name="nama_kriteria" required class="filter-input w-full" placeholder="Contoh: Nilai Rapor">
                    </div>
                    <div class="filter-item">
                        <label class="filter-label">Jenis Kriteria</label>
                        <select name="jenis" required class="filter-select w-full">
                            <option value="Benefit" selected>Benefit (Keuntungan)</option>
                            <option value="Cost">Cost (Biaya)</option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <label class="filter-label">Bobot (%)</label>
                        <input type="number" step="0.01" name="bobot" required class="filter-input w-full" placeholder="Contoh: 25">
                    </div>
                    <div class="flex gap-3 pt-5 border-t border-gray-100 mt-6">
                        <button type="submit" name="simpan_kriteria" class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white font-medium rounded-lg shadow-sm transition-all">Simpan Kriteria</button>
                        <a href="kriteria.php?page=kriteria" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-all">Batal</a>
                    </div>
                </form>
            </div>
        </div>

    <?php elseif ($aksi === 'edit' && isset($_GET['id'])): ?>
        <?php 
            $idEdit = (int)$_GET['id'];
            $dEdit = $kriteriaCtrl->getKriteriaDetail($idEdit);
        ?>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Edit Data Kriteria</h2>
        </div>
        
        <div class="table-card max-w-2xl">
            <div class="p-6">
                <form action="" method="post" class="space-y-5">
                    <input type="hidden" name="id_kriteria" value="<?= $dEdit['id_kriteria'] ?>">
                    <div class="filter-item">
                        <label class="filter-label">Nama Kriteria</label>
                        <input type="text" name="nama_kriteria" value="<?= htmlspecialchars($dEdit['nama_kriteria']) ?>" required class="filter-input w-full">
                    </div>
                    <div class="filter-item">
                        <label class="filter-label">Jenis Kriteria</label>
                        <select name="jenis" required class="filter-select w-full">
                            <option value="Benefit" <?= $dEdit['jenis'] === 'Benefit' ? 'selected' : '' ?>>Benefit (Keuntungan)</option>
                            <option value="Cost" <?= $dEdit['jenis'] === 'Cost' ? 'selected' : '' ?>>Cost (Biaya)</option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <label class="filter-label">Bobot (%)</label>
                        <input type="number" step="0.01" name="bobot" value="<?= htmlspecialchars($dEdit['bobot']) ?>" required class="filter-input w-full">
                    </div>
                    <div class="flex gap-3 pt-5 border-t border-gray-100 mt-6">
                        <button type="submit" name="simpan_edit_kriteria" class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white font-medium rounded-lg shadow-sm transition-all">Simpan Perubahan</button>
                        <a href="kriteria.php?page=kriteria" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-all">Batal</a>
                    </div>
                </form>
            </div>
        </div>

    <?php else: ?>
        <div class="filter-section mb-6">
            <div class="filter-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <div class="filter-item">
                    <span class="filter-label">Jumlah Kriteria Aktif</span>
                    <span class="text-xl font-bold text-purple-700 font-mono"><?= count($dataKriteria) ?> Kriteria</span>
                </div>
                <div class="filter-item">
                    <span class="filter-label">Total Akumulasi Bobot</span>
                    <span class="text-xl font-bold <?= $totalBobot == 100 ? 'text-green-600' : 'text-purple-600' ?> font-mono">
                        <?= $totalBobot ?>%
                    </span>
                </div>
                <div class="filter-item">
                    <span class="filter-label">Metode Penilaian</span>
                    <span class="text-sm font-semibold text-gray-700 mt-1">Naive Bayes</span>
                </div>
            </div>
        </div>
        <div class="table-card">
            <div class="table-card-header">
                <div class="table-card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span>Daftar Kriteria & Bobot Penilaian</span>
                    <span class="table-badge-counter"><?= count($dataKriteria) ?> Data</span>
                </div>
                <div class="flex items-center gap-4">
                    <a href="kriteria.php?page=kriteria&aksi=tambah" class="px-4 py-2 bg-gradient-to-r from-pink-500 to-pink-600 hover:from-pink-600 hover:to-pink-700 text-white font-medium text-xs rounded-lg shadow-sm transition-all flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Kriteria
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="custom-table w-full">
                    <thead>
                        <tr>
                            <th class="w-16 text-center">Kode</th>
                            <th>Nama Kriteria</th>
                            <th class="w-28 text-center">Jenis</th>
                            <th class="w-24 text-center">Bobot</th>
                            <th class="text-center">Sub Kriteria Utama</th>
                            <th class="w-24 text-center">Bobot Sub</th>
                            <th class="w-32 text-center">Rincian Sub</th>
                            <th class="w-28 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($dataKriteria)): ?>
                            <?php 
                            $kodeNo = 1;
                            foreach ($dataKriteria as $idK => $kriteria) : 
                                $subs = $kriteria['subs'];
                                $jmlSub = count($subs);
                                $firstSub = $jmlSub > 0 ? $subs[0] : null;
                                $jenis_class = ($kriteria['jenis'] === 'Cost') ? 'tidak-diterima' : 'diterima';
                            ?>
                                <tr class="data-row hover:bg-purple-50/50 transition-colors cursor-pointer" onclick="toggleSubKriteria(<?= $idK ?>)">
                                    <td class="text-center font-bold text-purple-600">C<?= $kodeNo ?></td>
                                    <td class="font-semibold text-gray-900"><?= htmlspecialchars($kriteria['nama']) ?></td>
                                    <td class="text-center">
                                        <span class="status-pill <?= $jenis_class ?> text-xs">
                                            <?= htmlspecialchars($kriteria['jenis']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center font-bold text-gray-800 text"><?= $kriteria['bobot'] ?>%</td>
                                    <td class="text-center">
                                        <?php if ($firstSub): ?>
                                            <span class="text-gray-800 font-medium"><?= htmlspecialchars($firstSub['nama_sub']) ?></span>
                                        <?php else: ?>
                                            <span class="text-gray-400 italic text-xs">Belum ada sub kriteria</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($firstSub): ?>
                                            <span class="score-badge"><?= number_format((float)$firstSub['nilai'], 2) ?></span>
                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($jmlSub > 1): ?>
                                            <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1 bg-purple-50 hover:bg-purple-100 text-purple-700 text-xs font-semibold rounded-full border border-purple-200 transition-colors" onclick="event.stopPropagation(); toggleSubKriteria(<?= $idK ?>);">
                                                <span>+<?= $jmlSub - 1 ?> Lainnya</span>
                                                <svg id="chevron-<?= $idK ?>" xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                        <?php elseif ($jmlSub === 1): ?>
                                            <span class="text-xs text-gray-500 font-medium">1 Opsi</span>
                                        <?php else: ?>
                                            <span class="text-xs text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center" onclick="event.stopPropagation();">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="kriteria.php?page=kriteria&aksi=edit&id=<?= $kriteria['id_kriteria'] ?>" class="text-blue-600 hover:text-blue-800 font-medium text-xs">Edit</a>
                                            <span class="text-gray-300">|</span>
                                            <a href="kriteria.php?page=kriteria&aksi=hapus_kriteria&id=<?= $kriteria['id_kriteria'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus kriteria ini?');" class="text-red-500 hover:text-red-700 font-medium text-xs">Hapus</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php if ($jmlSub > 0): ?>
                                    <tr id="sub-row-<?= $idK ?>" class="bg-gray-50/70 border-b border-purple-100/60" style="display: none;">
                                        <td colspan="8" class="p-0">
                                            <div id="sub-content-<?= $idK ?>" class="grid grid-rows-[0fr] transition-[grid-template-rows] duration-300 ease-in-out">
                                                <div class="overflow-hidden">
                                                    <div class="p-5 pl-16 bg-gradient-to-r from-purple-50/30 via-white to-purple-50/30 border-l-4 border-purple-500">
                                                        <div class="flex items-center justify-between mb-3">
                                                            <h4 class="text-xs font-bold uppercase tracking-wider text-purple-900 flex items-center gap-2">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                                                </svg>
                                                                Seluruh Pilihan Sub Kriteria: <?= htmlspecialchars($kriteria['nama']) ?>
                                                            </h4>
                                                            <span class="text-xs text-gray-500 font-medium"><?= $jmlSub ?> Klasifikasi Tersedia</span>
                                                        </div>

                                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-h-56 overflow-y-auto pr-1">
                                                            <?php foreach ($subs as $idx => $s): ?>
                                                                <div class="p-3 bg-white rounded-lg border border-purple-100 shadow-sm flex items-center justify-between gap-3">
                                                                    <div class="flex items-center gap-2.5">
                                                                        <span class="w-6 h-6 rounded-full bg-purple-100 text-purple-700 text-xs font-bold flex items-center justify-center">
                                                                            <?= $idx + 1 ?>
                                                                        </span>
                                                                        <span class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($s['nama_sub']) ?></span>
                                                                    </div>
                                                                    <div class="text-right">
                                                                        <span class="text-xs text-gray-400 block font-medium">Nilai / Bobot</span>
                                                                        <span class="score-badge text-xs"><?= number_format((float)$s['nilai'], 2) ?></span>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>

                            <?php 
                                $kodeNo++;
                            endforeach; 
                            ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-400 italic">
                                    Belum ada data kriteria yang tersimpan di database.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php endif; ?>

</main>
</div>

<script src="../../assets/js/kriteria.js?v=<?= time() ?>"></script>
</body>
</html>
