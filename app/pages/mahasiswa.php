<!-- app/pages/mahasiswa.php -->
<?php
/** @var mysqli $koneksi */

// Ambil data mahasiswa dari database
$query = mysqli_query($koneksi, "SELECT * FROM mahasiswa ORDER BY id_mahasiswa DESC");
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-white">Data Mahasiswa (Alternatif)</h1>
    <button class="px-4 py-2 bg-pink-600 hover:bg-pink-500 text-white rounded-lg shadow-md transition-colors">
        + Tambah Mahasiswa
    </button>
</div>

<div class="bg-card rounded-xl shadow-lg border border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-800 text-gray-300 text-sm uppercase tracking-wide border-b border-gray-700">
                    <th class="px-6 py-4 font-medium w-16 text-center">No</th>
                    <th class="px-6 py-4 font-medium w-48">NIM</th>
                    <th class="px-6 py-4 font-medium">Nama Mahasiswa</th>
                    <th class="px-6 py-4 font-medium w-32 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-300 divide-y divide-gray-700">
                <?php 
                $no = 1;
                if (mysqli_num_rows($query) > 0) :
                    while ($row = mysqli_fetch_assoc($query)) : 
                ?>
                    <tr class="hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-4 text-center"><?= $no++ ?></td>
                        <td class="px-6 py-4 text-pink-400 font-medium"><?= htmlspecialchars($row['nim']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
                        <td class="px-6 py-4 text-center space-x-2">
                            <button class="text-blue-400 hover:text-blue-300 transition-colors">Edit</button>
                            <button class="text-red-400 hover:text-red-300 transition-colors">Hapus</button>
                        </td>
                    </tr>
                <?php 
                    endwhile; 
                else: 
                ?>
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500 italic">Belum ada data mahasiswa.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>