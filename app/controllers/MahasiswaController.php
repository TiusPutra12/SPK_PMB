<?php
// app/controllers/MahasiswaController.php
require_once __DIR__ . '/../models/MahasiswaModel.php';
require_once __DIR__ . '/../models/KriteriaModel.php';

class MahasiswaController {
    private $mahasiswaModel;
    private $kriteriaModel;

    public function __construct() {
        $this->mahasiswaModel = new MahasiswaModel();
        $this->kriteriaModel = new KriteriaModel();
    }

    public function getProdiList() {
        return $this->mahasiswaModel->getListProdi();
    }

    public function handleRequest() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $aksi = isset($_GET['aksi']) ? $_GET['aksi'] : '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'tambah_prodi') {
            $nama = trim($_POST['nama_program'] ?? '');
            $res = $this->mahasiswaModel->addProgramStudi($nama);

            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode($res);
                exit;
            }

            if ($res['success']) {
                $_SESSION['success_msg'] = $res['message'];
            } else {
                $_SESSION['error_msg'] = $res['message'];
            }
            $redirect_aksi = isset($_GET['aksi']) ? $_GET['aksi'] : 'tambah';
            header("Location: mahasiswa.php?page=mahasiswa&aksi=" . urlencode($redirect_aksi));
            exit;
        }

        if (isset($_POST['simpan_tambah']) || isset($_POST['simpan_edit'])) {
            // Logika Menggabungkan Input Tempat (Text) dan Tanggal Lahir (Calendar)
            $tmp_lahir = trim($_POST['tempat_lahir'] ?? '');
            $tgl_lahir = trim($_POST['tanggal_lahir'] ?? '');
            
            $ttl = '';
            if ($tmp_lahir !== '' && $tgl_lahir !== '') {
                $ttl = $tmp_lahir . ', ' . $tgl_lahir;
            } elseif ($tmp_lahir !== '') {
                $ttl = $tmp_lahir;
            } elseif ($tgl_lahir !== '') {
                $ttl = $tgl_lahir;
            }

            $mhsData = [
                'nim'      => trim($_POST['nim'] ?? ''),
                'nama'     => $_POST['nama'] ?? '',
                'golongan' => $_POST['golongan'] ?? 'Umum'
            ];
            $detailData = [
                'nik'                  => trim($_POST['nik'] ?? ''),
                'jenis_kelamin'        => $_POST['jenis_kelamin'] ?? 'L',
                'tempat_tanggal_lahir' => $ttl, // Menggunakan gabungan dari Form HTML
                'asal_sekolah'         => $_POST['asal_sekolah'] ?? '',
                'jurusan_asal'         => $_POST['jurusan_asal'] ?? '', // Disambungkan dari Dropdown Program Studi
                'no_hp'                => $_POST['no_hp'] ?? '',
                'email'                => $_POST['email'] ?? '',
                'alamat'               => $_POST['alamat'] ?? '',
                'penghasilan_orangtua' => $_POST['penghasilan_orangtua'] ?? ''
            ];
            $nilaiKriteria = [
                'kriteria_sub'   => $_POST['kriteria_sub'] ?? [],
                'kriteria_nilai' => $_POST['kriteria_nilai'] ?? []
            ];

            if (isset($_POST['simpan_tambah'])) {
                $result = $this->mahasiswaModel->createMahasiswa($mhsData, $detailData, $nilaiKriteria);
                if ($result) {
                    $_SESSION['success_msg'] = "Data pendaftar berhasil ditambahkan!";
                }
            } else {
                $id = (int)($_POST['id_mahasiswa'] ?? 0);
                $mhsData['status'] = $_POST['status'] ?? 'Menunggu';
                $result = $this->mahasiswaModel->updateMahasiswa($id, $mhsData, $detailData, $nilaiKriteria);
                if ($result) {
                    $_SESSION['success_msg'] = "Data calon mahasiswa berhasil diperbarui!";
                }
            }

            if ($result) {
                $jenis = isset($_GET['jenis']) ? $_GET['jenis'] : 'camaba';
                header("Location: mahasiswa.php?page=mahasiswa&jenis=" . urlencode($jenis));
                exit;
            }
        }

        if ($aksi === 'hapus' && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $result = $this->mahasiswaModel->deleteMahasiswa($id);
            if ($result) {
                $_SESSION['success_msg'] = "Data calon mahasiswa berhasil dihapus!";
            }
            $jenis = isset($_GET['jenis']) ? $_GET['jenis'] : 'camaba';
            header("Location: mahasiswa.php?page=mahasiswa&jenis=" . urlencode($jenis));
            exit;
        }
    }

    public function getKriteriaData() {
        return $this->kriteriaModel->getAllKriteriaWithSubs();
    }

    public function getMahasiswaListWithWP() {
        $pendaftar_list = $this->mahasiswaModel->getAllMahasiswaWithDetail();
        return [
            'pendaftar_list' => array_values($pendaftar_list),
            'kriteria_db'    => $this->kriteriaModel->getAllKriteriaWithSubs(),
            'nilai_raw'      => $this->mahasiswaModel->getRawNilaiMahasiswa()['raw']
        ];
    }

    public function getMahasiswaDetail($id) {
        return $this->mahasiswaModel->getMahasiswaById($id);
    }
}