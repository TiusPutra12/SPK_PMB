<?php
// app/pages/analisis.php
require_once __DIR__ . '/../controllers/AnalisisController.php';

$nbCtrl = new AnalisisController();
$nbCtrl->handleRequest();

$menunggu_count = $nbCtrl->getPendingCount();
$hasil_analisis = $_SESSION['nb_results'] ?? [];

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
        <?php if (isset($_SESSION['nb_success'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'Analisis Selesai!',
                text: '<?= addslashes($_SESSION['nb_success']) ?>',
                confirmButtonColor: '#9333ea'
            });
            <?php unset($_SESSION['nb_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['nb_error'])): ?>
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: '<?= addslashes($_SESSION['nb_error']) ?>',
                confirmButtonColor: '#e11d48'
            });
            <?php unset($_SESSION['nb_error']); ?>
        <?php endif; ?>
    });
</script>

<main class="p-8 flex-1 overflow-y-auto">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">Analisis Klasifikasi Naive Bayes</h2>
            <p class="text-sm text-gray-500 mt-1">Sistem pembuat keputusan kelulusan otomatis berdasarkan probabilitas historis.</p>
        </div>
    </div>

    <!-- Panel Eksekusi -->
    <div class="bg-white rounded-2xl border border-purple-200 shadow-sm overflow-hidden mb-8">
        <div class="p-8 flex flex-col md:flex-row items-center justify-between gap-6 bg-gradient-to-r from-purple-50 to-pink-50">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 rounded-full bg-white shadow-md flex items-center justify-center border-2 border-purple-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Data Menunggu Analisis</h3>
                    <p class="text-purple-600 font-semibold mt-1">Terdapat <span class="text-2xl font-black bg-white px-2 py-0.5 rounded shadow-sm mx-1"><?= $menunggu_count ?></span> pendaftar baru.</p>
                </div>
            </div>
            
            <form method="post" action="">
                <button type="submit" name="proses_naive_bayes" class="px-8 py-4 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all flex items-center gap-3 transform hover:-translate-y-1" <?= $menunggu_count == 0 ? 'disabled style="opacity:0.6; cursor:not-allowed;"' : '' ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    Jalankan Prediksi Naive Bayes
                </button>
            </form>
        </div>
    </div>

    <!-- Tabel Hasil Analisis -->
    <?php if (!empty($hasil_analisis)): ?>
    <div class="table-card w-full animate-[fadeInDetail_0.5s_ease-out]">
        <div class="table-card-header bg-emerald-50 border-b border-emerald-100">
            <div class="table-card-title text-emerald-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>Hasil Klasifikasi & Ranking Kelulusan (Baru Saja Diproses)</span>
            </div>
        </div>

        <div class="table-scroll-container">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th class="w-20 text-center">RANKING</th>
                        <th>NAMA MAHASISWA</th>
                        <th class="w-48 text-center">PROBABILITAS (DITERIMA)</th>
                        <th class="w-40 text-center">KEPUTUSAN SISTEM</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $rank = 1; foreach ($hasil_analisis as $hasil): ?>
                        <tr class="data-row">
                            <td class="text-center font-black text-gray-500">#<?= $rank++ ?></td>
                            <td class="font-semibold text-gray-900"><?= htmlspecialchars($hasil['nama']) ?></td>
                            <td class="text-center">
                                <span class="font-mono font-bold <?= $hasil['prob_diterima'] >= 50 ? 'text-emerald-600' : 'text-red-500' ?>">
                                    <?= $hasil['prob_diterima'] ?> %
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="status-pill <?= $hasil['keputusan'] === 'Diterima' ? 'diterima' : 'tidak-diterima' ?>">
                                    <?= $hasil['keputusan'] ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="p-4 bg-gray-50 border-t border-gray-100 text-sm text-gray-500 text-center">
            Catatan: Status kelulusan ini telah otomatis diperbarui di menu Data Mahasiswa.
        </div>
    </div>
    <?php endif; ?>

</main>
</div> 
</body>
</html>