<?php

class DashboardModel {
    private $koneksi;

    public function __construct($koneksi) {
        $this->koneksi = $koneksi;
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
        $prodi = [];
        // Mengambil kode_prodi juga untuk ditampilkan di detail
        $q = @mysqli_query($this->koneksi, "SELECT id_prodi, nama_prodi, kode_prodi FROM prodi ORDER BY id_prodi ASC");
        if ($q && mysqli_num_rows($q) > 0) {
            while ($pr = mysqli_fetch_assoc($q)) {
                $prodi[] = [
                    'id_program'   => (int)$pr['id_prodi'], 
                    'nama_program' => $pr['nama_prodi'],
                    'id_prodi'     => (int)$pr['id_prodi'],
                    'nama_prodi'   => $pr['nama_prodi'],
                    'kode_prodi'   => $pr['kode_prodi']
                ];
            }
        } 
        return $prodi;
    }

    public function addProgramStudi($nama_program, $kode_prodi) {
        $nama_program = trim($nama_program);
        $kode_prodi = trim($kode_prodi);

        if (empty($nama_program) || empty($kode_prodi)) {
            return ['success' => false, 'message' => 'Nama dan Kode program studi tidak boleh kosong!'];
        }

        $nama_clean = mysqli_real_escape_string($this->koneksi, $nama_program);
        $kode_clean = mysqli_real_escape_string($this->koneksi, $kode_prodi);

        $check = mysqli_query($this->koneksi, "SELECT id_prodi FROM prodi WHERE LOWER(nama_prodi) = LOWER('$nama_clean')");
        if ($check && mysqli_num_rows($check) > 0) {
            return ['success' => false, 'message' => 'Program studi "' . $nama_program . '" sudah terdaftar!'];
        }

        // KUNCI PERBAIKAN: Insert nama dan kodenya sekaligus
        $ins = mysqli_query($this->koneksi, "INSERT INTO prodi (nama_prodi, kode_prodi) VALUES ('$nama_clean', '$kode_clean')");
        if ($ins) {
            $new_id = mysqli_insert_id($this->koneksi);
            return [
                'success' => true,
                'message' => 'Program studi "' . $nama_program . '" berhasil ditambahkan!'
            ];
        }
        return ['success' => false, 'message' => 'Gagal menambahkan data: ' . mysqli_error($this->koneksi)];
    }

    public function updateProgramStudi($id_program, $nama_program, $kode_prodi) {
        $id_program   = (int)$id_program;
        $nama_program = trim($nama_program);
        $kode_prodi   = trim($kode_prodi);

        if (empty($nama_program) || empty($kode_prodi)) {
            return ['success' => false, 'message' => 'Nama dan Kode program studi tidak boleh kosong!'];
        }

        $nama_clean = mysqli_real_escape_string($this->koneksi, $nama_program);
        $kode_clean = mysqli_real_escape_string($this->koneksi, $kode_prodi);

        $check = mysqli_query($this->koneksi, "SELECT id_prodi FROM prodi WHERE LOWER(nama_prodi) = LOWER('$nama_clean') AND id_prodi != $id_program");
        if ($check && mysqli_num_rows($check) > 0) {
            return ['success' => false, 'message' => 'Program studi "' . $nama_program . '" sudah terdaftar!'];
        }

        $upd = mysqli_query($this->koneksi, "UPDATE prodi SET nama_prodi = '$nama_clean', kode_prodi = '$kode_clean' WHERE id_prodi = $id_program");
        if ($upd) {
            return ['success' => true, 'message' => 'Program studi berhasil diperbarui!'];
        }
        return ['success' => false, 'message' => 'Gagal memperbarui data: ' . mysqli_error($this->koneksi)];
    }

    public function deleteProgramStudi($id_program) {
        $id_program = (int)$id_program;
        $del = mysqli_query($this->koneksi, "DELETE FROM prodi WHERE id_prodi = $id_program");
        if ($del && mysqli_affected_rows($this->koneksi) > 0) {
            return ['success' => true, 'message' => 'Program studi berhasil dihapus!'];
        }
        return ['success' => false, 'message' => 'Gagal menghapus data atau data tidak ditemukan.'];
    }

