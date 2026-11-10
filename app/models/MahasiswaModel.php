<?php
// app/models/MahasiswaModel.php
require_once __DIR__ . '/../includes/koneksi.php';

class MahasiswaModel {
    private $db;

    public function __construct() {
        global $koneksi;
        $this->db = $koneksi;
    }

    public function getMahasiswaTableInfo() {
        $checkCols = @mysqli_query($this->db, "SHOW COLUMNS FROM `mahasiswa`");
        $cols = [];
        if ($checkCols) {
            while ($c = mysqli_fetch_assoc($checkCols)) {
                $cols[] = strtolower($c['Field']);
            }
        }
        $idCol = in_array('id_mahasiswa', $cols) ? 'id_mahasiswa' : (in_array('id_data', $cols) ? 'id_data' : (in_array('id', $cols) ? 'id' : 'id_mahasiswa'));
        $namaCol = in_array('nama_mahasiswa', $cols) ? 'nama_mahasiswa' : (in_array('nama', $cols) ? 'nama' : "''");
        $statusCol = in_array('status', $cols) ? 'status' : "''";
        
        return [
            'id'       => $idCol, 
            'nama'     => $namaCol, 
            'status'   => $statusCol,
            'cols'     => $cols
        ];
    }

    // Menarik Data Program Studi
    public function getListProdi() {
        $check = @mysqli_query($this->db, "SHOW TABLES LIKE 'program_studi'");
        if ($check && mysqli_num_rows($check) > 0) {
            $q = mysqli_query($this->db, "SELECT nama_program FROM program_studi ORDER BY id_program ASC");
            $res = [];
            if ($q && mysqli_num_rows($q) > 0) {
                while ($r = mysqli_fetch_assoc($q)) {
                    $res[] = $r['nama_program'];
                }
                return $res;
            }
        }

        $checkOld = @mysqli_query($this->db, "SHOW TABLES LIKE 'prodi'");
        if ($checkOld && mysqli_num_rows($checkOld) > 0) {
            $qOld = mysqli_query($this->db, "SELECT nama_prodi FROM prodi ORDER BY id_prodi ASC");
            $res = [];
            if ($qOld) {
                while ($r = mysqli_fetch_assoc($qOld)) {
                    $res[] = $r['nama_prodi'];
                }
                return $res;
            }
        }

        return [
            'S1 Teknik Informatika',
            'S1 Sistem Informasi',
            'S1 Manajemen',
            'S1 Akuntansi',
            'S1 Pariwisata',
            'S1 Pendidikan Guru Sekolah Dasar (PGSD)',
            'S1 Pendidikan Kepelatihan Olahraga (PKO)',
            'S1 Pendidikan Bahasa Inggris'
        ];
    }

    public function addProgramStudi($nama_program) {
        $nama_program = trim($nama_program);
        if (empty($nama_program)) return false;
        $safe_nama = mysqli_real_escape_string($this->db, $nama_program);

        $check = @mysqli_query($this->db, "SHOW TABLES LIKE 'program_studi'");
        if ($check && mysqli_num_rows($check) > 0) {
            $cek = mysqli_query($this->db, "SELECT id_program FROM program_studi WHERE LOWER(nama_program) = LOWER('$safe_nama')");
            if ($cek && mysqli_num_rows($cek) > 0) return false;
            return mysqli_query($this->db, "INSERT INTO program_studi (nama_program) VALUES ('$safe_nama')") ? true : false;
        }
        return false;
    }

    public function updateProgramStudi($id_program, $nama_program) {
        $id = (int)$id_program;
        $nama_program = trim($nama_program);
        if ($id <= 0 || empty($nama_program)) return false;
        $safe_nama = mysqli_real_escape_string($this->db, $nama_program);

        $check = @mysqli_query($this->db, "SHOW TABLES LIKE 'program_studi'");
        if ($check && mysqli_num_rows($check) > 0) {
            return mysqli_query($this->db, "UPDATE program_studi SET nama_program = '$safe_nama' WHERE id_program = '$id'") ? true : false;
        }
        return false;
    }

    public function deleteProgramStudi($id_program) {
        $id = (int)$id_program;
        if ($id <= 0) return false;
        $check = @mysqli_query($this->db, "SHOW TABLES LIKE 'program_studi'");
        if ($check && mysqli_num_rows($check) > 0) {
            return mysqli_query($this->db, "DELETE FROM program_studi WHERE id_program = '$id'") ? true : false;
        }
        return false;
    }

