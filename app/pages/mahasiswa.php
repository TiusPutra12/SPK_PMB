<!-- app/pages/mahasiswa.php -->
<?php
/** @var mysqli $koneksi */
$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : '';

// --- LOGIKA PROSES CRUD ---
if (isset($_POST['simpan_tambah'])) {
    $nim = mysqli_real_escape_string($koneksi, $_POST['nim']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    mysqli_query($koneksi, "INSERT INTO mahasiswa (nim, nama_mahasiswa, status) VALUES ('$nim', '$nama', '$status')");
    echo "<script>window.location.href='?page=mahasiswa';</script>";
    exit;
}

if (isset($_POST['simpan_edit'])) {
    $id = $_POST['id_mahasiswa'];
    $nim = mysqli_real_escape_string($koneksi, $_POST['nim']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    mysqli_query($koneksi, "UPDATE mahasiswa SET nim='$nim', nama_mahasiswa='$nama', status='$status' WHERE id_mahasiswa='$id'");
    echo "<script>window.location.href='?page=mahasiswa';</script>";
    exit;
}

if ($aksi == 'hapus' && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    mysqli_query($koneksi, "DELETE FROM mahasiswa WHERE id_mahasiswa='$id'");
    echo "<script>window.location.href='?page=mahasiswa';</script>";
    exit;
}
?>

<!-- --- ANTARMUKA (VIEW) --- -->
<?php if ($aksi == 'tambah'): ?>
    <div class="mb-6"><h1 class="text-3xl font-bold text-white">Tambah Data Mahasiswa</h1></div>
    <div class="bg-card p-6 rounded-xl border border-gray-700 max-w-2xl">
        <form action="" method="post" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">NIM</label>
                <input type="text" name="nim" required class="w-full bg-darker border border-gray-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-pink-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Nama Lengkap</label>
                <input type="text" name="nama" required class="w-full bg-darker border border-gray-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-pink-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Status Kelulusan</label>
                <select name="status" required class="w-full bg-darker border border-gray-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-pink-500">
                    <option value="Diterima">Diterima</option>
                    <option value="Ditolak" selected>Ditolak</option>
                </select>
            </div>
            <div class="flex gap-3 pt-4">
                <button type="submit" name="simpan_tambah" class="px-6 py-2 bg-pink-600 hover:bg-pink-500 text-white rounded-lg transition-colors">Simpan</button>
                <a href="?page=mahasiswa" class="px-6 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-colors">Batal</a>
            </div>
        </form>
    </div>

<?php elseif ($aksi == 'edit'): ?>
    <?php
        $id = mysqli_real_escape_string($koneksi, $_GET['id']);
        $query_edit = mysqli_query($koneksi, "SELECT * FROM mahasiswa WHERE id_mahasiswa='$id'");
        $data = mysqli_fetch_assoc($query_edit);
    ?>
    <div class="mb-6"><h1 class="text-3xl font-bold text-white">Edit Data Mahasiswa</h1></div>
    <div class="bg-card p-6 rounded-xl border border-gray-700 max-w-2xl">
        <form action="" method="post" class="space-y-4">
            <input type="hidden" name="id_mahasiswa" value="<?= $data['id_mahasiswa'] ?>">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">NIM</label>
                <input type="text" name="nim" value="<?= htmlspecialchars($data['nim']) ?>" required class="w-full bg-darker border border-gray-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-pink-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Nama Lengkap</label>
                <input type="text" name="nama" value="<?= htmlspecialchars($data['nama_mahasiswa']) ?>" required class="w-full bg-darker border border-gray-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-pink-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Status Kelulusan</label>
                <select name="status" required class="w-full bg-darker border border-gray-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-pink-500">
                    <option value="Diterima" <?= $data['status'] == 'Diterima' ? 'selected' : '' ?>>Diterima</option>
                    <option value="Ditolak" <?= $data['status'] == 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
                </select>
            </div>
            <div class="flex gap-3 pt-4">
                <button type="submit" name="simpan_edit" class="px-6 py-2 bg-pink-600 hover:bg-pink-500 text-white rounded-lg transition-colors">Simpan Perubahan</button>
                <a href="?page=mahasiswa" class="px-6 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-colors">Batal</a>
            </div>
        </form>
    </div>

<?php else: ?>
    <!-- --- LOGIKA PAGINATION & PENCARIAN --- -->
    <?php
        $search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $halaman = isset($_GET['hal']) ? (int)$_GET['hal'] : 1;
        $offset = ($halaman - 1) * $limit;

        $whereClause = "";
        if ($search != '') {
            $whereClause = "WHERE nim LIKE '%$search%' OR nama_mahasiswa LIKE '%$search%'";
        }

        $queryCount = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM mahasiswa $whereClause");
        $totalData = mysqli_fetch_assoc($queryCount)['total'];
        $totalPages = ceil($totalData / $limit);
        $query = mysqli_query($koneksi, "SELECT * FROM mahasiswa $whereClause ORDER BY id_mahasiswa DESC LIMIT $offset, $limit");
    ?>

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-white">Data Mahasiswa (Alternatif)</h1>
        <a href="?page=mahasiswa&aksi=tambah" class="px-4 py-2 bg-pink-600 hover:bg-pink-500 text-white rounded-lg shadow-md transition-colors">
            + Tambah Mahasiswa
        </a>
    </div>

    <!-- FILTER LIMIT & PENCARIAN -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
        <div class="flex items-center gap-2">
            <label class="text-gray-400 text-sm">Tampilkan</label>
            <select id="limitSelect" class="bg-darker border border-gray-700 text-white text-sm rounded-lg focus:ring-pink-500 focus:border-pink-500 block p-2">
                <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
                <option value="20" <?= $limit == 20 ? 'selected' : '' ?>>20</option>
                <option value="30" <?= $limit == 30 ? 'selected' : '' ?>>30</option>
                <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
            </select>
            <label class="text-gray-400 text-sm">data</label>
        </div>

        <div class="w-full md:w-auto relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 20 20" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                </svg>
            </div>
            <!-- Hapus form agar tidak submit manual, cukup andalkan event oninput JS -->
            <input type="text" id="searchInput" value="<?= htmlspecialchars($search) ?>" class="block w-full md:w-80 p-2 pl-10 text-sm border rounded-lg bg-darker border-gray-700 placeholder-gray-400 text-white focus:ring-pink-500 focus:border-pink-500" placeholder="Ketik NIM atau Nama Mahasiswa...">
        </div>
    </div>

    <!-- TABEL DATA -->
    <div class="bg-card rounded-t-xl border-x border-t border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-800 text-gray-300 text-sm uppercase tracking-wide border-b border-gray-700">
                        <th class="px-6 py-4 font-medium w-16 text-center">No</th>
                        <th class="px-6 py-4 font-medium w-40">NIM</th>
                        <th class="px-6 py-4 font-medium">Nama Mahasiswa</th>
                        <th class="px-6 py-4 font-medium w-36 text-center">Status</th>
                        <th class="px-6 py-4 font-medium w-40 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="text-gray-300 divide-y divide-gray-700">
                    <?php 
                    $no = $offset + 1;
                    if (mysqli_num_rows($query) > 0) :
                        while ($row = mysqli_fetch_assoc($query)) : 
                            $badgeColor = 'bg-red-500/20 text-red-400 border-red-500/50';
                            if ($row['status'] == 'Diterima') $badgeColor = 'bg-green-500/20 text-green-400 border-green-500/50';
                    ?>
                        <tr class="hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4 text-center"><?= $no++ ?></td>
                            <td class="px-6 py-4 text-pink-400 font-medium"><?= htmlspecialchars($row['nim']) ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full border <?= $badgeColor ?>">
                                    <?= htmlspecialchars($row['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center space-x-3">
                                <a href="?page=mahasiswa&aksi=edit&id=<?= $row['id_mahasiswa'] ?>" class="text-blue-400 hover:text-blue-300 transition-colors">Edit</a>
                                <a href="?page=mahasiswa&aksi=hapus&id=<?= $row['id_mahasiswa'] ?>" onclick="return confirm('Apakah Anda yakin?');" class="text-red-400 hover:text-red-300 transition-colors">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500 italic">Data tidak ditemukan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- KONTROL PAGINATION -->
    <div id="paginationContainer" class="flex flex-col md:flex-row justify-between items-center p-4 bg-gray-800/30 rounded-b-xl border border-gray-700 gap-4">
        <span class="text-sm text-gray-400">
            Menampilkan <span class="font-semibold text-white"><?= $totalData > 0 ? $offset + 1 : 0 ?></span> 
            sampai <span class="font-semibold text-white"><?= min($offset + $limit, $totalData) ?></span> 
            dari <span class="font-semibold text-white"><?= $totalData ?></span> data
        </span>
        
        <?php if ($totalPages > 1): ?>
        <ul class="inline-flex -space-x-px text-sm">
            <li>
                <a href="?page=mahasiswa&hal=<?= max(1, $halaman - 1) ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>" 
                   class="flex items-center justify-center px-3 h-8 leading-tight bg-darker border border-e-0 border-gray-700 rounded-s-lg hover:bg-gray-800 hover:text-white text-gray-400 <?= $halaman <= 1 ? 'pointer-events-none opacity-50' : '' ?>">&lt; Sebelumnya</a>
            </li>
            <?php for($i = 1; $i <= $totalPages; $i++): ?>
            <li>
                <a href="?page=mahasiswa&hal=<?= $i ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>" 
                   class="flex items-center justify-center px-3 h-8 leading-tight border border-gray-700 <?= $halaman == $i ? 'text-pink-400 bg-gray-800 font-bold' : 'bg-darker hover:bg-gray-800 hover:text-white text-gray-400' ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
            <li>
                <a href="?page=mahasiswa&hal=<?= min($totalPages, $halaman + 1) ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>" 
                   class="flex items-center justify-center px-3 h-8 leading-tight bg-darker border border-gray-700 rounded-e-lg hover:bg-gray-800 hover:text-white text-gray-400 <?= $halaman >= $totalPages ? 'pointer-events-none opacity-50' : '' ?>">Selanjutnya &gt;</a>
            </li>
        </ul>
        <?php endif; ?>
    </div>

    <!-- SCRIPT AJAX UNTUK LIVE SEARCH & LIVE LIMIT -->
    <script>
        let timeout = null;
        const searchInput = document.getElementById('searchInput');
        const limitSelect = document.getElementById('limitSelect');
        const tableBody = document.getElementById('tableBody');
        const paginationContainer = document.getElementById('paginationContainer');

        function fetchLiveResults() {
            const searchVal = encodeURIComponent(searchInput.value);
            const limitVal = limitSelect.value;
            
            // Indikator loading sementara
            tableBody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-gray-400 italic"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Mencari data...</td></tr>';
            
            // Lakukan request AJAX ke halaman ini sendiri
            fetch(`?page=mahasiswa&search=${searchVal}&limit=${limitVal}`)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    // Ganti isi tabel dengan hasil dari server
                    const newTbody = doc.getElementById('tableBody');
                    if(newTbody) tableBody.innerHTML = newTbody.innerHTML;
                    
                    // Ganti struktur pagination
                    const newPagination = doc.getElementById('paginationContainer');
                    if(newPagination && paginationContainer) {
                        paginationContainer.innerHTML = newPagination.innerHTML;
                    }
                });
        }

        // Listener saat mengetik dengan debounce 300ms
        searchInput.addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(fetchLiveResults, 300);
        });

        // Listener saat mengubah batas data
        limitSelect.addEventListener('change', fetchLiveResults);
    </script>
<?php endif; ?>