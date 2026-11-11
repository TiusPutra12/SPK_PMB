<?php
// app/controllers/AnalisisController.php
require_once __DIR__ . '/../models/AnalisisModel.php';

class AnalisisController {
    private $analisisModel;

    // KUNCI PERBAIKAN: Terima parameter $koneksi
    public function __construct($koneksi) {
        $this->analisisModel = new AnalisisModel($koneksi); // Teruskan ke Model
    }

    public function handleRequest() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_POST['proses_naive_bayes'])) {
            $hasil = $this->analisisModel->jalankanKlasifikasi();
            
            if ($hasil['status'] === 'success') {
                $_SESSION['nb_success'] = "Berhasil memproses " . $hasil['jml_diproses'] . " data pendaftar menggunakan algoritma Naive Bayes!";
                $_SESSION['nb_results'] = $hasil['data'];
            } else {
                $_SESSION['nb_error'] = $hasil['message'];
            }
            
            header("Location: analisis.php?page=analisis");
            exit;
        }
    }

    public function getPendingCount() {
        return $this->analisisModel->getSummaryMenunggu();
    }
}