<?php

class DashboardModel {
    private $koneksi;

    public function __construct($koneksi) {
        $this->koneksi = $koneksi;
        $this->ensureProgramStudiTable();
    }

    public function ensureProgramStudiTable() {
        $check = @mysqli_query($this->koneksi, "SHOW TABLES LIKE 'program_studi'");
        if (!$check || mysqli_num_rows($check) == 0) {
            @mysqli_query($this->koneksi, "
                CREATE TABLE IF NOT EXISTS `program_studi` (
                    `id_program` INT AUTO_INCREMENT PRIMARY KEY,
                    `nama_program` VARCHAR(100) NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");

            $checkOld = @mysqli_query($this->koneksi, "SHOW TABLES LIKE 'prodi'");
            if ($checkOld && mysqli_num_rows($checkOld) > 0) {
                @mysqli_query($this->koneksi, "INSERT INTO `program_studi` (`nama_program`) SELECT `nama_prodi` FROM `prodi`");
            } else {
                @mysqli_query($this->koneksi, "
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

    public function getKriteria() {
        $list = [];
        $total_bobot = 0;
        $q = mysqli_query($this->koneksi, "SELECT id_kriteria, nama_kriteria, jenis, bobot FROM kriteria ORDER BY id_kriteria ASC");
        if ($q) {
            while ($k = mysqli_fetch_assoc($q)) {
                $list[$k['id_kriteria']] = [
                    'id_kriteria'   => (int)$k['id_kriteria'],
                    'nama_kriteria' => $k['nama_kriteria'],
                    'jenis'         => $k['jenis'],
                    'bobot'         => (float)$k['bobot'],
                ];
                $total_bobot += (float)$k['bobot'];
            }
        }
        foreach ($list as $id => $k) {
            $wj = ($total_bobot > 0) ? ($k['bobot'] / $total_bobot) : 0;
            $list[$id]['wj'] = (strtolower($k['jenis']) === 'cost') ? (-$wj) : $wj;
        }
        return $list;
    }

    public function getProdiList() {
        $this->ensureProgramStudiTable();
        $prodi = [];
        $q = @mysqli_query($this->koneksi, "SELECT id_program, nama_program FROM program_studi ORDER BY id_program ASC");
        if ($q && mysqli_num_rows($q) > 0) {
            while ($pr = mysqli_fetch_assoc($q)) {
                $prodi[] = [
                    'id_program'   => (int)$pr['id_program'],
                    'nama_program' => $pr['nama_program'],
                    'id_prodi'     => (int)$pr['id_program'],
                    'nama_prodi'   => $pr['nama_program']
                ];
            }
        } else {
            // Cek tabel prodi lama jika ada
            $qOld = @mysqli_query($this->koneksi, "SELECT id_prodi, nama_prodi FROM prodi ORDER BY id_prodi ASC");
            if ($qOld && mysqli_num_rows($qOld) > 0) {
                while ($pr = mysqli_fetch_assoc($qOld)) {
                    $prodi[] = [
                        'id_program'   => (int)$pr['id_prodi'],
                        'nama_program' => $pr['nama_prodi'],
                        'id_prodi'     => (int)$pr['id_prodi'],
                        'nama_prodi'   => $pr['nama_prodi']
                    ];
                }
            } else {
                $prodi = [
                    ['id_program' => 1, 'nama_program' => 'S1 Teknik Informatika', 'id_prodi' => 1, 'nama_prodi' => 'S1 Teknik Informatika'],
                    ['id_program' => 2, 'nama_program' => 'S1 Sistem Informasi', 'id_prodi' => 2, 'nama_prodi' => 'S1 Sistem Informasi'],
                    ['id_program' => 3, 'nama_program' => 'S1 Manajemen', 'id_prodi' => 3, 'nama_prodi' => 'S1 Manajemen'],
                    ['id_program' => 4, 'nama_program' => 'S1 Akuntansi', 'id_prodi' => 4, 'nama_prodi' => 'S1 Akuntansi'],
                    ['id_program' => 5, 'nama_program' => 'S1 Pariwisata', 'id_prodi' => 5, 'nama_prodi' => 'S1 Pariwisata']
                ];
            }
        }
        return $prodi;
    }

    public function addProgramStudi($nama_program) {
        $this->ensureProgramStudiTable();
        $nama_program = trim($nama_program);
        if (empty($nama_program)) {
            return ['success' => false, 'message' => 'Nama program studi tidak boleh kosong!'];
        }
        $nama_clean = mysqli_real_escape_string($this->koneksi, $nama_program);

        // Cek duplikasi nama program studi
        $check = mysqli_query($this->koneksi, "SELECT id_program FROM program_studi WHERE LOWER(nama_program) = LOWER('$nama_clean')");
        if ($check && mysqli_num_rows($check) > 0) {
            return ['success' => false, 'message' => 'Program studi "' . $nama_program . '" sudah terdaftar!'];
        }

        $ins = mysqli_query($this->koneksi, "INSERT INTO program_studi (nama_program) VALUES ('$nama_clean')");
        if ($ins) {
            $new_id = mysqli_insert_id($this->koneksi);
            return [
                'success' => true,
                'message' => 'Program studi "' . $nama_program . '" berhasil ditambahkan!',
                'id_program' => $new_id,
                'nama_program' => $nama_program
            ];
        }
        return ['success' => false, 'message' => 'Gagal menambahkan data: ' . mysqli_error($this->koneksi)];
    }

    public function updateProgramStudi($id_program, $nama_program) {
        $this->ensureProgramStudiTable();
        $id_program   = (int)$id_program;
        $nama_program = trim($nama_program);
        if (empty($nama_program)) {
            return ['success' => false, 'message' => 'Nama program studi tidak boleh kosong!'];
        }
        $nama_clean = mysqli_real_escape_string($this->koneksi, $nama_program);

        // Cek duplikasi (kecuali dirinya sendiri)
        $check = mysqli_query($this->koneksi, "SELECT id_program FROM program_studi WHERE LOWER(nama_program) = LOWER('$nama_clean') AND id_program != $id_program");
        if ($check && mysqli_num_rows($check) > 0) {
            return ['success' => false, 'message' => 'Program studi "' . $nama_program . '" sudah terdaftar!'];
        }

        $upd = mysqli_query($this->koneksi, "UPDATE program_studi SET nama_program = '$nama_clean' WHERE id_program = $id_program");
        if ($upd) {
            return ['success' => true, 'message' => 'Program studi berhasil diperbarui!', 'id_program' => $id_program, 'nama_program' => $nama_program];
        }
        return ['success' => false, 'message' => 'Gagal memperbarui data: ' . mysqli_error($this->koneksi)];
    }

    public function deleteProgramStudi($id_program) {
        $this->ensureProgramStudiTable();
        $id_program = (int)$id_program;
        $del = mysqli_query($this->koneksi, "DELETE FROM program_studi WHERE id_program = $id_program");
        if ($del && mysqli_affected_rows($this->koneksi) > 0) {
            return ['success' => true, 'message' => 'Program studi berhasil dihapus!'];
        }
        return ['success' => false, 'message' => 'Gagal menghapus data atau data tidak ditemukan.'];
    }

    public function getPendaftarList() {
        $list = [];
        $q = mysqli_query($this->koneksi, "
            SELECT 
                m.id_mahasiswa, 
                m.nim, 
                m.nama_mahasiswa, 
                m.status,
                m.golongan
            FROM mahasiswa m
            ORDER BY m.id_mahasiswa ASC
        ");

        if ($q) {
            while ($row = mysqli_fetch_assoc($q)) {
                $id = (int)$row['id_mahasiswa'];
                $nim = trim($row['nim'] ?? '');
                $tahun = "2026";
                if (strlen($nim) >= 2 && is_numeric(substr($nim, 0, 2))) {
                    $tahun = "20" . substr($nim, 0, 2);
                }

                $list[$id] = [
                    'id'       => $id,
                    'nama'     => $row['nama_mahasiswa'],
                    'nim'      => $row['nim'],
                    'status'   => $row['status'],
                    'golongan' => !empty($row['golongan']) ? $row['golongan'] : 'Umum',
                    'tahun'    => $tahun
                ];
            }
        }
        return $list;
    }

    public function getNilaiMatrix() {
        $q = mysqli_query($this->koneksi, "
            SELECT 
                n.id_mahasiswa,
                n.id_kriteria,
                n.nilai AS nilai_input,
                s.nilai AS bobot_sub
            FROM nilai n
            JOIN kriteria k ON n.id_kriteria = k.id_kriteria
            LEFT JOIN sub_kriteria s ON n.id_sub = s.id_sub
        ");

        $matrix = [];
        if ($q) {
            while ($n = mysqli_fetch_assoc($q)) {
                $id_mhs = (int)$n['id_mahasiswa'];
                $id_krit = (int)$n['id_kriteria'];
                $val = !is_null($n['nilai_input']) ? (float)$n['nilai_input'] : (float)$n['bobot_sub'];
                $matrix[$id_mhs][$id_krit] = $val;
            }
        }
        return $matrix;
    }
}
?>