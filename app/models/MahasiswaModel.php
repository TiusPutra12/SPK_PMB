<?php
// app/models/MahasiswaModel.php
require_once __DIR__ . '/../includes/koneksi.php';

class MahasiswaModel {
    private $db;

    public function __construct() {
        global $koneksi;
        $this->db = $koneksi;
    }

    // FITUR BARU: Menarik Data Program Studi sekaligus Auto-Generate Tabel jika belum ada
    public function getListProdi() {
        $q = mysqli_query($this->db, "SELECT nama_prodi FROM prodi ORDER BY id_prodi ASC");
        $res = [];
        if ($q) {
            while ($r = mysqli_fetch_assoc($q)) {
                $res[] = $r['nama_prodi'];
            }
        }
        return $res;
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

    public function getAllMahasiswaWithDetail() {
        $q = mysqli_query($this->db, "
            SELECT 
                m.id_mahasiswa, m.nim, m.nama_mahasiswa, m.status, m.golongan,
                d.nik, d.jenis_kelamin, d.tempat_tanggal_lahir, d.asal_sekolah,
                d.jurusan_asal, d.no_hp, d.email, d.alamat, d.penghasilan_orangtua
            FROM mahasiswa m
            LEFT JOIN data_mahasiswa d ON m.id_mahasiswa = d.id_mahasiswa
            ORDER BY m.id_mahasiswa ASC
        ");

        $list = [];
        if ($q) {
            while ($row = mysqli_fetch_assoc($q)) {
                $id = (int)$row['id_mahasiswa'];
                $nim = trim($row['nim']);
                
                $tahun = "2026";
                if (strlen($nim) >= 2 && is_numeric(substr($nim, 0, 2))) {
                    $tahun = "20" . substr($nim, 0, 2);
                }

                $prodi = !empty($row['jurusan_asal']) && $row['jurusan_asal'] !== '-' ? $row['jurusan_asal'] : '-';
                $jk = "-";
                if ($row['jenis_kelamin'] === 'L') { $jk = "Laki-laki"; } 
                elseif ($row['jenis_kelamin'] === 'P') { $jk = "Perempuan"; }

                $golongan = !empty($row['golongan']) ? $row['golongan'] : 'Umum';
                $status = !empty($row['status']) ? $row['status'] : 'Menunggu';

                $list[$id] = [
                    'id'        => $id,
                    'nama'      => $row['nama_mahasiswa'],
                    'nim'       => $row['nim'],
                    'nisn'      => $row['nim'],
                    'prodi'     => $prodi,
                    'tahun'     => $tahun,
                    'status'    => $status,
                    'golongan'  => $golongan,
                    'identitas' => [
                        'nik'                  => $row['nik'] ?: '-',
                        'jenis_kelamin'        => $jk,
                        'tempat_tanggal_lahir' => $row['tempat_tanggal_lahir'] ?: '-',
                        'asal_sekolah'         => $row['asal_sekolah'] ?: '-',
                        'jurusan_asal'         => $row['jurusan_asal'] ?: '-',
                        'no_hp'                => $row['no_hp'] ?: '-',
                        'email'                => $row['email'] ?: '-',
                        'alamat'               => $row['alamat'] ?: '-',
                        'penghasilan'          => $row['penghasilan_orangtua'] ?: '-',
                        'jalur'                => $golongan
                    ]
                ];
            }
        }
        return $list;
    }

    public function getMahasiswaById($id) {
        $id = (int)$id;
        $q = mysqli_query($this->db, "
            SELECT m.*, d.nik, d.jenis_kelamin, d.tempat_tanggal_lahir, d.asal_sekolah, d.jurusan_asal, d.no_hp, d.email, d.alamat, d.penghasilan_orangtua 
            FROM mahasiswa m 
            LEFT JOIN data_mahasiswa d ON m.id_mahasiswa = d.id_mahasiswa 
            WHERE m.id_mahasiswa='$id'
        ");
        return mysqli_fetch_assoc($q);
    }

    public function getRawNilaiMahasiswa() {
        $q = mysqli_query($this->db, "
            SELECT 
                n.id_mahasiswa, n.id_kriteria, n.nilai AS nilai_input,
                k.nama_kriteria, k.bobot, s.id_sub, s.nama_sub, s.nilai AS bobot_sub
            FROM nilai n
            JOIN kriteria k ON n.id_kriteria = k.id_kriteria
            LEFT JOIN sub_kriteria s ON n.id_sub = s.id_sub
            ORDER BY n.id_mahasiswa ASC, n.id_kriteria ASC
        ");

        $result = ['formatted' => [], 'raw' => []];
        if ($q) {
            while ($n = mysqli_fetch_assoc($q)) {
                $id_mhs = (int)$n['id_mahasiswa'];
                $id_krit = (int)$n['id_kriteria'];
                $val = !is_null($n['nilai_input']) ? (float)$n['nilai_input'] : (float)$n['bobot_sub'];

                $result['formatted'][$id_mhs][$id_krit] = [
                    'kode'       => 'C' . $id_krit,
                    'nama'       => $n['nama_kriteria'],
                    'bobot'      => $n['bobot'] . '%',
                    'nilai'      => $val,
                    'keterangan' => $n['nama_sub'] ?: '-'
                ];

                $result['raw'][$id_mhs][$id_krit] = [
                    'id_sub' => (int)$n['id_sub'],
                    'nilai'  => $val
                ];
            }
        }
        return $result;
    }

    public function createMahasiswa($mhsData, $detailData, $nilaiKriteria = []) {
        $nim = mysqli_real_escape_string($this->db, $mhsData['nim']);
        
        // Validasi Duplikat NIM
        $cekNIM = mysqli_query($this->db, "SELECT nim FROM mahasiswa WHERE nim = '$nim'");
        if (mysqli_num_rows($cekNIM) > 0) {
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['error_msg'] = "Gagal! NIM '$nim' sudah terdaftar.";
            return false;
        }

        // Validasi Duplikat NIK
        $nik = !empty($detailData['nik']) ? mysqli_real_escape_string($this->db, $detailData['nik']) : '-';
        if ($nik !== '-' && $nik !== '') {
            $cekNIK = mysqli_query($this->db, "SELECT nik FROM data_mahasiswa WHERE nik = '$nik'");
            if (mysqli_num_rows($cekNIK) > 0) {
                if (session_status() === PHP_SESSION_NONE) session_start();
                $_SESSION['error_msg'] = "Gagal! NIK '$nik' sudah terdaftar dalam sistem.";
                return false;
            }
        }

        $nama = mysqli_real_escape_string($this->db, $mhsData['nama']);
        $golongan = mysqli_real_escape_string($this->db, $mhsData['golongan'] ?? 'Umum');
        $status = 'Menunggu';

        $qMhs = mysqli_query($this->db, "INSERT INTO mahasiswa (nim, nama_mahasiswa, status, golongan) VALUES ('$nim', '$nama', '$status', '$golongan')");
        if (!$qMhs) return false;

        $id_new = mysqli_insert_id($this->db);

        $jk = !empty($detailData['jenis_kelamin']) ? mysqli_real_escape_string($this->db, $detailData['jenis_kelamin']) : 'L';
        $ttl = !empty($detailData['tempat_tanggal_lahir']) ? mysqli_real_escape_string($this->db, $detailData['tempat_tanggal_lahir']) : '-';
        $sekolah = !empty($detailData['asal_sekolah']) ? mysqli_real_escape_string($this->db, $detailData['asal_sekolah']) : '-';
        $jurusan = !empty($detailData['jurusan_asal']) ? mysqli_real_escape_string($this->db, $detailData['jurusan_asal']) : '-';
        $hp = !empty($detailData['no_hp']) ? mysqli_real_escape_string($this->db, $detailData['no_hp']) : '-';
        $email = !empty($detailData['email']) ? mysqli_real_escape_string($this->db, $detailData['email']) : '-';
        $alamat = !empty($detailData['alamat']) ? mysqli_real_escape_string($this->db, $detailData['alamat']) : '-';
        $penghasilan = !empty($detailData['penghasilan_orangtua']) ? mysqli_real_escape_string($this->db, $detailData['penghasilan_orangtua']) : '-';

        $qDetail = mysqli_query($this->db, "
            INSERT INTO data_mahasiswa (id_mahasiswa, nik, jenis_kelamin, tempat_tanggal_lahir, asal_sekolah, jurusan_asal, no_hp, email, alamat, penghasilan_orangtua, golongan)
            VALUES ('$id_new', '$nik', '$jk', '$ttl', '$sekolah', '$jurusan', '$hp', '$email', '$alamat', '$penghasilan', '$golongan')
        ");

        if (!$qDetail) {
             mysqli_query($this->db, "DELETE FROM mahasiswa WHERE id_mahasiswa = '$id_new'");
             if (session_status() === PHP_SESSION_NONE) session_start();
             $_SESSION['error_msg'] = "Gagal menyimpan detail mahasiswa! (Error DB)";
             return false;
        }

        $this->saveNilaiMahasiswa($id_new, $nilaiKriteria);
        return $id_new;
    }

    public function updateMahasiswa($id, $mhsData, $detailData, $nilaiKriteria = []) {
        $id = (int)$id;
        $nim = mysqli_real_escape_string($this->db, $mhsData['nim']);
        
        // Validasi Duplikat NIM saat Edit (Abaikan ID diri sendiri)
        $cekNIM = mysqli_query($this->db, "SELECT nim FROM mahasiswa WHERE nim = '$nim' AND id_mahasiswa != '$id'");
        if (mysqli_num_rows($cekNIM) > 0) {
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['error_msg'] = "Gagal Edit! NIM '$nim' sudah dipakai oleh pendaftar lain.";
            return false;
        }

        // Validasi Duplikat NIK saat Edit
        $nik = !empty($detailData['nik']) ? mysqli_real_escape_string($this->db, $detailData['nik']) : '-';
        if ($nik !== '-' && $nik !== '') {
            $cekNIK = mysqli_query($this->db, "SELECT nik FROM data_mahasiswa WHERE nik = '$nik' AND id_mahasiswa != '$id'");
            if (mysqli_num_rows($cekNIK) > 0) {
                if (session_status() === PHP_SESSION_NONE) session_start();
                $_SESSION['error_msg'] = "Gagal Edit! NIK '$nik' sudah terdaftar pada pengguna lain.";
                return false;
            }
        }

        $nama = mysqli_real_escape_string($this->db, $mhsData['nama']);
        $golongan = mysqli_real_escape_string($this->db, $mhsData['golongan'] ?? 'Umum');
        $status = mysqli_real_escape_string($this->db, $mhsData['status'] ?? 'Menunggu');

        mysqli_query($this->db, "UPDATE mahasiswa SET nim='$nim', nama_mahasiswa='$nama', status='$status', golongan='$golongan' WHERE id_mahasiswa='$id'");

        $jk = !empty($detailData['jenis_kelamin']) ? mysqli_real_escape_string($this->db, $detailData['jenis_kelamin']) : 'L';
        $ttl = !empty($detailData['tempat_tanggal_lahir']) ? mysqli_real_escape_string($this->db, $detailData['tempat_tanggal_lahir']) : '-';
        $sekolah = !empty($detailData['asal_sekolah']) ? mysqli_real_escape_string($this->db, $detailData['asal_sekolah']) : '-';
        $jurusan = !empty($detailData['jurusan_asal']) ? mysqli_real_escape_string($this->db, $detailData['jurusan_asal']) : '-';
        $hp = !empty($detailData['no_hp']) ? mysqli_real_escape_string($this->db, $detailData['no_hp']) : '-';
        $email = !empty($detailData['email']) ? mysqli_real_escape_string($this->db, $detailData['email']) : '-';
        $alamat = !empty($detailData['alamat']) ? mysqli_real_escape_string($this->db, $detailData['alamat']) : '-';
        $penghasilan = !empty($detailData['penghasilan_orangtua']) ? mysqli_real_escape_string($this->db, $detailData['penghasilan_orangtua']) : '-';

        $cek = mysqli_query($this->db, "SELECT id_data FROM data_mahasiswa WHERE id_mahasiswa='$id'");
        if ($cek && mysqli_num_rows($cek) > 0) {
            mysqli_query($this->db, "
                UPDATE data_mahasiswa SET 
                    nik='$nik', jenis_kelamin='$jk', tempat_tanggal_lahir='$ttl', 
                    asal_sekolah='$sekolah', jurusan_asal='$jurusan', no_hp='$hp', email='$email', alamat='$alamat',
                    penghasilan_orangtua='$penghasilan', golongan='$golongan'
                WHERE id_mahasiswa='$id'
            ");
        } else {
            mysqli_query($this->db, "
                INSERT INTO data_mahasiswa (id_mahasiswa, nik, jenis_kelamin, tempat_tanggal_lahir, asal_sekolah, jurusan_asal, no_hp, email, alamat, penghasilan_orangtua, golongan)
                VALUES ('$id', '$nik', '$jk', '$ttl', '$sekolah', '$jurusan', '$hp', '$email', '$alamat', '$penghasilan', '$golongan')
            ");
        }

        $this->saveNilaiMahasiswa($id, $nilaiKriteria);
        return true;
    }

    private function saveNilaiMahasiswa($id_mhs, $nilaiKriteria) {
        $id_mhs = (int)$id_mhs;
        if (empty($nilaiKriteria['kriteria_sub']) || !is_array($nilaiKriteria['kriteria_sub'])) return;

        foreach ($nilaiKriteria['kriteria_sub'] as $idK => $idSub) {
            $idK = (int)$idK;
            $idSub = (int)$idSub;
            if ($idSub <= 0) continue;

            $valInput = isset($nilaiKriteria['kriteria_nilai'][$idK]) ? (float)$nilaiKriteria['kriteria_nilai'][$idK] : null;
            if ($valInput === null) {
                $qSubVal = mysqli_query($this->db, "SELECT nilai FROM sub_kriteria WHERE id_sub='$idSub'");
                if ($qSubVal && $dSub = mysqli_fetch_assoc($qSubVal)) {
                    $valInput = (float)$dSub['nilai'];
                }
            }

            $cekNilai = mysqli_query($this->db, "SELECT id_nilai FROM nilai WHERE id_mahasiswa='$id_mhs' AND id_kriteria='$idK'");
            if ($cekNilai && mysqli_num_rows($cekNilai) > 0) {
                mysqli_query($this->db, "UPDATE nilai SET id_sub='$idSub', nilai='$valInput' WHERE id_mahasiswa='$id_mhs' AND id_kriteria='$idK'");
            } else {
                mysqli_query($this->db, "INSERT INTO nilai (id_mahasiswa, id_kriteria, id_sub, nilai) VALUES ('$id_mhs', '$idK', '$idSub', '$valInput')");
            }
        }
    }

    public function deleteMahasiswa($id) {
        $id = (int)$id;
        return mysqli_query($this->db, "DELETE FROM mahasiswa WHERE id_mahasiswa='$id'");
    }
}