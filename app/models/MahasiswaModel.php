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

    // FITUR: Tambah Program Studi Baru ke Database
    public function addProgramStudi($nama_program) {
        $this->ensureProgramStudiTable();
        $nama_program = trim($nama_program);
        if (empty($nama_program)) return false;

        $safe_nama = mysqli_real_escape_string($this->db, $nama_program);

        // Cek duplikat nama prodi
        $check = mysqli_query($this->db, "SELECT id_program FROM program_studi WHERE LOWER(nama_program) = LOWER('$safe_nama')");
        if ($check && mysqli_num_rows($check) > 0) {
            return false; // Sudah terdaftar
        }

        $ins = mysqli_query($this->db, "INSERT INTO program_studi (nama_program) VALUES ('$safe_nama')");
        return $ins ? true : false;
    }

    // FITUR: Edit Program Studi
    public function updateProgramStudi($id_program, $nama_program) {
        $this->ensureProgramStudiTable();
        $id = (int)$id_program;
        $nama_program = trim($nama_program);
        if ($id <= 0 || empty($nama_program)) return false;

        $safe_nama = mysqli_real_escape_string($this->db, $nama_program);
        $check = mysqli_query($this->db, "SELECT id_program FROM program_studi WHERE LOWER(nama_program) = LOWER('$safe_nama') AND id_program != '$id'");
        if ($check && mysqli_num_rows($check) > 0) {
            return false;
        }

        $upd = mysqli_query($this->db, "UPDATE program_studi SET nama_program = '$safe_nama' WHERE id_program = '$id'");
        return $upd ? true : false;
    }

    // FITUR: Hapus Program Studi
    public function deleteProgramStudi($id_program) {
        $this->ensureProgramStudiTable();
        $id = (int)$id_program;
        if ($id <= 0) return false;

        $del = mysqli_query($this->db, "DELETE FROM program_studi WHERE id_program = '$id'");
        return $del ? true : false;
    }

    public function getAllMahasiswaWithDetail() {
        $q = mysqli_query($this->db, "
            SELECT 
                m.id_mahasiswa, m.nama_mahasiswa, m.status,
                d.id_data, d.nik, d.jenis_kelamin, d.tempat_tanggal_lahir, 
                d.asal_sekolah, d.jurusan_asal, d.no_hp, d.email, d.alamat
            FROM mahasiswa m
            LEFT JOIN data_mahasiswa d ON m.id_mahasiswa = d.id_mahasiswa
            ORDER BY m.id_mahasiswa DESC
        ");

        $list = [];
        if ($q) {
            while ($row = mysqli_fetch_assoc($q)) {
                $id = (int)$row['id_mahasiswa'];
                $tahun = '2024';

                $list[$id] = [
                    'id'            => $id,
                    'nama'          => $row['nama_mahasiswa'],
                    'prodi'         => !empty($row['jurusan_asal']) ? $row['jurusan_asal'] : 'Umum',
                    'tahun'         => $tahun,
                    'status'        => !empty($row['status']) ? $row['status'] : 'Menunggu',
                    'detail'        => [
                        'nik'                  => $row['nik'] ?? '-',
                        'jenis_kelamin'        => $row['jenis_kelamin'] ?? '-',
                        'tempat_tanggal_lahir' => $row['tempat_tanggal_lahir'] ?? '-',
                        'asal_sekolah'         => $row['asal_sekolah'] ?? '-',
                        'no_hp'                => $row['no_hp'] ?? '-',
                        'email'                => $row['email'] ?? '-',
                        'alamat'               => $row['alamat'] ?? '-',
                    ]
                ];
            }
        }
        return $list;
    }

    public function getMahasiswaById($id) {
        $id = (int)$id;
        $q = mysqli_query($this->db, "
            SELECT 
                m.id_mahasiswa, m.nama_mahasiswa, m.status,
                d.nik, d.jenis_kelamin, d.tempat_tanggal_lahir, 
                d.asal_sekolah, d.jurusan_asal, d.no_hp, d.email, d.alamat
            FROM mahasiswa m
            LEFT JOIN data_mahasiswa d ON m.id_mahasiswa = d.id_mahasiswa
            WHERE m.id_mahasiswa = '$id'
        ");
        if ($q && mysqli_num_rows($q) > 0) {
            return mysqli_fetch_assoc($q);
        }
        return null;
    }

    public function generateNIM() {
        $prefix = date('y'); // 2 Digit Tahun Saat ini, contoh: 26
        $jurusan_code = "0101"; // Kode default Teknik Informatika/PMB UNTRI
        $searchPrefix = $prefix . $jurusan_code;
        
        $q = mysqli_query($this->db, "
            SELECT nim FROM mahasiswa 
            WHERE nim LIKE '{$searchPrefix}%' 
            ORDER BY nim DESC LIMIT 1
        ");
        
        $nextNumber = 1;
        if ($q && mysqli_num_rows($q) > 0) {
            $row = mysqli_fetch_assoc($q);
            $lastNim = $row['nim'];
            $lastSeq = substr($lastNim, strlen($searchPrefix));
            if (is_numeric($lastSeq)) {
                $nextNumber = (int)$lastSeq + 1;
            }
        }
        
        return $searchPrefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    // Helper Konversi Nilai Numeric/Null untuk Kolom Database INT
    private function prepareSqlInt($val) {
        if ($val === null || $val === '' || $val === '-') {
            return "NULL";
        }
        if (is_numeric($val)) {
            return (int)$val;
        }
        // Jika format string rupiah (misal "< Rp 1.500.000") ekstrak angka atau default NULL
        $clean = preg_replace('/[^0-9]/', '', (string)$val);
        if ($clean !== '') {
            return (int)$clean;
        }
        return "NULL";
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
            return ['valid' => true]; // Lewati jika tidak ada TTL
        }

        // Format TTL di sistem: "Tempat, YYYY-MM-DD" atau "YYYY-MM-DD" atau "Tempat, DD-MM-YYYY"
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
            return ['valid' => true]; // Jika format bebas/tak dikenali kalender, toleransi
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
        // 1. Validasi Duplikat NIK
        $nik = !empty($detailData['nik']) ? mysqli_real_escape_string($this->db, $detailData['nik']) : '-';
        if ($nik !== '-' && $nik !== '') {
            $cekNIK = mysqli_query($this->db, "SELECT nik FROM data_mahasiswa WHERE nik = '$nik'");
            if ($cekNIK && mysqli_num_rows($cekNIK) > 0) {
                if (session_status() === PHP_SESSION_NONE) session_start();
                $_SESSION['error_msg'] = "Gagal! NIK '$nik' sudah terdaftar dalam sistem.";
                return false;
            }
        }

        $jk = !empty($detailData['jenis_kelamin']) ? mysqli_real_escape_string($this->db, $detailData['jenis_kelamin']) : 'L';
        $ttl = !empty($detailData['tempat_tanggal_lahir']) ? mysqli_real_escape_string($this->db, $detailData['tempat_tanggal_lahir']) : '-';

        // 3. Validasi Kesesuaian Tanggal Lahir & Jenis Kelamin dengan NIK
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
        $nim = $this->generateNIM();
        $nim = mysqli_real_escape_string($this->db, $nim);

        $qMhs = mysqli_query($this->db, "INSERT INTO mahasiswa (nim, nama_mahasiswa, status) VALUES ('$nim', '$nama', '$status')");
        if (!$qMhs) return false;

        $id_new = mysqli_insert_id($this->db);

        $sekolah = !empty($detailData['asal_sekolah']) ? mysqli_real_escape_string($this->db, $detailData['asal_sekolah']) : '-';
        $jurusan = !empty($detailData['jurusan_asal']) ? mysqli_real_escape_string($this->db, $detailData['jurusan_asal']) : '-';
        $hp = !empty($detailData['no_hp']) ? mysqli_real_escape_string($this->db, $detailData['no_hp']) : '-';
        $email = !empty($detailData['email']) ? mysqli_real_escape_string($this->db, $detailData['email']) : '-';
        $alamat = !empty($detailData['alamat']) ? mysqli_real_escape_string($this->db, $detailData['alamat']) : '-';
        $qDetail = mysqli_query($this->db, "
            INSERT INTO data_mahasiswa (
                id_mahasiswa, nik, jenis_kelamin, tempat_tanggal_lahir, asal_sekolah, jurusan_asal, 
                no_hp, email, alamat
            )
            VALUES (
                '$id_new', '$nik', '$jk', '$ttl', '$sekolah', '$jurusan', 
                '$hp', '$email', '$alamat'
            )
        ");

        if (!$qDetail) {
             mysqli_query($this->db, "DELETE FROM mahasiswa WHERE id_mahasiswa = '$id_new'");
             if (session_status() === PHP_SESSION_NONE) session_start();
             $_SESSION['error_msg'] = "Gagal menyimpan detail mahasiswa! (Error DB: " . mysqli_error($this->db) . ")";
             return false;
        }

        $this->saveNilaiMahasiswa($id_new, $nilaiKriteria);
        return $id_new;
    }

    public function updateMahasiswa($id, $mhsData, $detailData, $nilaiKriteria = []) {
        $id = (int)$id;

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
        $status = mysqli_real_escape_string($this->db, $mhsData['status'] ?? 'Menunggu');

        mysqli_query($this->db, "UPDATE mahasiswa SET nama_mahasiswa='$nama', status='$status' WHERE id_mahasiswa='$id'");

        $sekolah = !empty($detailData['asal_sekolah']) ? mysqli_real_escape_string($this->db, $detailData['asal_sekolah']) : '-';
        $jurusan = !empty($detailData['jurusan_asal']) ? mysqli_real_escape_string($this->db, $detailData['jurusan_asal']) : '-';
        $hp = !empty($detailData['no_hp']) ? mysqli_real_escape_string($this->db, $detailData['no_hp']) : '-';
        $email = !empty($detailData['email']) ? mysqli_real_escape_string($this->db, $detailData['email']) : '-';
        $alamat = !empty($detailData['alamat']) ? mysqli_real_escape_string($this->db, $detailData['alamat']) : '-';
        $cek = mysqli_query($this->db, "SELECT id_data FROM data_mahasiswa WHERE id_mahasiswa='$id'");
        if ($cek && mysqli_num_rows($cek) > 0) {
            mysqli_query($this->db, "
                UPDATE data_mahasiswa SET 
                    nik='$nik', jenis_kelamin='$jk', tempat_tanggal_lahir='$ttl', 
                    asal_sekolah='$sekolah', jurusan_asal='$jurusan', no_hp='$hp', email='$email', alamat='$alamat'
                WHERE id_mahasiswa='$id'
            ");
        } else {
            mysqli_query($this->db, "
                INSERT INTO data_mahasiswa (
                    id_mahasiswa, nik, jenis_kelamin, tempat_tanggal_lahir, asal_sekolah, jurusan_asal, 
                    no_hp, email, alamat
                )
                VALUES (
                    '$id', '$nik', '$jk', '$ttl', '$sekolah', '$jurusan', 
                    '$hp', '$email', '$alamat'
                )
            ");
        }

        $this->saveNilaiMahasiswa($id, $nilaiKriteria);
        return true;
    }

    public function deleteMahasiswa($id) {
        $id = (int)$id;
        return mysqli_query($this->db, "DELETE FROM mahasiswa WHERE id_mahasiswa = '$id'");
    }

    public function determineSubKriteria($id_kriteria, $nilai) {
        $id_k = (int)$id_kriteria;
        if (!is_numeric($nilai)) return 0;
        $val = (float)$nilai;

        $q = mysqli_query($this->db, "SELECT id_sub, nama_sub, nilai FROM sub_kriteria WHERE id_kriteria = '$id_k' ORDER BY id_sub ASC");
        if (!$q || mysqli_num_rows($q) == 0) return 0;

        $subs = [];
        while ($r = mysqli_fetch_assoc($q)) {
            $subs[] = $r;
        }

        // 1. Cek pola range "A <= nilai < B" atau "A - B"
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

        // 2. Cek pola batas atas ">= X" atau "> X"
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

        // 3. Cek pola batas bawah "<= X" atau "< X"
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

        // 4. Fallback khusus berdasarkan id_kriteria spesifik:
        if ($id_k === 1) { // Nilai Rapor
            if ($val >= 80) return 3;
            if ($val >= 70) return 2;
            return 1;
        }
        if ($id_k === 4) { // Nilai TKA
            if ($val >= 75) return 12;
            if ($val >= 65) return 11;
            return 10;
        }
        if ($id_k === 5) { // Skor Bahasa Inggris
            if ($val >= 350) return 15;
            if ($val >= 300) return 14;
            return 13;
        }

        return !empty($subs) ? (int)$subs[0]['id_sub'] : 0;
    }

    private function saveNilaiMahasiswa($id_mhs, $nilaiKriteria) {
        $id_mhs = (int)$id_mhs;
        mysqli_query($this->db, "DELETE FROM nilai WHERE id_mahasiswa = '$id_mhs'");

        if (empty($nilaiKriteria['kriteria_nilai']) && empty($nilaiKriteria['kriteria_sub'])) return;

        // Ambil daftar kriteria untuk membedakan yang manual dan kategori
        $qKrit = mysqli_query($this->db, "SELECT id_kriteria, nama_kriteria FROM kriteria");
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
                // Tentukan id_sub otomatis jika belum ada atau untuk sinkronisasi nilai manual
                $autoSub = $this->determineSubKriteria($id_k, $val);
                if ($autoSub > 0) {
                    $id_sub = $autoSub;
                }
            } elseif (!$isManual && $id_sub > 0 && ($val === null || $val == 0)) {
                // Untuk C2 & C3 (Prestasi): simpan bobot sub_kriteria sebagai nilai jika belum diinput
                $qSub = mysqli_query($this->db, "SELECT nilai FROM sub_kriteria WHERE id_sub = '$id_sub'");
                if ($qSub && $rSub = mysqli_fetch_assoc($qSub)) {
                    $val = (float)$rSub['nilai'];
                }
            }

            if ($id_sub > 0) {
                $sqlVal = ($val !== null) ? "'$val'" : "NULL";
                mysqli_query($this->db, "
                    INSERT INTO nilai (id_mahasiswa, id_kriteria, id_sub, nilai)
                    VALUES ('$id_mhs', '$id_k', '$id_sub', $sqlVal)
                ");
            }
        }
    }

    public function getRawNilaiMahasiswa() {
        $q = mysqli_query($this->db, "SELECT * FROM nilai");
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
