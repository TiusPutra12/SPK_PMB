<?php
// app/models/MahasiswaModel.php
require_once __DIR__ . '/../includes/koneksi.php';

class MahasiswaModel {
    private $db;

    public function __construct() {
        global $koneksi;
        $this->db = $koneksi;
        $this->ensureProgramStudiTable();
    }

    public function ensureProgramStudiTable() {
        $check = @mysqli_query($this->db, "SHOW TABLES LIKE 'program_studi'");
        if (!$check || mysqli_num_rows($check) == 0) {
            @mysqli_query($this->db, "
                CREATE TABLE IF NOT EXISTS `program_studi` (
                    `id_program` INT AUTO_INCREMENT PRIMARY KEY,
                    `nama_program` VARCHAR(100) NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");

            $checkOld = @mysqli_query($this->db, "SHOW TABLES LIKE 'prodi'");
            if ($checkOld && mysqli_num_rows($checkOld) > 0) {
                @mysqli_query($this->db, "INSERT INTO `program_studi` (`nama_program`) SELECT `nama_prodi` FROM `prodi`");
            } else {
                @mysqli_query($this->db, "
                    INSERT INTO `program_studi` (`id_program`, `nama_program`) VALUES 
                    (1, 'S1 Teknik Informatika'), 
                    (2, 'S1 Sistem Informasi'), 
                    (3, 'S1 Manajemen'), 
                    (4, 'S1 Akuntansi'), 
                    (5, 'S1 Pariwisata'), 
                    (6, 'S1 Pendidikan Guru Sekolah Dasar (PGSD)'), 
                    (7, 'S1 Pendidikan Kepelatihan Olahraga (PKO)'), 
                    (8, 'S1 Pendidikan Bahasa Inggris')
                ");
            }
        }
    }

    // FITUR: Menarik Data Program Studi dari tabel program_studi (kolom id_program, nama_program)
    public function getListProdi() {
        $this->ensureProgramStudiTable();
        $q = mysqli_query($this->db, "SELECT id_program, nama_program FROM program_studi ORDER BY id_program ASC");
        $res = [];
        if ($q && mysqli_num_rows($q) > 0) {
            while ($r = mysqli_fetch_assoc($q)) {
                $res[] = $r['nama_program'];
            }
        } else {
            $qOld = @mysqli_query($this->db, "SELECT nama_prodi FROM prodi ORDER BY id_prodi ASC");
            if ($qOld) {
                while ($r = mysqli_fetch_assoc($qOld)) {
                    $res[] = $r['nama_prodi'];
                }
            }
        }
        return $res;
    }

    public function addProgramStudi($nama_program) {
        $this->ensureProgramStudiTable();
        $nama_program = trim($nama_program);
        if (empty($nama_program)) {
            return ['success' => false, 'message' => 'Nama program studi tidak boleh kosong!'];
        }
        $nama_clean = mysqli_real_escape_string($this->db, $nama_program);

        $check = mysqli_query($this->db, "SELECT id_program FROM program_studi WHERE LOWER(nama_program) = LOWER('$nama_clean')");
        if ($check && mysqli_num_rows($check) > 0) {
            return ['success' => false, 'message' => 'Program studi "' . $nama_program . '" sudah terdaftar!'];
        }

        $ins = mysqli_query($this->db, "INSERT INTO program_studi (nama_program) VALUES ('$nama_clean')");
        if ($ins) {
            $new_id = mysqli_insert_id($this->db);
            return [
                'success' => true,
                'message' => 'Program studi "' . $nama_program . '" berhasil ditambahkan!',
                'id_program' => $new_id,
                'nama_program' => $nama_program
            ];
        }
        return ['success' => false, 'message' => 'Gagal menambahkan program studi: ' . mysqli_error($this->db)];
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
                d.jurusan_asal, d.no_hp, d.email, d.alamat, d.penghasilan_orangtua,
                d.pekerjaan_ayah, d.pekerjaan_ibu, d.penghasilan_ayah, d.penghasilan_ibu,
                d.aset_kendaraan, d.aset_bangunan, d.uang_makan_harian
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
                        'pekerjaan_ayah'       => $row['pekerjaan_ayah'] ?? '-',
                        'pekerjaan_ibu'        => $row['pekerjaan_ibu'] ?? '-',
                        'penghasilan_ayah'     => $row['penghasilan_ayah'] ?? '-',
                        'penghasilan_ibu'      => $row['penghasilan_ibu'] ?? '-',
                        'aset_kendaraan'       => $row['aset_kendaraan'] ?? '-',
                        'aset_bangunan'        => $row['aset_bangunan'] ?? '-',
                        'uang_makan_harian'    => $row['uang_makan_harian'] ?? '-',
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
            SELECT m.*, d.* 
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

    // Validasi Kesesuaian NIK dan Tanggal Lahir (Aturan Dukcapil)
    public function validateNikWithTtl($nik, $ttl, $jk) {
        $nik = trim($nik ?? '');
        if ($nik === '' || $nik === '-') {
            return ['valid' => true];
        }
        
        if (strlen($nik) !== 16 || !ctype_digit($nik)) {
            return ['valid' => false, 'message' => 'NIK harus berjumlah 16 digit angka.'];
        }

        $parts = explode(', ', $ttl);
        $tgl = count($parts) == 2 ? trim($parts[1]) : trim($ttl);

        if (empty($tgl) || $tgl === '-') {
            return ['valid' => false, 'message' => 'Tanggal lahir tidak boleh kosong untuk verifikasi NIK.'];
        }

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $tgl, $m)) {
            $inputYear = (int)$m[1];
            $inputMonth = (int)$m[2];
            $inputDay = (int)$m[3];
        } elseif (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $tgl, $m)) {
            $inputDay = (int)$m[1];
            $inputMonth = (int)$m[2];
            $inputYear = (int)$m[3];
        } else {
            return ['valid' => false, 'message' => 'Format tanggal lahir tidak valid.'];
        }

        $inputYY = substr((string)$inputYear, -2);
        $inputMM = str_pad($inputMonth, 2, '0', STR_PAD_LEFT);

        $nikDayRaw = (int)substr($nik, 6, 2);
        $nikMM = substr($nik, 8, 2);
        $nikYY = substr($nik, 10, 2);

        $nikGender = '';
        $nikRealDay = 0;
        if ($nikDayRaw > 40 && $nikDayRaw <= 71) {
            $nikGender = 'P';
            $nikRealDay = $nikDayRaw - 40;
        } elseif ($nikDayRaw >= 1 && $nikDayRaw <= 31) {
            $nikGender = 'L';
            $nikRealDay = $nikDayRaw;
        } else {
            return ['valid' => false, 'message' => 'Digit tanggal pada NIK (digit ke-7 & 8) tidak valid sesuai ketentuan Dukcapil.'];
        }

        if ($inputDay !== $nikRealDay || $inputMM !== $nikMM || $inputYY !== $nikYY || $jk !== $nikGender) {
            return [
                'valid' => false,
                'message' => 'Tanggal lahir atau jenis kelamin tidak sesuai dengan informasi 6 digit pada NIK (Nomor Induk Kependudukan).'
            ];
        }

        return ['valid' => true];
    }

    public function createMahasiswa($mhsData, $detailData, $nilaiKriteria = []) {
        $rawNim = trim($mhsData['nim'] ?? '');
        if (empty($rawNim)) {
            $prefix = date('y') . '010';
            $qLast = mysqli_query($this->db, "SELECT nim FROM mahasiswa WHERE nim LIKE '{$prefix}%' ORDER BY nim DESC LIMIT 1");
            if ($qLast && $rowLast = mysqli_fetch_assoc($qLast)) {
                $lastNum = (int)substr($rowLast['nim'], strlen($prefix));
                $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
                $nim = $prefix . $nextNum;
            } else {
                $nim = $prefix . '0001';
            }
            while (mysqli_num_rows(mysqli_query($this->db, "SELECT nim FROM mahasiswa WHERE nim = '$nim'")) > 0) {
                $lastNum = (int)substr($nim, strlen($prefix));
                $nim = $prefix . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
            }
        } else {
            $nim = mysqli_real_escape_string($this->db, $rawNim);
            // Validasi Duplikat NIM
            $cekNIM = mysqli_query($this->db, "SELECT nim FROM mahasiswa WHERE nim = '$nim'");
            if (mysqli_num_rows($cekNIM) > 0) {
                if (session_status() === PHP_SESSION_NONE) session_start();
                $_SESSION['error_msg'] = "Gagal! NIM '$nim' sudah terdaftar.";
                return false;
            }
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

        $jk = !empty($detailData['jenis_kelamin']) ? mysqli_real_escape_string($this->db, $detailData['jenis_kelamin']) : 'L';
        $ttl = !empty($detailData['tempat_tanggal_lahir']) ? mysqli_real_escape_string($this->db, $detailData['tempat_tanggal_lahir']) : '-';

        // Validasi Kesesuaian Tanggal Lahir & Jenis Kelamin dengan NIK
        if ($nik !== '-' && $nik !== '') {
            $checkNik = $this->validateNikWithTtl($nik, $ttl, $jk);
            if (!$checkNik['valid']) {
                if (session_status() === PHP_SESSION_NONE) session_start();
                $_SESSION['error_msg'] = "Gagal! " . $checkNik['message'];
                return false;
            }
        }

        $nama = mysqli_real_escape_string($this->db, $mhsData['nama']);
        $golongan = mysqli_real_escape_string($this->db, $mhsData['golongan'] ?? 'Umum');
        $status = 'Menunggu';

        $qMhs = mysqli_query($this->db, "INSERT INTO mahasiswa (nim, nama_mahasiswa, status, golongan) VALUES ('$nim', '$nama', '$status', '$golongan')");
        if (!$qMhs) return false;

        $id_new = mysqli_insert_id($this->db);

        $sekolah = !empty($detailData['asal_sekolah']) ? mysqli_real_escape_string($this->db, $detailData['asal_sekolah']) : '-';
        $jurusan = !empty($detailData['jurusan_asal']) ? mysqli_real_escape_string($this->db, $detailData['jurusan_asal']) : '-';
        $hp = !empty($detailData['no_hp']) ? mysqli_real_escape_string($this->db, $detailData['no_hp']) : '-';
        $email = !empty($detailData['email']) ? mysqli_real_escape_string($this->db, $detailData['email']) : '-';
        $alamat = !empty($detailData['alamat']) ? mysqli_real_escape_string($this->db, $detailData['alamat']) : '-';
        $penghasilan = !empty($detailData['penghasilan_orangtua']) ? mysqli_real_escape_string($this->db, $detailData['penghasilan_orangtua']) : '-';

        $pekerjaan_ayah = !empty($detailData['pekerjaan_ayah']) ? mysqli_real_escape_string($this->db, $detailData['pekerjaan_ayah']) : '-';
        $pekerjaan_ibu = !empty($detailData['pekerjaan_ibu']) ? mysqli_real_escape_string($this->db, $detailData['pekerjaan_ibu']) : '-';
        $penghasilan_ayah = !empty($detailData['penghasilan_ayah']) ? mysqli_real_escape_string($this->db, $detailData['penghasilan_ayah']) : '-';
        $penghasilan_ibu = !empty($detailData['penghasilan_ibu']) ? mysqli_real_escape_string($this->db, $detailData['penghasilan_ibu']) : '-';
        $aset_kendaraan = !empty($detailData['aset_kendaraan']) ? mysqli_real_escape_string($this->db, $detailData['aset_kendaraan']) : '-';
        $aset_bangunan = !empty($detailData['aset_bangunan']) ? mysqli_real_escape_string($this->db, $detailData['aset_bangunan']) : '-';
        $uang_makan = !empty($detailData['uang_makan_harian']) ? mysqli_real_escape_string($this->db, $detailData['uang_makan_harian']) : '-';

        $qDetail = mysqli_query($this->db, "
            INSERT INTO data_mahasiswa (
                id_mahasiswa, nik, jenis_kelamin, tempat_tanggal_lahir, asal_sekolah, jurusan_asal, 
                no_hp, email, alamat, penghasilan_orangtua, golongan,
                pekerjaan_ayah, pekerjaan_ibu, penghasilan_ayah, penghasilan_ibu, 
                aset_kendaraan, aset_bangunan, uang_makan_harian
            )
            VALUES (
                '$id_new', '$nik', '$jk', '$ttl', '$sekolah', '$jurusan', 
                '$hp', '$email', '$alamat', '$penghasilan', '$golongan',
                '$pekerjaan_ayah', '$pekerjaan_ibu', '$penghasilan_ayah', '$penghasilan_ibu',
                '$aset_kendaraan', '$aset_bangunan', '$uang_makan'
            )
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
        $rawNim = trim($mhsData['nim'] ?? '');
        if (!empty($rawNim)) {
            $nim = mysqli_real_escape_string($this->db, $rawNim);
            // Validasi Duplikat NIM saat Edit (Abaikan ID diri sendiri)
            $cekNIM = mysqli_query($this->db, "SELECT nim FROM mahasiswa WHERE nim = '$nim' AND id_mahasiswa != '$id'");
            if (mysqli_num_rows($cekNIM) > 0) {
                if (session_status() === PHP_SESSION_NONE) session_start();
                $_SESSION['error_msg'] = "Gagal Edit! NIM '$nim' sudah dipakai oleh pendaftar lain.";
                return false;
            }
        } else {
            $qOld = mysqli_query($this->db, "SELECT nim FROM mahasiswa WHERE id_mahasiswa = '$id'");
            $rowOld = mysqli_fetch_assoc($qOld);
            $nim = $rowOld['nim'] ?? '';
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

        $jk = !empty($detailData['jenis_kelamin']) ? mysqli_real_escape_string($this->db, $detailData['jenis_kelamin']) : 'L';
        $ttl = !empty($detailData['tempat_tanggal_lahir']) ? mysqli_real_escape_string($this->db, $detailData['tempat_tanggal_lahir']) : '-';

        // Validasi Kesesuaian Tanggal Lahir & Jenis Kelamin dengan NIK saat Edit
        if ($nik !== '-' && $nik !== '') {
            $checkNik = $this->validateNikWithTtl($nik, $ttl, $jk);
            if (!$checkNik['valid']) {
                if (session_status() === PHP_SESSION_NONE) session_start();
                $_SESSION['error_msg'] = "Gagal Edit! " . $checkNik['message'];
                return false;
            }
        }

        $nama = mysqli_real_escape_string($this->db, $mhsData['nama']);
        $golongan = mysqli_real_escape_string($this->db, $mhsData['golongan'] ?? 'Umum');
        $status = mysqli_real_escape_string($this->db, $mhsData['status'] ?? 'Menunggu');

        mysqli_query($this->db, "UPDATE mahasiswa SET nim='$nim', nama_mahasiswa='$nama', status='$status', golongan='$golongan' WHERE id_mahasiswa='$id'");

        $sekolah = !empty($detailData['asal_sekolah']) ? mysqli_real_escape_string($this->db, $detailData['asal_sekolah']) : '-';
        $jurusan = !empty($detailData['jurusan_asal']) ? mysqli_real_escape_string($this->db, $detailData['jurusan_asal']) : '-';
        $hp = !empty($detailData['no_hp']) ? mysqli_real_escape_string($this->db, $detailData['no_hp']) : '-';
        $email = !empty($detailData['email']) ? mysqli_real_escape_string($this->db, $detailData['email']) : '-';
        $alamat = !empty($detailData['alamat']) ? mysqli_real_escape_string($this->db, $detailData['alamat']) : '-';
        $penghasilan = !empty($detailData['penghasilan_orangtua']) ? mysqli_real_escape_string($this->db, $detailData['penghasilan_orangtua']) : '-';

        $pekerjaan_ayah = !empty($detailData['pekerjaan_ayah']) ? mysqli_real_escape_string($this->db, $detailData['pekerjaan_ayah']) : '-';
        $pekerjaan_ibu = !empty($detailData['pekerjaan_ibu']) ? mysqli_real_escape_string($this->db, $detailData['pekerjaan_ibu']) : '-';
        $penghasilan_ayah = !empty($detailData['penghasilan_ayah']) ? mysqli_real_escape_string($this->db, $detailData['penghasilan_ayah']) : '-';
        $penghasilan_ibu = !empty($detailData['penghasilan_ibu']) ? mysqli_real_escape_string($this->db, $detailData['penghasilan_ibu']) : '-';
        $aset_kendaraan = !empty($detailData['aset_kendaraan']) ? mysqli_real_escape_string($this->db, $detailData['aset_kendaraan']) : '-';
        $aset_bangunan = !empty($detailData['aset_bangunan']) ? mysqli_real_escape_string($this->db, $detailData['aset_bangunan']) : '-';
        $uang_makan = !empty($detailData['uang_makan_harian']) ? mysqli_real_escape_string($this->db, $detailData['uang_makan_harian']) : '-';

        $cek = mysqli_query($this->db, "SELECT id_data FROM data_mahasiswa WHERE id_mahasiswa='$id'");
        if ($cek && mysqli_num_rows($cek) > 0) {
            mysqli_query($this->db, "
                UPDATE data_mahasiswa SET 
                    nik='$nik', jenis_kelamin='$jk', tempat_tanggal_lahir='$ttl', 
                    asal_sekolah='$sekolah', jurusan_asal='$jurusan', no_hp='$hp', email='$email', alamat='$alamat',
                    penghasilan_orangtua='$penghasilan', golongan='$golongan',
                    pekerjaan_ayah='$pekerjaan_ayah', pekerjaan_ibu='$pekerjaan_ibu',
                    penghasilan_ayah='$penghasilan_ayah', penghasilan_ibu='$penghasilan_ibu',
                    aset_kendaraan='$aset_kendaraan', aset_bangunan='$aset_bangunan',
                    uang_makan_harian='$uang_makan'
                WHERE id_mahasiswa='$id'
            ");
        } else {
            mysqli_query($this->db, "
                INSERT INTO data_mahasiswa (
                    id_mahasiswa, nik, jenis_kelamin, tempat_tanggal_lahir, asal_sekolah, jurusan_asal, 
                    no_hp, email, alamat, penghasilan_orangtua, golongan,
                    pekerjaan_ayah, pekerjaan_ibu, penghasilan_ayah, penghasilan_ibu, 
                    aset_kendaraan, aset_bangunan, uang_makan_harian
                )
                VALUES (
                    '$id', '$nik', '$jk', '$ttl', '$sekolah', '$jurusan', 
                    '$hp', '$email', '$alamat', '$penghasilan', '$golongan',
                    '$pekerjaan_ayah', '$pekerjaan_ibu', '$penghasilan_ayah', '$penghasilan_ibu',
                    '$aset_kendaraan', '$aset_bangunan', '$uang_makan'
                )
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