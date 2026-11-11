<?php
// app/models/AnalisisModel.php

class AnalisisModel {
    private $db;

    // KUNCI PERBAIKAN: Tangkap koneksi via Dependency Injection
    public function __construct($koneksi) {
        $this->db = $koneksi;
        $this->ensureNilaiTable();
    }

    public function ensureNilaiTable() {
        $check = @mysqli_query($this->db, "SHOW TABLES LIKE 'nilai'");
        if (!$check || mysqli_num_rows($check) === 0) {
            @mysqli_query($this->db, "
                CREATE TABLE IF NOT EXISTS `nilai` (
                    `id_nilai` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `id_mahasiswa` INT NOT NULL,
                    `id_kriteria` INT NOT NULL,
                    `id_sub` INT NOT NULL,
                    `nilai` FLOAT NULL DEFAULT NULL,
                    INDEX `fk_nilai_mahasiswa`(`id_mahasiswa` ASC),
                    INDEX `fk_nilai_kriteria`(`id_kriteria` ASC),
                    INDEX `fk_nilai_sub`(`id_sub` ASC)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }
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

            // Jika nilai metrik masih kosong di tabel mahasiswa namun ada data di tabel nilai, hitung otomatis
            if ($totalVal === null && $hasNilaiTbl) {
                $qNilaiAll = mysqli_query($this->db, "
                    SELECT n.id_kriteria, n.id_sub, s.nilai as sub_score, k.bobot
                    FROM nilai n
                    LEFT JOIN sub_kriteria s ON n.id_sub = s.id_sub
                    LEFT JOIN kriteria k ON n.id_kriteria = k.id_kriteria
                    WHERE n.id_mahasiswa = '$id'
                ");
                if ($qNilaiAll && mysqli_num_rows($qNilaiAll) > 0) {
                    $totW = 0.0; $totA = 0.0; $sumB = 0.0; $cnt = 0;
                    while ($nr = mysqli_fetch_assoc($qNilaiAll)) {
                        $sScore = (float)($nr['sub_score'] ?? 0);
                        $bScore = (float)($nr['bobot'] ?? 0);
                        $totW += ($sScore * $bScore);
                        $sumB += $bScore;
                        $totA += $sScore;
                        $cnt++;
                    }
                    if ($cnt > 0) {
                        $totalVal = ($sumB > 0) ? round($totW / $sumB, 4) : 0;
                        $tasVal = round($totA, 4);
                        $rskVal = round($totA / $cnt, 4);

                        $updFields = [];
                        if (in_array('total', $cols)) $updFields[] = "total = '$totalVal'";
                        if (in_array('TAS', $cols)) $updFields[] = "TAS = '$tasVal'";
                        elseif (in_array('tas', $cols)) $updFields[] = "tas = '$tasVal'";
                        if (in_array('RSK', $cols)) $updFields[] = "RSK = '$rskVal'";
                        elseif (in_array('rsk', $cols)) $updFields[] = "rsk = '$rskVal'";

                        if (!empty($updFields)) {
                            @mysqli_query($this->db, "UPDATE mahasiswa SET " . implode(', ', $updFields) . " WHERE {$idCol} = '$id'");
                        }
                    }
                }
            }

            // Ekstrak nilai per kriteria (C1-C5) dari tabel nilai
            if ($hasNilaiTbl) {
                $qNilai = mysqli_query($this->db, "SELECT id_kriteria, id_sub FROM nilai WHERE id_mahasiswa = '$id'");
                if ($qNilai) {
                    while ($n = mysqli_fetch_assoc($qNilai)) {
                        $features['C' . $n['id_kriteria']] = 'SUB_' . $n['id_sub'];
                    }
                }
            }

            // Kategorisasi fitur metrik SPK berdasarkan skala penerimaan
            if ($totalVal !== null) {
                $features['total_spk'] = ($totalVal >= 0.55) ? 'Tinggi' : (($totalVal >= 0.40) ? 'Sedang' : 'Rendah');
            }
            if ($rskVal !== null) {
                $features['rsk'] = ($rskVal >= 0.50) ? 'Tinggi' : (($rskVal >= 0.36) ? 'Sedang' : 'Rendah');
            }
            if ($tasVal !== null) {
                $features['tas'] = ($tasVal >= 2.5) ? 'Tinggi' : (($tasVal >= 1.8) ? 'Sedang' : 'Rendah');
            }

            // Fallback: Jika tabel nilai belum terisi namun total_spk sudah ada di tabel mahasiswa
            if (empty($features['C1']) && $totalVal !== null) {
                $lvl = $features['total_spk'] ?? 'Sedang';
                if ($lvl === 'Tinggi') {
                    $features['C1'] = 'SUB_3'; $features['C2'] = 'SUB_5'; $features['C3'] = 'SUB_8'; $features['C4'] = 'SUB_12'; $features['C5'] = 'SUB_15';
                } elseif ($lvl === 'Sedang') {
                    $features['C1'] = 'SUB_2'; $features['C2'] = 'SUB_4'; $features['C3'] = 'SUB_7'; $features['C4'] = 'SUB_11'; $features['C5'] = 'SUB_14';
                } else {
                    $features['C1'] = 'SUB_1'; $features['C2'] = 'SUB_4'; $features['C3'] = 'SUB_7'; $features['C4'] = 'SUB_10'; $features['C5'] = 'SUB_13';
                }
            }

            if (empty($features)) {
                $features['jalur_seleksi'] = 'Reguler';
            }

            $dataset[] = [
                'id'       => $id,
                'nama'     => $row['nama_mahasiswa'],
                'status'   => $row['status'],
                'total'    => $totalVal,
                'tas'      => $tasVal,
                'rsk'      => $rskVal,
                'features' => $features
            ];
        }
        return $dataset;
    }

    public function getBaselineTrainingData() {
        $subScores = [
            1 => 0.25, 2 => 0.5, 3 => 0.75, // C1
            4 => 0.2,  5 => 0.4, 6 => 0.8,  // C2
            7 => 0.2,  8 => 0.4, 9 => 0.8,  // C3
            10 => 0.3, 11 => 0.6, 12 => 0.9, // C4
            13 => 0.25, 14 => 0.5, 15 => 0.75 // C5
        ];
        $weights = [1 => 25, 2 => 20, 3 => 15, 4 => 25, 5 => 15];
        $sumW = 100;

        $buildProfile = function($c1, $c2, $c3, $c4, $c5, $status, $name) use ($subScores, $weights, $sumW) {
            $tas = $subScores[$c1] + $subScores[$c2] + $subScores[$c3] + $subScores[$c4] + $subScores[$c5];
            $rsk = $tas / 5;
            $total = ($subScores[$c1]*$weights[1] + $subScores[$c2]*$weights[2] + $subScores[$c3]*$weights[3] + $subScores[$c4]*$weights[4] + $subScores[$c5]*$weights[5]) / $sumW;

            return [
                'id'       => 0,
                'nama'     => $name,
                'status'   => $status,
                'total'    => round($total, 4),
                'tas'      => round($tas, 4),
                'rsk'      => round($rsk, 4),
                'features' => [
                    'C1'        => 'SUB_' . $c1,
                    'C2'        => 'SUB_' . $c2,
                    'C3'        => 'SUB_' . $c3,
                    'C4'        => 'SUB_' . $c4,
                    'C5'        => 'SUB_' . $c5,
                    'total_spk' => ($total >= 0.55) ? 'Tinggi' : (($total >= 0.40) ? 'Sedang' : 'Rendah'),
                    'tas'       => ($tas >= 2.5) ? 'Tinggi' : (($tas >= 1.8) ? 'Sedang' : 'Rendah'),
                    'rsk'       => ($rsk >= 0.50) ? 'Tinggi' : (($rsk >= 0.36) ? 'Sedang' : 'Rendah')
                ]
            ];
        };

        return [
            // --- KELOMPOK DITERIMA ---
            $buildProfile(3, 6, 9, 12, 15, 'Diterima', 'Latih Diterima 1 (Sangat Baik)'),
            $buildProfile(3, 5, 8, 12, 15, 'Diterima', 'Latih Diterima 2 (Tinggi Nasional)'),
            $buildProfile(3, 5, 8, 12, 14, 'Diterima', 'Latih Diterima 3 (Tinggi Teratur)'),
            $buildProfile(3, 4, 7, 12, 14, 'Diterima', 'Latih Diterima 4 (Akademik Unggul)'),
            $buildProfile(3, 5, 7, 11, 15, 'Diterima', 'Latih Diterima 5 (Bahasa Inggris Unggul)'),
            $buildProfile(2, 6, 8, 12, 14, 'Diterima', 'Latih Diterima 6 (Prestasi Internasional)'),
            $buildProfile(2, 5, 8, 12, 15, 'Diterima', 'Latih Diterima 7 (TKA & Bahasa Tinggi)'),
            $buildProfile(3, 4, 8, 11, 14, 'Diterima', 'Latih Diterima 8 (Rapor & Prestasi Baik)'),
            $buildProfile(2, 5, 7, 12, 14, 'Diterima', 'Latih Diterima 9 (TKA Kuat)'),
            $buildProfile(3, 4, 7, 11, 15, 'Diterima', 'Latih Diterima 10 (Rapor & TOEFL Baik)'),
            $buildProfile(2, 5, 8, 11, 14, 'Diterima', 'Latih Diterima 11 (Di Atas Rata-rata)'),
            $buildProfile(3, 5, 7, 12, 13, 'Diterima', 'Latih Diterima 12 (Akademik Solid)'),
            $buildProfile(2, 4, 8, 12, 14, 'Diterima', 'Latih Diterima 13 (TKA & Non-Akademik Kuat)'),
            $buildProfile(3, 4, 7, 11, 14, 'Diterima', 'Latih Diterima 14 (Standar Lolos)'),

            // --- KELOMPOK DITOLAK ---
            $buildProfile(1, 4, 7, 10, 13, 'Ditolak', 'Latih Ditolak 1 (Rendah Semua)'),
            $buildProfile(1, 4, 7, 10, 14, 'Ditolak', 'Latih Ditolak 2 (Rapor & TKA Rendah)'),
            $buildProfile(1, 4, 7, 11, 13, 'Ditolak', 'Latih Ditolak 3 (Rapor Rendah)'),
            $buildProfile(2, 4, 7, 10, 13, 'Ditolak', 'Latih Ditolak 4 (TKA & Inggris Rendah)'),
            $buildProfile(1, 5, 7, 10, 13, 'Ditolak', 'Latih Ditolak 5 (TKA & Rapor Rendah)'),
            $buildProfile(1, 4, 8, 10, 13, 'Ditolak', 'Latih Ditolak 6 (Akademik Kurang)'),
            $buildProfile(2, 4, 7, 10, 14, 'Ditolak', 'Latih Ditolak 7 (TKA Rendah Sedang)'),
            $buildProfile(1, 4, 7, 11, 14, 'Ditolak', 'Latih Ditolak 8 (Rapor di Bawah 70)'),
            $buildProfile(2, 4, 7, 11, 13, 'Ditolak', 'Latih Ditolak 9 (Kurang Memenuhi)'),
            $buildProfile(1, 5, 7, 10, 14, 'Ditolak', 'Latih Ditolak 10 (Prestasi ada tapi Akademik Rendah)'),
            $buildProfile(1, 4, 7, 10, 15, 'Ditolak', 'Latih Ditolak 11 (TKA & Rapor Rendah)'),
            $buildProfile(2, 4, 7, 10, 13, 'Ditolak', 'Latih Ditolak 12 (Di Bawah Standar)')
        ];
    }

    public function jalankanKlasifikasi() {
        $testingData = $this->getDatasetByStatus(['Menunggu']);

        if (empty($testingData)) {
            return [
                'status'  => 'empty',
                'message' => 'Tidak ada data calon mahasiswa berstatus "Menunggu" untuk dianalisis.'
            ];
        }

        $dbTraining = $this->getDatasetByStatus(['Diterima', 'Ditolak']);
        $jml_dit_db = 0;
        $jml_tol_db = 0;
        foreach ($dbTraining as $t) {
            if ($t['status'] === 'Diterima') $jml_dit_db++;
            elseif ($t['status'] === 'Ditolak') $jml_tol_db++;
        }

        $baseline = $this->getBaselineTrainingData();
        $trainingData = array_merge($dbTraining, $baseline);

        $jml_latih = count($trainingData);
        $jml_diterima = 0;
        $jml_ditolak = 0;

        foreach ($trainingData as $t) {
            if ($t['status'] === 'Diterima') $jml_diterima++;
            elseif ($t['status'] === 'Ditolak') $jml_ditolak++;
        }

        $prior_diterima = $jml_diterima / $jml_latih;
        $prior_ditolak = $jml_ditolak / $jml_latih;

        $hasil_klasifikasi = [];
        $idCol = $this->getIdColumn();

        foreach ($testingData as $test) {
            $prob_diterima = $prior_diterima;
            $prob_ditolak = $prior_ditolak;

            foreach ($test['features'] as $key => $val) {
                $match_diterima = 0;
                $match_ditolak = 0;

                foreach ($trainingData as $train) {
                    if (isset($train['features'][$key]) && $train['features'][$key] === $val) {
                        if ($train['status'] === 'Diterima') $match_diterima++;
                        elseif ($train['status'] === 'Ditolak') $match_ditolak++;
                    }
                }

                $laplace_diterima = ($match_diterima + 1) / ($jml_diterima + 3);
                $laplace_ditolak  = ($match_ditolak + 1) / ($jml_ditolak + 3);

                $prob_diterima *= $laplace_diterima;
                $prob_ditolak  *= $laplace_ditolak;
            }

            $total_prob = $prob_diterima + $prob_ditolak;
            $persen_diterima = ($total_prob > 0) ? ($prob_diterima / $total_prob) * 100 : 0;

            $keputusan = ($prob_diterima >= $prob_ditolak) ? 'Diterima' : 'Ditolak';

            $hasil_klasifikasi[] = [
                'id_mahasiswa'  => $test['id'],
                'nama'          => $test['nama'],
                'total'         => $test['total'],
                'tas'           => $test['tas'],
                'rsk'           => $test['rsk'],
                'prob_diterima' => round($persen_diterima, 2),
                'keputusan'     => $keputusan
            ];

            $id_mhs = (int)$test['id'];
            mysqli_query($this->db, "UPDATE mahasiswa SET status = '$keputusan' WHERE {$idCol} = '$id_mhs'");
        }

        usort($hasil_klasifikasi, function($a, $b) {
            return $b['prob_diterima'] <=> $a['prob_diterima'];
        });

        return [
            'status'       => 'success',
            'data'         => $hasil_klasifikasi,
            'jml_diproses' => count($hasil_klasifikasi)
        ];
    }

    public function getSummaryMenunggu() {
        $q = mysqli_query($this->db, "SELECT COUNT(*) as total FROM mahasiswa WHERE status = 'Menunggu'");
        return ($q && ($row = mysqli_fetch_assoc($q))) ? (int)$row['total'] : 0;
    }
}