    public function getPendaftarList() {
        $list = [];

        $checkCols = @mysqli_query($this->koneksi, "SHOW COLUMNS FROM mahasiswa");
        $cols = [];
        if ($checkCols) {
            while ($c = mysqli_fetch_assoc($checkCols)) {
                $cols[] = strtolower($c['Field']);
            }
        }

        $idCol = in_array('id_mahasiswa', $cols) ? 'id_mahasiswa' : (in_array('id_data', $cols) ? 'id_data' : (in_array('id', $cols) ? 'id' : 'id_mahasiswa'));
        $hasNim = in_array('nim', $cols);
        $hasNama = in_array('nama_mahasiswa', $cols) ? 'nama_mahasiswa' : (in_array('nama', $cols) ? 'nama' : "''");
        $hasStatus = in_array('status', $cols) ? 'status' : "''";
        $hasGolongan = in_array('golongan', $cols) ? 'golongan' : "''";

        $nimSelect = $hasNim ? "m.nim" : "'' AS nim";
        $namaSelect = ($hasNama !== "''") ? "m.{$hasNama} AS nama_mahasiswa" : "'' AS nama_mahasiswa";
        $statusSelect = ($hasStatus !== "''") ? "m.{$hasStatus} AS status" : "'Menunggu' AS status";
        $golonganSelect = ($hasGolongan !== "''") ? "m.{$hasGolongan} AS golongan" : "'Umum' AS golongan";

        $q = mysqli_query($this->koneksi, "
            SELECT 
                m.{$idCol} AS id_mahasiswa, 
                {$nimSelect}, 
                {$namaSelect}, 
                {$statusSelect},
                {$golonganSelect}
            FROM mahasiswa m
            ORDER BY m.{$idCol} ASC
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
                    'nama'     => $row['nama_mahasiswa'] ?? 'Pendaftar #' . $id,
                    'nim'      => $row['nim'] ?? '',
                    'status'   => $row['status'] ?? 'Menunggu',
                    'golongan' => !empty($row['golongan']) ? $row['golongan'] : 'Umum',
                    'tahun'    => $tahun
                ];
            }
        }
        return $list;
    }

    public function getNilaiMatrix() {
        $checkTable = @mysqli_query($this->koneksi, "SHOW TABLES LIKE 'nilai'");
        if (!$checkTable || mysqli_num_rows($checkTable) === 0) {
            return [];
        }

        $checkCols = @mysqli_query($this->koneksi, "SHOW COLUMNS FROM `nilai`");
        $cols = [];
        if ($checkCols) {
            while ($c = mysqli_fetch_assoc($checkCols)) {
                $cols[] = strtolower($c['Field']);
            }
        }
        $idMhsCol = in_array('id_mahasiswa', $cols) ? 'id_mahasiswa' : (in_array('id_data', $cols) ? 'id_data' : 'id_mahasiswa');

        $hasSubTable = false;
        $checkSub = @mysqli_query($this->koneksi, "SHOW TABLES LIKE 'sub_kriteria'");
        if ($checkSub && mysqli_num_rows($checkSub) > 0) {
            $hasSubTable = true;
        }

        $joinSub = $hasSubTable ? "LEFT JOIN sub_kriteria s ON n.id_sub = s.id_sub" : "";
        $selectSub = $hasSubTable ? "s.nilai AS bobot_sub" : "NULL AS bobot_sub";

        $q = @mysqli_query($this->koneksi, "
            SELECT 
                n.{$idMhsCol} AS id_mahasiswa,
                n.id_kriteria,
                n.nilai AS nilai_input,
                {$selectSub}
            FROM `nilai` n
            {$joinSub}
        ");

        $matrix = [];
        if ($q) {
            while ($n = mysqli_fetch_assoc($q)) {
                $id_mhs = (int)$n['id_mahasiswa'];
                $id_krit = (int)$n['id_kriteria'];
                $val = !is_null($n['nilai_input']) ? (float)$n['nilai_input'] : (!is_null($n['bobot_sub']) ? (float)$n['bobot_sub'] : 0);
                $matrix[$id_mhs][$id_krit] = $val;
            }
        }
        return $matrix;
    }
}
?>