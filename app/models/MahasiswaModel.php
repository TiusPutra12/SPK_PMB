<?php

require_once __DIR__ . '/../includes/koneksi.php';

class MahasiswaModel {
    private $db;

    public function __construct() {
        global $koneksi;
        $this->db = $koneksi;
        $this->checkSchema();
    }

    private function checkSchema() {
        $checkCol = mysqli_query($this->db, "SHOW COLUMNS FROM mahasiswa LIKE 'golongan'");
        if ($checkCol && mysqli_num_rows($checkCol) === 0) {
            @mysqli_query($this->db, "ALTER TABLE mahasiswa ADD COLUMN golongan ENUM('Umum','Beasiswa') NOT NULL DEFAULT 'Umum' AFTER status");
        }

        $checkCol2 = mysqli_query($this->db, "SHOW COLUMNS FROM data_mahasiswa LIKE 'penghasilan_orangtua'");
        if ($checkCol2 && mysqli_num_rows($checkCol2) === 0) {
            @mysqli_query($this->db, "ALTER TABLE data_mahasiswa ADD COLUMN penghasilan_orangtua VARCHAR(100) NULL AFTER alamat");
        }
    }

    public function getAllMahasiswaWithDetail() {
        $q = mysqli_query($this->db, "
            SELECT 
                m.id_mahasiswa, 
                m.nim, 
                m.nama_mahasiswa, 
                m.status,
                m.golongan,
                d.nik,
                d.jenis_kelamin,
                d.tempat_tanggal_lahir,
                d.asal_sekolah,
                d.jurusan_asal,
                d.no_hp,
                d.email,
                d.alamat,
                d.penghasilan_orangtua
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

                $prodi = "Teknik Informatika";
                if (!empty($row['jurusan_asal'])) {
                    $prodi = $row['jurusan_asal'];
                }

                $jk = "-";
                if ($row['jenis_kelamin'] === 'L') {
                    $jk = "Laki-laki";
                } elseif ($row['jenis_kelamin'] === 'P') {
                    $jk = "Perempuan";
                }

                $golongan = !empty($row['golongan']) ? $row['golongan'] : 'Umum';

                $list[$id] = [
                    'id'        => $id,
                    'nama'      => $row['nama_mahasiswa'],
                    'nim'       => $row['nim'],
                    'nisn'      => $row['nim'],
                    'prodi'     => $prodi,
                    'tahun'     => $tahun,
                    'status'    => $row['status'],
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
                    ],
                    'nilai'     => [
                        'kriteria'  => [],
                        'vektor_s'  => '0',
                        'vektor_v'  => '0',
                        'peringkat' => 0
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
                n.id_mahasiswa,
                n.id_kriteria,
                n.nilai AS nilai_input,
                k.nama_kriteria,
                k.bobot,
                s.id_sub,
                s.nama_sub,
                s.nilai AS bobot_sub
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
        $nama = mysqli_real_escape_string($this->db, $mhsData['nama']);
        $status = mysqli_real_escape_string($this->db, $mhsData['status']);
        $golongan = mysqli_real_escape_string($this->db, $mhsData['golongan'] ?? 'Umum');

        $qMhs = mysqli_query($this->db, "INSERT INTO mahasiswa (nim, nama_mahasiswa, status, golongan) VALUES ('$nim', '$nama', '$status', '$golongan')");
        if (!$qMhs) return false;

        $id_new = mysqli_insert_id($this->db);

        $nik = mysqli_real_escape_string($this->db, $detailData['nik'] ?? '');
        $jk = mysqli_real_escape_string($this->db, $detailData['jenis_kelamin'] ?? 'L');
        $ttl = mysqli_real_escape_string($this->db, $detailData['tempat_tanggal_lahir'] ?? '');
        $sekolah = mysqli_real_escape_string($this->db, $detailData['asal_sekolah'] ?? '');
        $jurusan = mysqli_real_escape_string($this->db, $detailData['jurusan_asal'] ?? '');
        $hp = mysqli_real_escape_string($this->db, $detailData['no_hp'] ?? '');
        $email = mysqli_real_escape_string($this->db, $detailData['email'] ?? '');
        $alamat = mysqli_real_escape_string($this->db, $detailData['alamat'] ?? '');
        $penghasilan = mysqli_real_escape_string($this->db, $detailData['penghasilan_orangtua'] ?? '');

        mysqli_query($this->db, "
            INSERT INTO data_mahasiswa (id_mahasiswa, nik, jenis_kelamin, tempat_tanggal_lahir, asal_sekolah, jurusan_asal, no_hp, email, alamat, penghasilan_orangtua)
            VALUES ('$id_new', '$nik', '$jk', '$ttl', '$sekolah', '$jurusan', '$hp', '$email', '$alamat', '$penghasilan')
        ");

        $this->saveNilaiMahasiswa($id_new, $nilaiKriteria);
        return $id_new;
    }

    public function updateMahasiswa($id, $mhsData, $detailData, $nilaiKriteria = []) {
        $id = (int)$id;
        $nim = mysqli_real_escape_string($this->db, $mhsData['nim']);
        $nama = mysqli_real_escape_string($this->db, $mhsData['nama']);
        $status = mysqli_real_escape_string($this->db, $mhsData['status']);
        $golongan = mysqli_real_escape_string($this->db, $mhsData['golongan'] ?? 'Umum');

        mysqli_query($this->db, "UPDATE mahasiswa SET nim='$nim', nama_mahasiswa='$nama', status='$status', golongan='$golongan' WHERE id_mahasiswa='$id'");

        $nik = mysqli_real_escape_string($this->db, $detailData['nik'] ?? '');
        $jk = mysqli_real_escape_string($this->db, $detailData['jenis_kelamin'] ?? 'L');
        $ttl = mysqli_real_escape_string($this->db, $detailData['tempat_tanggal_lahir'] ?? '');
        $sekolah = mysqli_real_escape_string($this->db, $detailData['asal_sekolah'] ?? '');
        $jurusan = mysqli_real_escape_string($this->db, $detailData['jurusan_asal'] ?? '');
        $hp = mysqli_real_escape_string($this->db, $detailData['no_hp'] ?? '');
        $email = mysqli_real_escape_string($this->db, $detailData['email'] ?? '');
        $alamat = mysqli_real_escape_string($this->db, $detailData['alamat'] ?? '');
        $penghasilan = mysqli_real_escape_string($this->db, $detailData['penghasilan_orangtua'] ?? '');

        $cek = mysqli_query($this->db, "SELECT id_data FROM data_mahasiswa WHERE id_mahasiswa='$id'");
        if ($cek && mysqli_num_rows($cek) > 0) {
            mysqli_query($this->db, "
                UPDATE data_mahasiswa SET 
                    nik='$nik', jenis_kelamin='$jk', tempat_tanggal_lahir='$ttl', 
                    asal_sekolah='$sekolah', jurusan_asal='$jurusan', no_hp='$hp', email='$email', alamat='$alamat',
                    penghasilan_orangtua='$penghasilan'
                WHERE id_mahasiswa='$id'
            ");
        } else {
            mysqli_query($this->db, "
                INSERT INTO data_mahasiswa (id_mahasiswa, nik, jenis_kelamin, tempat_tanggal_lahir, asal_sekolah, jurusan_asal, no_hp, email, alamat, penghasilan_orangtua)
                VALUES ('$id', '$nik', '$jk', '$ttl', '$sekolah', '$jurusan', '$hp', '$email', '$alamat', '$penghasilan')
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

