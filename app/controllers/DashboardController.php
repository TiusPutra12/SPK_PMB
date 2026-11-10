<?php

require_once __DIR__ . '/../models/DashboardModel.php';

class DashboardController {
    private $model;

    public function __construct($koneksi) {
        $this->model = new DashboardModel($koneksi);
    }

    public function handleRequest() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi'])) {
            $aksi = $_POST['aksi'];

            if ($aksi === 'tambah_prodi') {
                $nama = trim($_POST['nama_program'] ?? '');
                $res = $this->model->addProgramStudi($nama);
            } elseif ($aksi === 'edit_prodi') {
                $id   = (int)($_POST['id_program'] ?? 0);
                $nama = trim($_POST['nama_program'] ?? '');
                $res = $this->model->updateProgramStudi($id, $nama);
            } elseif ($aksi === 'hapus_prodi') {
                $id = (int)($_POST['id_program'] ?? 0);
                $res = $this->model->deleteProgramStudi($id);
            } else {
                $res = ['success' => false, 'message' => 'Aksi tidak dikenal.'];
            }

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode($res);
                exit;
            }

            if ($res['success']) {
                $_SESSION['success_msg'] = $res['message'];
            } else {
                $_SESSION['error_msg'] = $res['message'];
            }
            header("Location: dashboard.php?page=beranda");
            exit;
        }
    }

    public function getDashboardData() {
        $kriteria_list = $this->model->getKriteria();
        $prodi_list    = $this->model->getProdiList();
        $pendaftar_list = $this->model->getPendaftarList();
        $nilai_per_mhs = $this->model->getNilaiMatrix();

        $vektor_s_list = [];
        $total_vektor_s = 0;

        foreach ($pendaftar_list as $id_mhs => $mhs) {
            $s_val = 1.0;
            $has_nilai = false;

            if (!empty($nilai_per_mhs[$id_mhs])) {
                foreach ($kriteria_list as $id_k => $krit) {
                    $val = $nilai_per_mhs[$id_mhs][$id_k] ?? 1.0;
                    if ($val <= 0) $val = 1.0; 
                    $wj = $krit['wj'];
                    $s_val *= pow($val, $wj);
                    $has_nilai = true;
                }
            } else {
                $s_val = 0;
            }

            $vektor_s_list[$id_mhs] = $has_nilai ? $s_val : 0;
            $total_vektor_s += $vektor_s_list[$id_mhs];
        }

        $ranking_temp = [];
        foreach ($pendaftar_list as $id_mhs => $mhs) {
            $v_val = ($total_vektor_s > 0) ? ($vektor_s_list[$id_mhs] / $total_vektor_s) : 0;
            $pendaftar_list[$id_mhs]['vektor_v'] = $v_val;
            $ranking_temp[$id_mhs] = $v_val;
        }

        arsort($ranking_temp);
        $rank = 1;
        foreach ($ranking_temp as $id_mhs => $v) {
            $pendaftar_list[$id_mhs]['peringkat'] = ($v > 0) ? $rank++ : 999999;
        }

        $tahun_stat = [];
        $total_diterima = 0;

        foreach ($pendaftar_list as $id_mhs => $mhs) {
            $thn = $mhs['tahun'];
            if (!isset($tahun_stat[$thn])) {
                $tahun_stat[$thn] = ['total' => 0, 'diterima' => 0];
            }
            $tahun_stat[$thn]['total']++;

            $is_diterima = ($mhs['status'] === 'Diterima');
            if ($is_diterima) {
                $tahun_stat[$thn]['diterima']++;
                $total_diterima++;
            }
        }

        ksort($tahun_stat);
        if (empty($tahun_stat)) {
            $tahun_stat = [
                '2022' => ['total' => 0, 'diterima' => 0],
                '2023' => ['total' => 0, 'diterima' => 0],
                '2024' => ['total' => 0, 'diterima' => 0],
                '2025' => ['total' => 0, 'diterima' => 0],
                '2026' => ['total' => 0, 'diterima' => 0],
            ];
        }

        $chart_labels   = array_keys($tahun_stat);
        $chart_total    = array_column($tahun_stat, 'total');
        $chart_diterima = array_column($tahun_stat, 'diterima');

        return [
            'kriteria_list'  => $kriteria_list,
            'prodi_list'     => $prodi_list,
            'pendaftar_list' => $pendaftar_list,
            'tahun_stat'     => $tahun_stat,
            'total_diterima' => $total_diterima,
            'chart_labels'   => $chart_labels,
            'chart_total'    => $chart_total,
            'chart_diterima' => $chart_diterima
        ];
    }
}
?>