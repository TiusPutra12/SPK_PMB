<?php

require_once __DIR__ . '/../models/KriteriaModel.php';

class KriteriaController {
    private $kriteriaModel;

    public function __construct() {
        $this->kriteriaModel = new KriteriaModel();
    }

    public function handleRequest() {
        $aksi = isset($_GET['aksi']) ? $_GET['aksi'] : '';

        if (isset($_POST['simpan_kriteria'])) {
            $nama = $_POST['nama_kriteria'] ?? '';
            $jenis = $_POST['jenis'] ?? 'Benefit';
            $bobot = $_POST['bobot'] ?? 0;
            $this->kriteriaModel->insertKriteria($nama, $jenis, $bobot);
            header("Location: kriteria.php?page=kriteria");
            exit;
        }

        if (isset($_POST['simpan_edit_kriteria'])) {
            $id = $_POST['id_kriteria'] ?? 0;
            $nama = $_POST['nama_kriteria'] ?? '';
            $jenis = $_POST['jenis'] ?? 'Benefit';
            $bobot = $_POST['bobot'] ?? 0;
            $this->kriteriaModel->updateKriteria($id, $nama, $jenis, $bobot);
            header("Location: kriteria.php?page=kriteria");
            exit;
        }

        if ($aksi === 'hapus_kriteria' && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $this->kriteriaModel->deleteKriteria($id);
            header("Location: kriteria.php?page=kriteria");
            exit;
        }
    }

    public function getKriteriaList() {
        return $this->kriteriaModel->getAllKriteriaWithSubs();
    }

    public function getKriteriaDetail($id) {
        return $this->kriteriaModel->getKriteriaById($id);
    }
}