    public function getAllMahasiswaWithDetail() {
        $info = $this->getMahasiswaTableInfo();
        $idCol = $info['id'];
        $cols = $info['cols'];

        $namaSelect = ($info['nama'] !== "''") ? "m.{$info['nama']} AS nama_mahasiswa" : "'' AS nama_mahasiswa";
        $statusSelect = ($info['status'] !== "''") ? "m.{$info['status']} AS status" : "'Menunggu' AS status";

        // Kolom-kolom langsung dari tabel mahasiswa
        $nikSelect      = in_array('nik', $cols) ? "m.nik" : "'-' AS nik";
        $jkSelect       = in_array('jenis_kelamin', $cols) ? "m.jenis_kelamin" : "'L' AS jenis_kelamin";
        $ttlSelect      = in_array('tempat_tanggal_lahir', $cols) ? "m.tempat_tanggal_lahir" : "'-' AS tempat_tanggal_lahir";
        $sekolahSelect  = in_array('asal_sekolah', $cols) ? "m.asal_sekolah" : "'-' AS asal_sekolah";
        $jurusanSelect  = in_array('jurusan_asal', $cols) ? "m.jurusan_asal" : "'Umum' AS jurusan_asal";
        $hpSelect       = in_array('no_hp', $cols) ? "m.no_hp" : "'-' AS no_hp";
        $emailSelect    = in_array('email', $cols) ? "m.email" : "'-' AS email";
        $alamatSelect   = in_array('alamat', $cols) ? "m.alamat" : "'-' AS alamat";
        $golonganSelect = in_array('golongan', $cols) ? "m.golongan" : "'Umum' AS golongan";
        $totalSelect    = in_array('total_bobot', $cols) ? "m.total_bobot" : "NULL AS total_bobot";

        $q = mysqli_query($this->db, "
            SELECT 
                m.{$idCol} AS id_mahasiswa, 
                {$namaSelect}, 
                {$statusSelect},
                {$nikSelect},
                {$jkSelect},
                {$ttlSelect},
                {$sekolahSelect},
                {$jurusanSelect},
                {$hpSelect},
                {$emailSelect},
                {$alamatSelect},
                {$golonganSelect},
                {$totalSelect}
            FROM mahasiswa m
            ORDER BY m.{$idCol} DESC
        ");

        $list = [];
        if ($q) {
            while ($row = mysqli_fetch_assoc($q)) {
                $id = (int)$row['id_mahasiswa'];
                $tahun = '2026';

                $list[$id] = [
                    'id'            => $id,
                    'nama'          => $row['nama_mahasiswa'] ?? 'Mahasiswa #' . $id,
                    'prodi'         => !empty($row['jurusan_asal']) ? $row['jurusan_asal'] : 'Umum',
                    'tahun'         => $tahun,
                    'status'        => !empty($row['status']) ? $row['status'] : 'Menunggu',
                    'golongan'      => !empty($row['golongan']) ? $row['golongan'] : 'Umum',
                    'total_bobot'   => isset($row['total_bobot']) && $row['total_bobot'] !== null ? (float)$row['total_bobot'] : null,
                    'detail'        => [
                        'nik'                  => $row['nik'] ?? '-',
                        'jenis_kelamin'        => $row['jenis_kelamin'] ?? '-',
                        'tempat_tanggal_lahir' => $row['tempat_tanggal_lahir'] ?? '-',
                        'asal_sekolah'         => $row['asal_sekolah'] ?? '-',
                        'jurusan_asal'         => $row['jurusan_asal'] ?? '-',
                        'no_hp'                => $row['no_hp'] ?? '-',
                        'email'                => $row['email'] ?? '-',
                        'alamat'               => $row['alamat'] ?? '-',
                        'golongan'             => $row['golongan'] ?? 'Umum',
                        'total_bobot'          => isset($row['total_bobot']) && $row['total_bobot'] !== null ? (float)$row['total_bobot'] : null,
                    ]
                ];
            }
        }
        return $list;
    }

    public function getMahasiswaById($id) {
        $id = (int)$id;
        $info = $this->getMahasiswaTableInfo();
        $idCol = $info['id'];
        $cols = $info['cols'];

        $namaSelect = ($info['nama'] !== "''") ? "m.{$info['nama']} AS nama_mahasiswa" : "'' AS nama_mahasiswa";
        $statusSelect = ($info['status'] !== "''") ? "m.{$info['status']} AS status" : "'Menunggu' AS status";

        $nikSelect      = in_array('nik', $cols) ? "m.nik" : "'-' AS nik";
        $jkSelect       = in_array('jenis_kelamin', $cols) ? "m.jenis_kelamin" : "'L' AS jenis_kelamin";
        $ttlSelect      = in_array('tempat_tanggal_lahir', $cols) ? "m.tempat_tanggal_lahir" : "'-' AS tempat_tanggal_lahir";
        $sekolahSelect  = in_array('asal_sekolah', $cols) ? "m.asal_sekolah" : "'-' AS asal_sekolah";
        $jurusanSelect  = in_array('jurusan_asal', $cols) ? "m.jurusan_asal" : "'Umum' AS jurusan_asal";
        $hpSelect       = in_array('no_hp', $cols) ? "m.no_hp" : "'-' AS no_hp";
        $emailSelect    = in_array('email', $cols) ? "m.email" : "'-' AS email";
        $alamatSelect   = in_array('alamat', $cols) ? "m.alamat" : "'-' AS alamat";
        $golonganSelect = in_array('golongan', $cols) ? "m.golongan" : "'Umum' AS golongan";
        $totalSelect    = in_array('total_bobot', $cols) ? "m.total_bobot" : "NULL AS total_bobot";

        $q = mysqli_query($this->db, "
            SELECT 
                m.{$idCol} AS id_mahasiswa, 
                {$namaSelect}, 
                {$statusSelect},
                {$nikSelect},
                {$jkSelect},
                {$ttlSelect},
                {$sekolahSelect},
                {$jurusanSelect},
                {$hpSelect},
                {$emailSelect},
                {$alamatSelect},
                {$golonganSelect},
                {$totalSelect}
            FROM mahasiswa m
            WHERE m.{$idCol} = '$id'
        ");

        if ($q && mysqli_num_rows($q) > 0) {
            return mysqli_fetch_assoc($q);
        }
        return null;
    }

    // Validasi Kesesuaian NIK dan Tanggal Lahir (Aturan Dukcapil)
    public function validateNikWithTtl($nik, $ttl, $jk) {
        $nik = trim($nik);
        if (strlen($nik) !== 16 || !ctype_digit($nik)) {
            return [
                'valid' => false,
                'message' => "NIK harus berjumlah tepat 16 digit angka."
            ];
        }

        if (empty($ttl) || $ttl === '-') {
            return ['valid' => true];
        }

        $tglStr = '';
        if (strpos($ttl, ', ') !== false) {
            $parts = explode(', ', $ttl);
            $tglStr = trim($parts[1] ?? '');
        } else {
            $tglStr = trim($ttl);
        }

        $y = ''; $m = ''; $d = '';
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $tglStr, $matches)) {
            $y = $matches[1];
            $m = $matches[2];
            $d = $matches[3];
        } elseif (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $tglStr, $matches)) {
            $d = $matches[1];
            $m = $matches[2];
            $y = $matches[3];
        } else {
            return ['valid' => true];
        }

