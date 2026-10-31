<?php

require_once __DIR__ . '/../includes/koneksi.php';

class KriteriaModel {
    private $db;

    public function __construct() {
        global $koneksi;
        $this->db = $koneksi;
    }

    public function getAllKriteriaWithSubs() {
        $query = mysqli_query($this->db, "
            SELECT k.id_kriteria, k.nama_kriteria, k.jenis, k.bobot, s.id_sub, s.nama_sub, s.nilai 
            FROM kriteria k 
            LEFT JOIN sub_kriteria s ON k.id_kriteria = s.id_kriteria 
            ORDER BY k.id_kriteria ASC, s.nilai ASC
        ");

        $dataKriteria = [];
        if ($query) {
            while ($row = mysqli_fetch_assoc($query)) {
                $idK = (int)$row['id_kriteria'];
                if (!isset($dataKriteria[$idK])) {
                    $dataKriteria[$idK] = [
                        'id_kriteria'   => $idK,
                        'nama_kriteria' => $row['nama_kriteria'],
                        'nama'          => $row['nama_kriteria'],
                        'jenis'         => $row['jenis'],
                        'bobot'         => (float)$row['bobot'],
                        'subs'          => []
                    ];
                }
                if (!empty($row['id_sub'])) {
                    $dataKriteria[$idK]['subs'][] = [
                        'id_sub'   => (int)$row['id_sub'],
                        'nama_sub' => $row['nama_sub'],
                        'nilai'    => (float)$row['nilai']
                    ];
                }
            }
        }
        return $dataKriteria;
    }

    public function getKriteriaById($id) {
        $id = (int)$id;
        $query = mysqli_query($this->db, "SELECT * FROM kriteria WHERE id_kriteria='$id'");
        return mysqli_fetch_assoc($query);
    }

    public function insertKriteria($nama, $jenis, $bobot) {
        $nama = mysqli_real_escape_string($this->db, $nama);
        $jenis = mysqli_real_escape_string($this->db, $jenis);
        $bobot = (float)$bobot;
        return mysqli_query($this->db, "INSERT INTO kriteria (nama_kriteria, jenis, bobot) VALUES ('$nama', '$jenis', '$bobot')");
    }

    public function updateKriteria($id, $nama, $jenis, $bobot) {
        $id = (int)$id;
        $nama = mysqli_real_escape_string($this->db, $nama);
        $jenis = mysqli_real_escape_string($this->db, $jenis);
        $bobot = (float)$bobot;
        return mysqli_query($this->db, "UPDATE kriteria SET nama_kriteria='$nama', jenis='$jenis', bobot='$bobot' WHERE id_kriteria='$id'");
    }

    public function deleteKriteria($id) {
        $id = (int)$id;
        return mysqli_query($this->db, "DELETE FROM kriteria WHERE id_kriteria='$id'");
    }
}

