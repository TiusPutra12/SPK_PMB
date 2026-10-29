<?php
// app/pages/mahasiswa.php
require_once '../includes/koneksi.php'; 
$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : '';

// --- LOGIKA PROSES CRUD ---
/** @var mysqli $koneksi */
if (isset($_POST['simpan_tambah'])) {
    $nim = mysqli_real_escape_string($koneksi, $_POST['nim']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    mysqli_query($koneksi, "INSERT INTO mahasiswa (nim, nama_mahasiswa, status) VALUES ('$nim', '$nama', '$status')");
    header("Location: mahasiswa.php?page=mahasiswa"); exit;
}

if (isset($_POST['simpan_edit'])) {
    $id = $_POST['id_mahasiswa'];
    $nim = mysqli_real_escape_string($koneksi, $_POST['nim']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    mysqli_query($koneksi, "UPDATE mahasiswa SET nim='$nim', nama_mahasiswa='$nama', status='$status' WHERE id_mahasiswa='$id'");
    header("Location: mahasiswa.php?page=mahasiswa"); exit;
}

if ($aksi == 'hapus' && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    mysqli_query($koneksi, "DELETE FROM mahasiswa WHERE id_mahasiswa='$id'");
    header("Location: mahasiswa.php?page=mahasiswa"); exit;
}

// Panggil template utama (Header, Sidebar, Topbar)
include '../includes/head.php'; 

// Cetak CSS buatan teman Anda agar layout-nya berfungsi
/** @var string $style_css */
echo $style_css; 
?>

<!-- Bungkus konten di dalam tag main -->
<main class="p-8 flex-1">
    
    <?php if ($aksi == 'tambah'): ?>
        <!-- =================== FORM TAMBAH =================== -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Tambah Data Mahasiswa</h2>
        </div>
        
        <div class="table-card max-w-2xl">
            <div class="p-6">
                <form action="" method="post" class="space-y-5">
                    <div class="filter-item">
                        <label class="filter-label">NIM</label>
                        <input type="text" name="nim" required class="filter-input w-full" placeholder="Masukkan NIM Mahasiswa">
                    </div>
                    <div class="filter-item">
                        <label class="filter-label">Nama Lengkap</label>
                        <input type="text" name="nama" required class="filter-input w-full" placeholder="Masukkan Nama Lengkap">
                    </div>
                    <div class="filter-item">
                        <label class="filter-label">Status Kelulusan</label>
                        <select name="status" required class="filter-select w-full">
                            <option value="Diterima">Diterima</option>
                            <option value="Ditolak" selected>Ditolak</option>
                        </select>
                    </div>
                    <div class="flex gap-3 pt-5 border-t border-gray-100 mt-6">
                        <button type="submit" name="simpan_tambah" class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white font-medium rounded-lg shadow-sm transition-all">Simpan Data</button>
                        <a href="mahasiswa.php?page=mahasiswa" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-all">Batal</a>
                    </div>
                </form>
            </div>
        </div>

    <?php elseif ($aksi == 'edit'): ?>
        <!-- =================== FORM EDIT =================== -->
        <?php
            /** @var mysqli $koneksi */
            $id = mysqli_real_escape_string($koneksi, $_GET['id']);
            $query_edit = mysqli_query($koneksi, "SELECT * FROM mahasiswa WHERE id_mahasiswa='$id'");
            $data = mysqli_fetch_assoc($query_edit);
        ?>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Edit Data Mahasiswa</h2>
        </div>
        
        <div class="table-card max-w-2xl">
            <div class="p-6">
                <form action="" method="post" class="space-y-5">
                    <input type="hidden" name="id_mahasiswa" value="<?= $data['id_mahasiswa'] ?>">
                    <div class="filter-item">
                        <label class="filter-label">NIM</label>
                        <input type="text" name="nim" value="<?= htmlspecialchars($data['nim']) ?>" required class="filter-input w-full">
                    </div>
                    <div class="filter-item">
                        <label class="filter-label">Nama Lengkap</label>
                        <input type="text" name="nama" value="<?= htmlspecialchars($data['nama_mahasiswa']) ?>" required class="filter-input w-full">
                    </div>
                    <div class="filter-item">
                        <label class="filter-label">Status Kelulusan</label>
                        <select name="status" required class="filter-select w-full">
                            <option value="Diterima" <?= $data['status'] == 'Diterima' ? 'selected' : '' ?>>Diterima</option>
                            <option value="Ditolak" <?= $data['status'] == 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
                        </select>
                    </div>
                    <div class="flex gap-3 pt-5 border-t border-gray-100 mt-6">
                        <button type="submit" name="simpan_edit" class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white font-medium rounded-lg shadow-sm transition-all">Simpan Perubahan</button>
                        <a href="mahasiswa.php?page=mahasiswa" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-all">Batal</a>
                    </div>
                </form>
            </div>
        </div>

    <?php else: ?>
        <!-- =================== TAMPILAN TABEL & LIVE SEARCH =================== -->
        <?php
            /** @var mysqli $koneksi */
            $search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $halaman = isset($_GET['hal']) ? (int)$_GET['hal'] : 1;
            $offset = ($halaman - 1) * $limit;

            $whereClause = "";
            if ($search != '') { $whereClause = "WHERE nim LIKE '%$search%' OR nama_mahasiswa LIKE '%$search%'"; }

            $queryCount = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM mahasiswa $whereClause");
            $totalData = mysqli_fetch_assoc($queryCount)['total'];
            $totalPages = ceil($totalData / $limit);
            $query = mysqli_query($koneksi, "SELECT * FROM mahasiswa $whereClause ORDER BY id_mahasiswa DESC LIMIT $offset, $limit");
        ?>
        
        <div class="flex justify-end mb-6">
            <a href="mahasiswa.php?page=mahasiswa&aksi=tambah" class="px-5 py-2.5 bg-gradient-to-r from-pink-500 to-pink-600 hover:from-pink-600 hover:to-pink-700 text-white font-medium rounded-lg shadow-sm transition-all flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Tambah Mahasiswa
            </a>
        </div>

        <div class="filter-section mb-6">
            <div class="filter-grid" style="grid-template-columns: auto 1fr;">
                <div class="filter-item">
                    <label for="limitSelect" class="filter-label">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                        Tampilkan Data
                    </label>
                    <select id="limitSelect" class="filter-select">
                        <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10 baris</option>
                        <option value="20" <?= $limit == 20 ? 'selected' : '' ?>>20 baris</option>
                        <option value="30" <?= $limit == 30 ? 'selected' : '' ?>>30 baris</option>
                        <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50 baris</option>
                    </select>
                </div>
                
                <!-- KOREKSI: max-w-md digabungkan ke atribut class alih-alih di dalam properti style inline -->
                <div class="filter-item max-w-md" style="justify-self: end; width: 100%;">
                    <label for="searchInput" class="filter-label">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        Cari Mahasiswa
                    </label>
                    <input type="text" id="searchInput" value="<?= htmlspecialchars($search) ?>" class="filter-input" placeholder="Ketik NIM atau Nama Mahasiswa...">
                </div>
            </div>
        </div>

        <div class="dashboard-split-container">
            <div class="master-table-col" style="width: 100%;">
                <div class="table-card">
                    <div class="table-card-header">
                        <div class="table-card-title">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            <span>Daftar Mahasiswa Alternatif</span>
                            <span id="counterData" class="table-badge-counter"><?= $totalData ?> Data</span>
                        </div>
                    </div>

                    <div class="table-scroll-container">
                        <table class="custom-table" id="tableMahasiswa">
                            <thead>
                                <tr>
                                    <th class="w-16 text-center">No</th>
                                    <th>NIM</th>
                                    <th>Nama Lengkap</th>
                                    <th class="w-36 text-center">Status</th>
                                    <th class="w-32 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <?php 
                                $no = $offset + 1;
                                if (mysqli_num_rows($query) > 0) :
                                    while ($row = mysqli_fetch_assoc($query)) : 
                                        $status_class = ($row['status'] == 'Ditolak') ? 'tidak-diterima' : 'diterima';
                                ?>
                                    <tr class="data-row">
                                        <td class="text-center font-semibold text-gray-500"><?= $no++ ?></td>
                                        <td class="font-semibold text-gray-900"><?= htmlspecialchars($row['nim']) ?></td>
                                        <td class="font-semibold text-gray-900"><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
                                        <td class="text-center">
                                            <span class="status-pill <?= $status_class ?>"><?= htmlspecialchars($row['status']) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <a href="mahasiswa.php?page=mahasiswa&aksi=edit&id=<?= $row['id_mahasiswa'] ?>" class="p-1.5 text-blue-600 bg-blue-50 rounded hover:bg-blue-100 transition-colors" title="Edit Data">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                                </a>
                                                <a href="mahasiswa.php?page=mahasiswa&aksi=hapus&id=<?= $row['id_mahasiswa'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');" class="p-1.5 text-red-600 bg-red-50 rounded hover:bg-red-100 transition-colors" title="Hapus Data">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; else: ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            Tidak ditemukan data mahasiswa yang sesuai.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div id="paginationContainer" class="p-4 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4 bg-white rounded-b-xl">
                        <span class="text-sm text-gray-500 font-medium">
                            Menampilkan <span class="font-bold text-gray-900"><?= $totalData > 0 ? $offset + 1 : 0 ?></span> 
                            sampai <span class="font-bold text-gray-900"><?= min($offset + $limit, $totalData) ?></span> dari <span class="font-bold text-gray-900"><?= $totalData ?></span> data
                        </span>
                        <?php if ($totalPages > 1): ?>
                        <div class="flex items-center space-x-1">
                            <a href="mahasiswa.php?page=mahasiswa&hal=<?= max(1, $halaman - 1) ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>" class="px-3 py-1.5 text-sm font-medium text-gray-500 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 <?= $halaman <= 1 ? 'pointer-events-none opacity-50' : '' ?>">Sebelumnya</a>
                            
                            <?php for($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="mahasiswa.php?page=mahasiswa&hal=<?= $i ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>" class="px-3 py-1.5 text-sm font-medium border rounded-lg transition-colors <?= $halaman == $i ? 'bg-purple-50 text-purple-700 border-purple-200' : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50' ?>"><?= $i ?></a>
                            <?php endfor; ?>
                            
                            <a href="mahasiswa.php?page=mahasiswa&hal=<?= min($totalPages, $halaman + 1) ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>" class="px-3 py-1.5 text-sm font-medium text-gray-500 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 <?= $halaman >= $totalPages ? 'pointer-events-none opacity-50' : '' ?>">Selanjutnya</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <script>
            let timeout = null;
            const searchInput = document.getElementById('searchInput');
            const limitSelect = document.getElementById('limitSelect');
            const tableBody = document.getElementById('tableBody');
            const paginationContainer = document.getElementById('paginationContainer');
            const counterData = document.getElementById('counterData');

            function fetchLiveResults() {
                const searchVal = encodeURIComponent(searchInput.value);
                const limitVal = limitSelect.value;
                
                tableBody.innerHTML = '<tr><td colspan="5" class="px-6 py-12 text-center text-gray-400 italic"><svg class="animate-spin w-8 h-8 mx-auto text-purple-500 mb-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Mencari data...</td></tr>';
                
                fetch(`mahasiswa.php?page=mahasiswa&search=${searchVal}&limit=${limitVal}`)
                    .then(response => response.text())
                    .then(html => {
                        const parser = new window.DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        
                        const newTbody = doc.getElementById('tableBody');
                        if(newTbody) tableBody.innerHTML = newTbody.innerHTML;
                        
                        const newPagination = doc.getElementById('paginationContainer');
                        if(newPagination && paginationContainer) paginationContainer.innerHTML = newPagination.innerHTML;
                        
                        const newCounter = doc.getElementById('counterData');
                        if(newCounter && counterData) counterData.innerHTML = newCounter.innerHTML;
                    });
            }

            searchInput.addEventListener('input', () => { clearTimeout(timeout); timeout = setTimeout(fetchLiveResults, 300); });
            limitSelect.addEventListener('change', fetchLiveResults);
        </script>
    <?php endif; ?>
</main>
</div> 
</body>
</html>