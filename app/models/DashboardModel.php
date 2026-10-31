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
        $q = @mysqli_query($this->koneksi, "SELECT id_prodi, nama_prodi FROM prodi ORDER BY id_prodi ASC");
        if ($q && mysqli_num_rows($q) > 0) {
            while ($pr = mysqli_fetch_assoc($q)) {
                $prodi[] = [
                    'id_prodi'   => $pr['id_prodi'],
                    'nama_prodi' => $pr['nama_prodi']
                ];
            }
        } else {
            $prodi = [
                ['id_prodi' => 1, 'nama_prodi' => 'Teknik Informatika'],
                ['id_prodi' => 2, 'nama_prodi' => 'Sistem Informasi'],
                ['id_prodi' => 3, 'nama_prodi' => 'Teknologi Informasi'],
                ['id_prodi' => 4, 'nama_prodi' => 'Manajemen Informatika'],
                ['id_prodi' => 5, 'nama_prodi' => 'Rekayasa Perangkat Lunak']
            ];
        }
        return $prodi;
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

