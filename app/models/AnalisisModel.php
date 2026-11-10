<?php
// app/models/AnalisisModel.php
require_once __DIR__ . '/../includes/koneksi.php';

class AnalisisModel {
    private $db;

    public function __construct() {
        global $koneksi;
        $this->db = $koneksi;
    }

    private function getIdColumn() {
        $checkCol = @mysqli_query($this->db, "SHOW COLUMNS FROM mahasiswa LIKE 'id_data'");
        if ($checkCol && mysqli_num_rows($checkCol) > 0) {
            return 'id_data';
        }
        return 'id_mahasiswa';
    }

    private function getDatasetByStatus($status_array) {
        $status_in = "'" . implode("','", $status_array) . "'";
        $idCol = $this->getIdColumn();

        // Cek kolom yang tersedia di tabel mahasiswa
        $checkCols = @mysqli_query($this->db, "SHOW COLUMNS FROM mahasiswa");
        $cols = [];
        if ($checkCols) {
            while ($c = mysqli_fetch_assoc($checkCols)) {
                $cols[] = $c['Field'];
            }
        }

        $selectFields = ["m.{$idCol} AS id_mahasiswa", "m.nama_mahasiswa", "m.status"];
        if (in_array('total', $cols)) $selectFields[] = "m.total";
        if (in_array('TAS', $cols)) $selectFields[] = "m.TAS";
        elseif (in_array('tas', $cols)) $selectFields[] = "m.tas AS TAS";
        if (in_array('RSK', $cols)) $selectFields[] = "m.RSK";
        elseif (in_array('rsk', $cols)) $selectFields[] = "m.rsk AS RSK";

        $sql = "SELECT " . implode(", ", $selectFields) . " FROM mahasiswa m WHERE m.status IN ($status_in)";
        $query = mysqli_query($this->db, $sql);

        $dataset = [];
        if (!$query) {
            return $dataset;
        }

        $checkNilai = @mysqli_query($this->db, "SHOW TABLES LIKE 'nilai'");
        $hasNilaiTbl = ($checkNilai && mysqli_num_rows($checkNilai) > 0);

        while ($row = mysqli_fetch_assoc($query)) {
            $id = $row['id_mahasiswa'];
            $features = [];

            // Ekstrak fitur dari nilai metrik SPK (total, TAS, RSK)
            $totalVal = isset($row['total']) && is_numeric($row['total']) ? (float)$row['total'] : null;
            $tasVal   = isset($row['TAS']) && is_numeric($row['TAS']) ? (float)$row['TAS'] : null;
            $rskVal   = isset($row['RSK']) && is_numeric($row['RSK']) ? (float)$row['RSK'] : null;

            if ($totalVal !== null) {
                $features['total_spk'] = ($totalVal >= 0.75) ? 'Tinggi' : (($totalVal >= 0.5) ? 'Sedang' : 'Rendah');
            }
            if ($rskVal !== null) {
                $features['rsk'] = ($rskVal >= 0.7) ? 'Tinggi' : (($rskVal >= 0.4) ? 'Sedang' : 'Rendah');
            }
            if ($tasVal !== null) {
                $features['tas'] = ($tasVal >= 3.0) ? 'Tinggi' : (($tasVal >= 1.5) ? 'Sedang' : 'Rendah');
            }

            if ($hasNilaiTbl) {
                $qNilai = mysqli_query($this->db, "SELECT id_kriteria, id_sub FROM nilai WHERE id_mahasiswa = '$id'");
                if ($qNilai) {
                    while ($n = mysqli_fetch_assoc($qNilai)) {
                        $features['C' . $n['id_kriteria']] = 'SUB_' . $n['id_sub'];
                    }
                }
            }

            if (empty($features)) {
                $features['jalur_seleksi'] = 'Reguler';
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

        if ($jml_diterima === 0 || $jml_ditolak === 0) {
            return [
                'status' => 'error',
                'message' => 'Data latih belum seimbang. Sistem membutuhkan minimal 1 data berstatus Diterima dan 1 data berstatus Ditolak sebagai acuan klasifikasi.'
            ];
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
            $idCol = $this->getIdColumn();
            mysqli_query($this->db, "UPDATE mahasiswa SET status = '$keputusan' WHERE {$idCol} = '$id_mhs'");
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
        $q = mysqli_query($this->db, "SELECT COUNT(*) as total FROM mahasiswa WHERE status = 'Menunggu'");
        return ($q && ($row = mysqli_fetch_assoc($q))) ? (int)$row['total'] : 0;
    }
}