        $inputDay = (int)$d;
        $inputMM = str_pad($m, 2, '0', STR_PAD_LEFT);
        $inputYY = substr($y, -2);

        $nikDayRaw = (int)substr($nik, 6, 2);
        $nikMM = substr($nik, 8, 2);
        $nikMonth = (int)$nikMM;
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
            return [
                'valid' => false,
                'message' => "Digit tanggal lahir pada NIK (" . substr($nik, 6, 2) . ") tidak valid sesuai standar Dukcapil."
            ];
        }

        if ($nikMonth < 1 || $nikMonth > 12) {
            return [
                'valid' => false,
                'message' => "Digit bulan lahir pada NIK (" . $nikMM . ") tidak valid."
            ];
        }

        $isDayMatch = ($inputDay === $nikRealDay);
        $isMonthMatch = ($inputMM === $nikMM);
        $isYearMatch = ($inputYY === $nikYY);
        $isGenderMatch = ($jk === $nikGender);

        if (!$isDayMatch || !$isMonthMatch || !$isYearMatch || !$isGenderMatch) {
            $nikDayFmt = str_pad($nikRealDay, 2, '0', STR_PAD_LEFT);
            $nikGenderTxt = ($nikGender === 'P') ? 'Perempuan (Tanggal + 40)' : 'Laki-laki';
            $inputGenderTxt = ($jk === 'P') ? 'Perempuan' : 'Laki-laki';

            $errDetail = [];
            if (!$isGenderMatch) {
                $errDetail[] = "Jenis kelamin pada NIK ($nikGenderTxt) tidak cocok dengan pilihan form ($inputGenderTxt)";
            }
            if (!$isDayMatch) {
                $errDetail[] = "Tanggal lahir pada NIK ($nikRealDay) berbeda dengan pilihan form ($inputDay)";
            }
            if (!$isMonthMatch) {
                $errDetail[] = "Bulan lahir pada NIK ($nikMonth) berbeda dengan pilihan form ($inputMM)";
            }
            if (!$isYearMatch) {
                $errDetail[] = "Tahun lahir pada NIK (akhiran $nikYY) berbeda dengan tahun ($y)";
            }

            return [
                'valid' => false,
                'message' => "Tanggal Lahir/Gender tidak sesuai NIK! " . implode(', ', $errDetail) . "."
            ];
        }

        return ['valid' => true];
    }

    public function createMahasiswa($mhsData, $detailData, $nilaiKriteria = []) {
        $info = $this->getMahasiswaTableInfo();
        $cols = $info['cols'];

        // 1. Validasi Duplikat NIK
        $nik = !empty($detailData['nik']) ? mysqli_real_escape_string($this->db, $detailData['nik']) : '-';
        if ($nik !== '-' && $nik !== '') {
            if (in_array('nik', $cols)) {
                $cekNIK = @mysqli_query($this->db, "SELECT nik FROM mahasiswa WHERE nik = '$nik'");
                if ($cekNIK && mysqli_num_rows($cekNIK) > 0) {
                    if (session_status() === PHP_SESSION_NONE) session_start();
                    $_SESSION['error_msg'] = "Gagal! NIK '$nik' sudah terdaftar dalam sistem.";
                    return false;
                }
            }
            $checkDataMhs = @mysqli_query($this->db, "SHOW TABLES LIKE 'data_mahasiswa'");
            if ($checkDataMhs && mysqli_num_rows($checkDataMhs) > 0) {
                $cekNIKData = @mysqli_query($this->db, "SELECT nik FROM data_mahasiswa WHERE nik = '$nik'");
                if ($cekNIKData && mysqli_num_rows($cekNIKData) > 0) {
                    if (session_status() === PHP_SESSION_NONE) session_start();
                    $_SESSION['error_msg'] = "Gagal! NIK '$nik' sudah terdaftar dalam sistem.";
                    return false;
                }
            }
        }

        $jk = !empty($detailData['jenis_kelamin']) ? mysqli_real_escape_string($this->db, $detailData['jenis_kelamin']) : 'L';
        $ttl = !empty($detailData['tempat_tanggal_lahir']) ? mysqli_real_escape_string($this->db, $detailData['tempat_tanggal_lahir']) : '-';

        // 2. Validasi Kesesuaian Tanggal Lahir & Jenis Kelamin dengan NIK
        if ($nik !== '-' && $nik !== '') {
            $checkNik = $this->validateNikWithTtl($nik, $ttl, $jk);
            if (!$checkNik['valid']) {
                if (session_status() === PHP_SESSION_NONE) session_start();
                $_SESSION['error_msg'] = "Gagal! " . $checkNik['message'];
                return false;
            }
        }

        $nama = mysqli_real_escape_string($this->db, $mhsData['nama']);
        $status = 'Menunggu';
        $sekolah = !empty($detailData['asal_sekolah']) ? mysqli_real_escape_string($this->db, $detailData['asal_sekolah']) : '-';
        $jurusan = !empty($detailData['jurusan_asal']) ? mysqli_real_escape_string($this->db, $detailData['jurusan_asal']) : '-';
        $hp = !empty($detailData['no_hp']) ? mysqli_real_escape_string($this->db, $detailData['no_hp']) : '-';
        $email = !empty($detailData['email']) ? mysqli_real_escape_string($this->db, $detailData['email']) : '-';
        $alamat = !empty($detailData['alamat']) ? mysqli_real_escape_string($this->db, $detailData['alamat']) : '-';
        $golongan = !empty($detailData['golongan']) ? mysqli_real_escape_string($this->db, $detailData['golongan']) : 'Umum';

        // Hitung skor penilaian: Total Akumulasi Skor (TAS), Rata-rata Skor (RSK), dan Nilai Akhir Terbobot (total)
        $metrics = $this->calculateMetrics($nilaiKriteria);
        $calculatedTotal = $metrics['total'];
        $tasVal = $metrics['TAS'];
        $rskVal = $metrics['RSK'];

        // Siapkan field yang tersedia di tabel mahasiswa
        $insertData = [];
        if ($info['nama'] !== "''") {
            $insertData[$info['nama']] = "'$nama'";
        }
        if (in_array('status', $cols)) {
            $insertData['status'] = "'$status'";
        }
        if (in_array('nik', $cols)) {
            $insertData['nik'] = "'$nik'";
        }
        if (in_array('jenis_kelamin', $cols)) {
            $insertData['jenis_kelamin'] = "'$jk'";
        }
        if (in_array('tempat_tanggal_lahir', $cols)) {
            $insertData['tempat_tanggal_lahir'] = "'$ttl'";
        }
        if (in_array('asal_sekolah', $cols)) {
            $insertData['asal_sekolah'] = "'$sekolah'";
        }
        if (in_array('jurusan_asal', $cols)) {
            $insertData['jurusan_asal'] = "'$jurusan'";
        }
        if (in_array('no_hp', $cols)) {
            $insertData['no_hp'] = "'$hp'";
        }
        if (in_array('email', $cols)) {
            $insertData['email'] = "'$email'";
        }
        if (in_array('alamat', $cols)) {
            $insertData['alamat'] = "'$alamat'";
        }
        if (in_array('golongan', $cols)) {
            $insertData['golongan'] = "'$golongan'";
        }
        if (in_array('total', $cols)) {
            $insertData['total'] = $calculatedTotal !== null ? "'$calculatedTotal'" : "NULL";
        }
        if (in_array('tas', $cols) || in_array('TAS', $cols)) {
            $tasCol = in_array('TAS', $cols) ? 'TAS' : 'tas';
            $insertData[$tasCol] = $tasVal !== null ? "'$tasVal'" : "NULL";
        }
        if (in_array('rsk', $cols) || in_array('RSK', $cols)) {
            $rskCol = in_array('RSK', $cols) ? 'RSK' : 'rsk';
            $insertData[$rskCol] = $rskVal !== null ? "'$rskVal'" : "NULL";
        }
        if (in_array('total_bobot', $cols)) {
            $insertData['total_bobot'] = $calculatedTotal !== null ? "'$calculatedTotal'" : "NULL";
        }

        $fieldsSql = implode(", ", array_keys($insertData));
        $valsSql   = implode(", ", array_values($insertData));

        $qMhs = mysqli_query($this->db, "INSERT INTO mahasiswa ({$fieldsSql}) VALUES ({$valsSql})");
        if (!$qMhs) {
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['error_msg'] = "Gagal menyimpan data mahasiswa: " . mysqli_error($this->db);
            return false;
        }

        $id_new = mysqli_insert_id($this->db);
        $this->saveNilaiMahasiswa($id_new, $nilaiKriteria);
        return $id_new;
    }

    public function updateMahasiswa($id, $mhsData, $detailData, $nilaiKriteria = []) {
        $id = (int)$id;
        $info = $this->getMahasiswaTableInfo();
        $idCol = $info['id'];
        $cols = $info['cols'];

        // Validasi Duplikat NIK saat Edit di tabel mahasiswa
        $nik = !empty($detailData['nik']) ? mysqli_real_escape_string($this->db, $detailData['nik']) : '-';
        if ($nik !== '-' && $nik !== '' && in_array('nik', $cols)) {
            $cekNIK = @mysqli_query($this->db, "SELECT nik FROM mahasiswa WHERE nik = '$nik' AND {$idCol} != '$id'");
            if ($cekNIK && mysqli_num_rows($cekNIK) > 0) {
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
        $status = mysqli_real_escape_string($this->db, $mhsData['status'] ?? 'Menunggu');
        $sekolah = !empty($detailData['asal_sekolah']) ? mysqli_real_escape_string($this->db, $detailData['asal_sekolah']) : '-';
        $jurusan = !empty($detailData['jurusan_asal']) ? mysqli_real_escape_string($this->db, $detailData['jurusan_asal']) : '-';
        $hp = !empty($detailData['no_hp']) ? mysqli_real_escape_string($this->db, $detailData['no_hp']) : '-';
        $email = !empty($detailData['email']) ? mysqli_real_escape_string($this->db, $detailData['email']) : '-';
        $alamat = !empty($detailData['alamat']) ? mysqli_real_escape_string($this->db, $detailData['alamat']) : '-';
        $golongan = !empty($detailData['golongan']) ? mysqli_real_escape_string($this->db, $detailData['golongan']) : 'Umum';

        // Hitung skor penilaian: Total Akumulasi Skor (TAS), Rata-rata Skor (RSK), dan Nilai Akhir Terbobot (total)
        $metrics = $this->calculateMetrics($nilaiKriteria);
        $calculatedTotal = $metrics['total'];
        $tasVal = $metrics['TAS'];
        $rskVal = $metrics['RSK'];

        $updates = [];
        if ($info['nama'] !== "''") {
            $updates[] = "{$info['nama']}='$nama'";
        }
        if (in_array('status', $cols)) {
            $updates[] = "status='$status'";
        }
        if (in_array('nik', $cols)) {
            $updates[] = "nik='$nik'";
        }
        if (in_array('jenis_kelamin', $cols)) {
            $updates[] = "jenis_kelamin='$jk'";
        }
        if (in_array('tempat_tanggal_lahir', $cols)) {
            $updates[] = "tempat_tanggal_lahir='$ttl'";
        }
        if (in_array('asal_sekolah', $cols)) {
            $updates[] = "asal_sekolah='$sekolah'";
        }
        if (in_array('jurusan_asal', $cols)) {
            $updates[] = "jurusan_asal='$jurusan'";
        }
        if (in_array('no_hp', $cols)) {
            $updates[] = "no_hp='$hp'";
        }
        if (in_array('email', $cols)) {
            $updates[] = "email='$email'";
        }
        if (in_array('alamat', $cols)) {
            $updates[] = "alamat='$alamat'";
        }
        if (in_array('golongan', $cols)) {
            $updates[] = "golongan='$golongan'";
        }
        if (in_array('total', $cols) && $calculatedTotal !== null) {
            $updates[] = "total='$calculatedTotal'";
        }
        if ((in_array('tas', $cols) || in_array('TAS', $cols)) && $tasVal !== null) {
            $tasCol = in_array('TAS', $cols) ? 'TAS' : 'tas';
            $updates[] = "{$tasCol}='$tasVal'";
        }
        if ((in_array('rsk', $cols) || in_array('RSK', $cols)) && $rskVal !== null) {
            $rskCol = in_array('RSK', $cols) ? 'RSK' : 'rsk';
            $updates[] = "{$rskCol}='$rskVal'";
        }
        if (in_array('total_bobot', $cols) && $calculatedTotal !== null) {
            $updates[] = "total_bobot='$calculatedTotal'";
        }

        if (!empty($updates)) {
            $updatesSql = implode(", ", $updates);
            mysqli_query($this->db, "UPDATE mahasiswa SET {$updatesSql} WHERE {$idCol}='$id'");
        }

        $this->saveNilaiMahasiswa($id, $nilaiKriteria);
        return true;
    }

    public function deleteMahasiswa($id) {
        $id = (int)$id;
        $info = $this->getMahasiswaTableInfo();
        $idCol = $info['id'];

        $checkNilai = @mysqli_query($this->db, "SHOW TABLES LIKE 'nilai'");
        if ($checkNilai && mysqli_num_rows($checkNilai) > 0) {
            @mysqli_query($this->db, "DELETE FROM nilai WHERE id_mahasiswa = '$id'");
        }

        return mysqli_query($this->db, "DELETE FROM mahasiswa WHERE {$idCol} = '$id'");
    }

    public function calculateMetrics($nilaiKriteria) {
        if (empty($nilaiKriteria['kriteria_nilai']) && empty($nilaiKriteria['kriteria_sub'])) {
            return ['total' => null, 'TAS' => null, 'RSK' => null];
        }

        $qKrit = @mysqli_query($this->db, "SELECT id_kriteria, bobot, jenis FROM kriteria");
        if (!$qKrit || mysqli_num_rows($qKrit) == 0) return ['total' => null, 'TAS' => null, 'RSK' => null];

        $kriteriaList = [];
        $sumBobot = 0;
        while ($k = mysqli_fetch_assoc($qKrit)) {
            $id_k = (int)$k['id_kriteria'];
            $b = (float)$k['bobot'];
            $kriteriaList[$id_k] = [
                'bobot' => $b,
                'jenis' => $k['jenis']
            ];
            $sumBobot += $b;
        }

        $totalWeighted = 0.0;
        $totalAccumulated = 0.0;
        $countFilled = 0;

        foreach ($kriteriaList as $id_k => $k) {
            $normW = ($sumBobot > 0) ? ($k['bobot'] / $sumBobot) : 0;
            $subId = (int)($nilaiKriteria['kriteria_sub'][$id_k] ?? 0);
            $rawVal = $nilaiKriteria['kriteria_nilai'][$id_k] ?? null;

            if ($subId <= 0 && is_numeric($rawVal)) {
                $subId = $this->determineSubKriteria($id_k, (float)$rawVal);
            }

            $score = null;
            if ($subId > 0) {
                $qSub = @mysqli_query($this->db, "SELECT nilai FROM sub_kriteria WHERE id_sub = '$subId'");
                if ($qSub && $rSub = mysqli_fetch_assoc($qSub)) {
                    $score = (float)$rSub['nilai'];
                }
            } elseif (is_numeric($rawVal)) {
                $score = (float)$rawVal;
            }

            if ($score !== null) {
                $totalWeighted += ($score * $normW);
                $totalAccumulated += $score;
                $countFilled++;
            }
        }

        if ($countFilled === 0) {
            return ['total' => null, 'TAS' => null, 'RSK' => null];
        }

        $rsk = $totalAccumulated / $countFilled;

        return [
            'total' => round($totalWeighted, 4),
            'TAS'   => round($totalAccumulated, 4),
            'RSK'   => round($rsk, 4)
        ];
    }

    public function calculateTotalBobot($nilaiKriteria) {
        $metrics = $this->calculateMetrics($nilaiKriteria);
        return $metrics['total'];
    }

    public function determineSubKriteria($id_kriteria, $nilai) {
        $id_k = (int)$id_kriteria;
        if (!is_numeric($nilai)) return 0;
        $val = (float)$nilai;

        $checkSub = @mysqli_query($this->db, "SHOW TABLES LIKE 'sub_kriteria'");
        if (!$checkSub || mysqli_num_rows($checkSub) == 0) return 0;

        $q = mysqli_query($this->db, "SELECT id_sub, nama_sub, nilai FROM sub_kriteria WHERE id_kriteria = '$id_k' ORDER BY id_sub ASC");
        if (!$q || mysqli_num_rows($q) == 0) return 0;

        $subs = [];
        while ($r = mysqli_fetch_assoc($q)) {
            $subs[] = $r;
        }

        foreach ($subs as $s) {
            $nama = trim($s['nama_sub']);
            if (preg_match('/(\d+(?:\.\d+)?)\s*<=\s*(?:nilai|[a-zA-Z]+)?\s*<\s*(\d+(?:\.\d+)?)/i', $nama, $m)) {
                $min = (float)$m[1];
                $max = (float)$m[2];
                if ($val >= $min && $val < $max) {
                    return (int)$s['id_sub'];
                }
            } elseif (preg_match('/^(\d+(?:\.\d+)?)\s*-\s*(\d+(?:\.\d+)?)$/', $nama, $m)) {
                $min = (float)$m[1];
                $max = (float)$m[2];
                if ($val >= $min && $val <= $max) {
                    return (int)$s['id_sub'];
                }
            }
        }

        foreach ($subs as $s) {
            $nama = trim($s['nama_sub']);
            if (preg_match('/>=\s*(\d+(?:\.\d+)?)/', $nama, $m)) {
                $threshold = (float)$m[1];
                if ($val >= $threshold) {
                    return (int)$s['id_sub'];
                }
            } elseif (preg_match('/^>\s*(\d+(?:\.\d+)?)/', $nama, $m)) {
                $threshold = (float)$m[1];
                if ($val > $threshold) {
                    return (int)$s['id_sub'];
                }
            }
        }

        foreach ($subs as $s) {
            $nama = trim($s['nama_sub']);
            if (preg_match('/^<\s*(\d+(?:\.\d+)?)/', $nama, $m)) {
                $threshold = (float)$m[1];
                if ($val < $threshold) {
                    return (int)$s['id_sub'];
                }
            } elseif (preg_match('/<=\s*(\d+(?:\.\d+)?)/', $nama, $m) && strpos($nama, '>') === false) {
                $threshold = (float)$m[1];
                if ($val <= $threshold) {
                    return (int)$s['id_sub'];
                }
            }
        }

        if ($id_k === 1) {
            if ($val >= 80) return 3;
            if ($val >= 70) return 2;
            return 1;
        }
        if ($id_k === 4) {
            if ($val >= 75) return 12;
            if ($val >= 65) return 11;
            return 10;
        }
        if ($id_k === 5) {
            if ($val >= 350) return 15;
            if ($val >= 300) return 14;
            return 13;
        }

        return !empty($subs) ? (int)$subs[0]['id_sub'] : 0;
    }

    private function saveNilaiMahasiswa($id_mhs, $nilaiKriteria) {
        $checkNilai = @mysqli_query($this->db, "SHOW TABLES LIKE 'nilai'");
        if (!$checkNilai || mysqli_num_rows($checkNilai) == 0) return;

        $id_mhs = (int)$id_mhs;
        @mysqli_query($this->db, "DELETE FROM nilai WHERE id_mahasiswa = '$id_mhs'");

        if (empty($nilaiKriteria['kriteria_nilai']) && empty($nilaiKriteria['kriteria_sub'])) return;

        $qKrit = @mysqli_query($this->db, "SELECT id_kriteria, nama_kriteria FROM kriteria");
        $allKrit = [];
        if ($qKrit) {
            while ($k = mysqli_fetch_assoc($qKrit)) {
                $allKrit[(int)$k['id_kriteria']] = $k['nama_kriteria'];
            }
        }

        foreach ($allKrit as $id_k => $namaKrit) {
            $nilaiRaw = $nilaiKriteria['kriteria_nilai'][$id_k] ?? null;
            $subRaw = $nilaiKriteria['kriteria_sub'][$id_k] ?? null;

            $val = (is_numeric($nilaiRaw) && $nilaiRaw !== '') ? (float)$nilaiRaw : null;
            $id_sub = (int)$subRaw;

            $isManual = in_array($id_k, [1, 4, 5])
                || stripos($namaKrit, 'Rapor') !== false
                || stripos($namaKrit, 'TKA') !== false
                || stripos($namaKrit, 'Inggris') !== false;

            if ($isManual && $val !== null) {
                $autoSub = $this->determineSubKriteria($id_k, $val);
                if ($autoSub > 0) {
                    $id_sub = $autoSub;
                }
            } elseif (!$isManual && $id_sub > 0 && ($val === null || $val == 0)) {
                $qSub = @mysqli_query($this->db, "SELECT nilai FROM sub_kriteria WHERE id_sub = '$id_sub'");
                if ($qSub && $rSub = mysqli_fetch_assoc($qSub)) {
                    $val = (float)$rSub['nilai'];
                }
            }

            if ($id_sub > 0) {
                $sqlVal = ($val !== null) ? "'$val'" : "NULL";
                @mysqli_query($this->db, "
                    INSERT INTO nilai (id_mahasiswa, id_kriteria, id_sub, nilai)
                    VALUES ('$id_mhs', '$id_k', '$id_sub', $sqlVal)
                ");
            }
        }
    }

    public function getRawNilaiMahasiswa() {
        $checkNilai = @mysqli_query($this->db, "SHOW TABLES LIKE 'nilai'");
        if (!$checkNilai || mysqli_num_rows($checkNilai) == 0) {
            return ['raw' => []];
        }

        $q = @mysqli_query($this->db, "SELECT * FROM nilai");
        $res = [];
        if ($q) {
            while ($row = mysqli_fetch_assoc($q)) {
                $res[$row['id_mahasiswa']][$row['id_kriteria']] = [
                    'id_sub' => (int)$row['id_sub'],
                    'nilai'  => ($row['nilai'] !== null && $row['nilai'] !== '') ? (float)$row['nilai'] : ''
                ];
            }
        }
        return ['raw' => $res];
    }
}
