<?php
// app/models/AnalisisModel.php
require_once __DIR__ . '/../includes/koneksi.php';

class AnalisisModel {
    private $db;

    public function __construct() {
        global $koneksi;
        $this->db = $koneksi;
    }

    private function getDatasetByStatus($status_array) {
        $status_in = "'" . implode("','", $status_array) . "'";
        $query = mysqli_query($this->db, "
            SELECT 
                m.id_mahasiswa, m.nama_mahasiswa, m.status, m.golongan,
                d.penghasilan_orangtua
            FROM mahasiswa m
            LEFT JOIN data_mahasiswa d ON m.id_mahasiswa = d.id_mahasiswa
            WHERE m.status IN ($status_in)
        ");

        $dataset = [];
        while ($row = mysqli_fetch_assoc($query)) {
            $id = $row['id_mahasiswa'];
            $features = [
                'ekonomi' => $row['penghasilan_orangtua'] ?: 'Kosong',
                'golongan' => $row['golongan']
            ];

            // KUNCI PERBAIKAN: Klasifikasi menggunakan id_sub (Opsi Kategori), bukan skor desimal mentah
            $qNilai = mysqli_query($this->db, "SELECT id_kriteria, id_sub FROM nilai WHERE id_mahasiswa = '$id'");
            while ($n = mysqli_fetch_assoc($qNilai)) {
                $features['C' . $n['id_kriteria']] = 'SUB_' . $n['id_sub'];
            }

            $dataset[] = [
                'id' => $id,
                'nama' => $row['nama_mahasiswa'],
                'status' => $row['status'],
                'features' => $features
            ];
        }
        return $dataset;
    }

    public function jalankanKlasifikasi() {
        $trainingData = $this->getDatasetByStatus(['Diterima', 'Ditolak']);
        $testingData = $this->getDatasetByStatus(['Menunggu']);

        if (empty($testingData)) return ['status' => 'empty', 'message' => 'Tidak ada data pendaftar yang menunggu analisis.'];
        if (empty($trainingData)) return ['status' => 'error', 'message' => 'Data latih kosong. Sistem membutuhkan minimal 1 data mahasiswa Diterima & Ditolak.'];

        $jml_latih = count($trainingData);
        $jml_diterima = 0;
        $jml_ditolak = 0;

        foreach ($trainingData as $t) {
            if ($t['status'] === 'Diterima') $jml_diterima++;
            else if ($t['status'] === 'Ditolak') $jml_ditolak++;
        }

        $prior_diterima = $jml_diterima / $jml_latih;
        $prior_ditolak = $jml_ditolak / $jml_latih;

        $hasil_klasifikasi = [];

        foreach ($testingData as $test) {
            $prob_diterima = $prior_diterima;
            $prob_ditolak = $prior_ditolak;

            foreach ($test['features'] as $key => $val) {
                $match_diterima = 0;
                $match_ditolak = 0;

                foreach ($trainingData as $train) {
                    if (isset($train['features'][$key]) && $train['features'][$key] == $val) {
                        if ($train['status'] === 'Diterima') $match_diterima++;
                        else if ($train['status'] === 'Ditolak') $match_ditolak++;
                    }
                }

                // Laplace Smoothing untuk menutupi probabilitas nol
                $laplace_diterima = ($match_diterima + 1) / ($jml_diterima + 3);
                $laplace_ditolak = ($match_ditolak + 1) / ($jml_ditolak + 3);

                $prob_diterima *= $laplace_diterima;
                $prob_ditolak *= $laplace_ditolak;
            }

            $total_prob = $prob_diterima + $prob_ditolak;
            
            // Konversi ke persentase 0-100%
            $persen_diterima = ($total_prob > 0) ? ($prob_diterima / $total_prob) * 100 : 0;

            $keputusan = ($prob_diterima > $prob_ditolak) ? 'Diterima' : 'Ditolak';

            $hasil_klasifikasi[] = [
                'id_mahasiswa' => $test['id'],
                'nama' => $test['nama'],
                'prob_diterima' => round($persen_diterima, 2),
                'keputusan' => $keputusan
            ];

            $id_mhs = $test['id'];
            mysqli_query($this->db, "UPDATE mahasiswa SET status = '$keputusan' WHERE id_mahasiswa = '$id_mhs'");
        }

        // Urutkan (Ranking) dari persentase Diterima tertinggi ke terendah
        usort($hasil_klasifikasi, function($a, $b) {
            return $b['prob_diterima'] <=> $a['prob_diterima'];
        });

        return [
            'status' => 'success',
            'data' => $hasil_klasifikasi,
            'jml_diproses' => count($hasil_klasifikasi)
        ];
    }

    public function getSummaryMenunggu() {
        $q = mysqli_query($this->db, "SELECT COUNT(id_mahasiswa) as total FROM mahasiswa WHERE status = 'Menunggu'");
        return mysqli_fetch_assoc($q)['total'] ?? 0;
    }
}