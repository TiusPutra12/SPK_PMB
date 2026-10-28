<!-- app/pages/kriteria.php -->
<?php
/** @var mysqli $koneksi */

// Ambil data kriteria beserta sub kriteria-nya melalui JOIN
$query = mysqli_query($koneksi, "
    SELECT k.id_kriteria, k.nama_kriteria, s.nama_sub, s.nilai 
    FROM kriteria k 
    LEFT JOIN sub_kriteria s ON k.id_kriteria = s.id_kriteria 
    ORDER BY k.id_kriteria ASC, s.nilai ASC
");

// Kelompokkan array berdasarkan ID Kriteria agar bisa menggunakan rowspan
$dataKriteria = [];
while ($row = mysqli_fetch_assoc($query)) {
    $dataKriteria[$row['id_kriteria']]['nama'] = $row['nama_kriteria'];
    $dataKriteria[$row['id_kriteria']]['subs'][] = [
        'nama_sub' => $row['nama_sub'],
        'nilai' => $row['nilai']
    ];
}
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-white">Data Kriteria & Sub Kriteria</h1>
    <button class="px-4 py-2 bg-pink-600 hover:bg-pink-500 text-white rounded-lg shadow-md transition-colors">
        + Tambah Data
    </button>
</div>

<div class="bg-card rounded-xl shadow-lg border border-gray-700 overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-gray-800 text-gray-300 text-sm uppercase tracking-wide border-b border-gray-700">
                <th class="px-6 py-4 font-medium">Kriteria</th>
                <th class="px-6 py-4 font-medium">Sub Kriteria</th>
                <th class="px-6 py-4 font-medium text-center">Nilai/Bobot</th>
            </tr>
        </thead>
        <tbody class="text-gray-300 divide-y divide-gray-700">
            
            <?php foreach ($dataKriteria as $id => $kriteria) : ?>
                <?php 
                $jmlSub = count($kriteria['subs']); 
                $first = true;
                ?>
                
                <?php if ($jmlSub > 0): ?>
                    <?php foreach ($kriteria['subs'] as $sub) : ?>
                        <tr class="hover:bg-gray-800/50 transition-colors <?= (!$first) ? 'border-l border-gray-700' : '' ?>">
                            
                            <?php if ($first): ?>
                                <td class="px-6 py-4 font-semibold text-pink-400 border-b border-gray-600" rowspan="<?= $jmlSub ?>">
                                    <?= htmlspecialchars($kriteria['nama']) ?>
                                </td>
                                <?php $first = false; ?>
                            <?php endif; ?>
                            
                            <td class="px-6 py-3 border-b border-gray-700"><?= htmlspecialchars($sub['nama_sub']) ?></td>
                            <td class="px-6 py-3 text-center border-b border-gray-700">
                                <?= number_format($sub['nilai'], 2, ',', '.') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Jika kriteria belum punya sub -->
                    <tr class="hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-4 font-semibold text-pink-400"><?= htmlspecialchars($kriteria['nama']) ?></td>
                        <td class="px-6 py-3 text-gray-500 italic">Belum ada sub kriteria</td>
                        <td class="px-6 py-3 text-center">-</td>
                    </tr>
                <?php endif; ?>
                
            <?php endforeach; ?>

        </tbody>
    </table>
</div>