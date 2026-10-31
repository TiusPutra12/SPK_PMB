<?php

require_once __DIR__ . '/../models/MahasiswaModel.php';
require_once __DIR__ . '/../models/KriteriaModel.php';

class MahasiswaController {
    private $mahasiswaModel;
    private $kriteriaModel;

    public function __construct() {
        $this->mahasiswaModel = new MahasiswaModel();
        $this->kriteriaModel = new KriteriaModel();
    }

    public function handleRequest() {
        $aksi = isset($_GET['aksi']) ? $_GET['aksi'] : '';

        if (isset($_POST['simpan_tambah'])) {
            $mhsData = [
                'nim'      => $_POST['nim'] ?? '',
                'nama'     => $_POST['nama'] ?? '',
                'status'   => $_POST['status'] ?? 'Ditolak',
                'golongan' => $_POST['golongan'] ?? 'Umum'
            ];
            $detailData = [
                'nik'                  => $_POST['nik'] ?? '',
                'jenis_kelamin'        => $_POST['jenis_kelamin'] ?? 'L',
                'tempat_tanggal_lahir' => $_POST['tempat_tanggal_lahir'] ?? '',
                'asal_sekolah'         => $_POST['asal_sekolah'] ?? '',
                'jurusan_asal'         => $_POST['jurusan_asal'] ?? '',
                'no_hp'                => $_POST['no_hp'] ?? '',
                'email'                => $_POST['email'] ?? '',
                'alamat'               => $_POST['alamat'] ?? '',
                'penghasilan_orangtua' => $_POST['penghasilan_orangtua'] ?? ''
            ];
            $nilaiKriteria = [
                'kriteria_sub'   => $_POST['kriteria_sub'] ?? [],
                'kriteria_nilai' => $_POST['kriteria_nilai'] ?? []
            ];

            $this->mahasiswaModel->createMahasiswa($mhsData, $detailData, $nilaiKriteria);
            header("Location: mahasiswa.php?page=mahasiswa");
            exit;
        }

        if (isset($_POST['simpan_edit'])) {
            $id = (int)($_POST['id_mahasiswa'] ?? 0);
            $mhsData = [
                'nim'      => $_POST['nim'] ?? '',
                'nama'     => $_POST['nama'] ?? '',
                'status'   => $_POST['status'] ?? 'Ditolak',
                'golongan' => $_POST['golongan'] ?? 'Umum'
            ];
            $detailData = [
                'nik'                  => $_POST['nik'] ?? '',
                'jenis_kelamin'        => $_POST['jenis_kelamin'] ?? 'L',
                'tempat_tanggal_lahir' => $_POST['tempat_tanggal_lahir'] ?? '',
                'asal_sekolah'         => $_POST['asal_sekolah'] ?? '',
                'jurusan_asal'         => $_POST['jurusan_asal'] ?? '',
                'no_hp'                => $_POST['no_hp'] ?? '',
                'email'                => $_POST['email'] ?? '',
                'alamat'               => $_POST['alamat'] ?? '',
                'penghasilan_orangtua' => $_POST['penghasilan_orangtua'] ?? ''
            ];
            $nilaiKriteria = [
                'kriteria_sub'   => $_POST['kriteria_sub'] ?? [],
                'kriteria_nilai' => $_POST['kriteria_nilai'] ?? []
            ];

            $this->mahasiswaModel->updateMahasiswa($id, $mhsData, $detailData, $nilaiKriteria);
            header("Location: mahasiswa.php?page=mahasiswa");
            exit;
        }

        if ($aksi === 'hapus' && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $this->mahasiswaModel->deleteMahasiswa($id);
            header("Location: mahasiswa.php?page=mahasiswa");
            exit;
        }
    }

    public function getKriteriaData() {
        return $this->kriteriaModel->getAllKriteriaWithSubs();
    }

    public function getMahasiswaListWithWP() {
        $pendaftar_list = $this->mahasiswaModel->getAllMahasiswaWithDetail();
        $nilaiData = $this->mahasiswaModel->getRawNilaiMahasiswa();
        $nilai_per_mhs = $nilaiData['formatted'];
        $kriteria_db = $this->kriteriaModel->getAllKriteriaWithSubs();

        $total_bobot = array_sum(array_column($kriteria_db, 'bobot'));
        $kriteria_list = [];
        foreach ($kriteria_db as $idK => $k) {
            $wj = ($total_bobot > 0) ? ($k['bobot'] / $total_bobot) : 0;
            $kriteria_list[$idK] = $k;
            $kriteria_list[$idK]['wj'] = (strtolower($k['jenis']) === 'cost') ? (-$wj) : $wj;
        }

        $vektor_s_list = [];
        $total_vektor_s = 0;

        foreach ($pendaftar_list as $id_mhs => $mhs) {
            $s_val = 1.0;
            $has_nilai = false;

            if (!empty($nilai_per_mhs[$id_mhs])) {
                $pendaftar_list[$id_mhs]['nilai']['kriteria'] = array_values($nilai_per_mhs[$id_mhs]);
                foreach ($kriteria_list as $id_k => $krit) {
                    $val = $nilai_per_mhs[$id_mhs][$id_k]['nilai'] ?? 1.0;
                    if ($val <= 0) $val = 1.0;
                    $wj = $krit['wj'];
                    $s_val *= pow($val, $wj);
                    $has_nilai = true;
                }
            } else {
                foreach ($kriteria_list as $id_k => $krit) {
                    $pendaftar_list[$id_mhs]['nilai']['kriteria'][] = [
                        'kode'       => 'C' . $id_k,
                        'nama'       => $krit['nama_kriteria'],
                        'bobot'      => $krit['bobot'] . '%',
                        'nilai'      => 0,
                        'keterangan' => 'Belum dinilai'
                    ];
                }
                $s_val = 0;
            }

            $vektor_s_list[$id_mhs] = $has_nilai ? $s_val : 0;
            $total_vektor_s += $vektor_s_list[$id_mhs];
            $pendaftar_list[$id_mhs]['nilai']['vektor_s'] = number_format($vektor_s_list[$id_mhs], 4);
        }

        $ranking_temp = [];
        foreach ($pendaftar_list as $id_mhs => $mhs) {
            $v_val = ($total_vektor_s > 0) ? ($vektor_s_list[$id_mhs] / $total_vektor_s) : 0;
            $pendaftar_list[$id_mhs]['nilai']['vektor_v'] = number_format($v_val, 4);
            $ranking_temp[$id_mhs] = $v_val;
        }

        arsort($ranking_temp);
        $rank = 1;
        foreach ($ranking_temp as $id_mhs => $v) {
            $pendaftar_list[$id_mhs]['nilai']['peringkat'] = ($v > 0) ? $rank++ : '-';
        }

        return [
            'pendaftar_list' => array_values($pendaftar_list),
            'kriteria_db'    => $kriteria_db,
            'nilai_raw'      => $nilaiData['raw']
        ];
    }

    public function getMahasiswaDetail($id) {
        return $this->mahasiswaModel->getMahasiswaById($id);
    }
}

