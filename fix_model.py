#!/usr/bin/env python3
# Script to clean MahasiswaModel.php - remove nim, golongan, and beasiswa fields

import re

filepath = r'c:\laragon\www\SPK_PMB\SPK_PMB\app\models\MahasiswaModel.php'

with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

# ===== 1. Fix getAllMahasiswaWithDetail() SQL query =====
old_query = """            SELECT 
                m.id_mahasiswa, m.nim, m.nama_mahasiswa, m.status, m.golongan,
                d.id_data, d.nik, d.jenis_kelamin, d.tempat_tanggal_lahir, 
                d.asal_sekolah, d.jurusan_asal, d.no_hp, d.email, d.alamat, d.penghasilan_orangtua,
                d.pekerjaan_ayah, d.pekerjaan_ibu, d.penghasilan_ayah, d.penghasilan_ibu,
                d.aset_kendaraan, d.aset_bangunan, d.uang_makan_harian
            FROM mahasiswa m"""

new_query = """            SELECT 
                m.id_mahasiswa, m.nama_mahasiswa, m.status,
                d.id_data, d.nik, d.jenis_kelamin, d.tempat_tanggal_lahir, 
                d.asal_sekolah, d.jurusan_asal, d.no_hp, d.email, d.alamat
            FROM mahasiswa m"""

content = content.replace(old_query, new_query)

# ===== 2. Fix getAllMahasiswaWithDetail() return array - remove nim, golongan, beasiswa fields =====
old_list = """                $list[$id] = [
                    'id'            => $id,
                    'nim'           => $nim,
                    'nama'          => $row['nama_mahasiswa'],
                    'prodi'         => !empty($row['jurusan_asal']) ? $row['jurusan_asal'] : 'Umum',
                    'golongan'      => !empty($row['golongan']) ? $row['golongan'] : 'Umum',
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
                        'penghasilan_orangtua' => $row['penghasilan_orangtua'] ?? '-',
                        'pekerjaan_ayah'       => $row['pekerjaan_ayah'] ?? '-',
                        'pekerjaan_ibu'        => $row['pekerjaan_ibu'] ?? '-',
                        'penghasilan_ayah'     => $row['penghasilan_ayah'] ?? null,
                        'penghasilan_ibu'      => $row['penghasilan_ibu'] ?? null,
                        'aset_kendaraan'       => $row['aset_kendaraan'] ?? null,
                        'aset_bangunan'        => $row['aset_bangunan'] ?? null,
                        'uang_makan_harian'    => $row['uang_makan_harian'] ?? null
                    ]
                ];"""

new_list = """                $list[$id] = [
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
                ];"""

content = content.replace(old_list, new_list)

# ===== 3. Remove nim and golongan processing in getAllMahasiswaWithDetail() =====
# Remove: $nim = trim($row['nim']); and tahun-from-nim logic
old_nim_logic = """                $id = (int)$row['id_mahasiswa'];
                $nim = trim($row['nim']);
                
                // Menentukan Angkatan dari NIM
                $tahun = '2024'; 
                if (strlen($nim) >= 2 && is_numeric(substr($nim, 0, 2))) {
                    $tahun = '20' . substr($nim, 0, 2);
                }"""

new_nim_logic = """                $id = (int)$row['id_mahasiswa'];
                $tahun = '2024';"""

content = content.replace(old_nim_logic, new_nim_logic)

# ===== 4. Fix getMahasiswaById() SQL =====
old_byid = """            SELECT 
                m.id_mahasiswa, m.nim, m.nama_mahasiswa, m.status, m.golongan,
                d.nik, d.jenis_kelamin, d.tempat_tanggal_lahir, 
                d.asal_sekolah, d.jurusan_asal, d.no_hp, d.email, d.alamat, d.penghasilan_orangtua,
                d.pekerjaan_ayah, d.pekerjaan_ibu, d.penghasilan_ayah, d.penghasilan_ibu,
                d.aset_kendaraan, d.aset_bangunan, d.uang_makan_harian
            FROM mahasiswa m
            LEFT JOIN data_mahasiswa d ON m.id_mahasiswa = d.id_mahasiswa
            WHERE m.id_mahasiswa = '$id'"""

new_byid = """            SELECT 
                m.id_mahasiswa, m.nama_mahasiswa, m.status,
                d.nik, d.jenis_kelamin, d.tempat_tanggal_lahir, 
                d.asal_sekolah, d.jurusan_asal, d.no_hp, d.email, d.alamat
            FROM mahasiswa m
            LEFT JOIN data_mahasiswa d ON m.id_mahasiswa = d.id_mahasiswa
            WHERE m.id_mahasiswa = '$id'"""

content = content.replace(old_byid, new_byid)

# ===== 5. Fix createMahasiswa() - remove nim logic, remove golongan, remove beasiswa fields =====
# Remove nim extraction and duplicate check
old_create_nim = """        $nim = trim($mhsData['nim'] ?? '');
        
        // Auto-Generate NIM jika kosong
        if (empty($nim)) {
            $nim = $this->generateNIM();
        }

        // 1. Validasi Duplikat NIM
        $nim = mysqli_real_escape_string($this->db, $nim);
        $cekNIM = mysqli_query($this->db, \"SELECT nim FROM mahasiswa WHERE nim = '$nim'\");
        if ($cekNIM && mysqli_num_rows($cekNIM) > 0) {
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['error_msg'] = \"Gagal! NIM '$nim' sudah terdaftar dalam sistem.\";
            return false;
        }

        // 2. Validasi Duplikat NIK"""

new_create_nim = """        // 1. Validasi Duplikat NIK"""

content = content.replace(old_create_nim, new_create_nim)

# Remove golongan from createMahasiswa
old_create_golongan = """        $nama = mysqli_real_escape_string($this->db, $mhsData['nama']);
        $golongan = mysqli_real_escape_string($this->db, $mhsData['golongan'] ?? 'Umum');
        $status = 'Menunggu';

        $qMhs = mysqli_query($this->db, \"INSERT INTO mahasiswa (nim, nama_mahasiswa, status, golongan) VALUES ('$nim', '$nama', '$status', '$golongan')\");"""

new_create_golongan = """        $nama = mysqli_real_escape_string($this->db, $mhsData['nama']);
        $status = 'Menunggu';
        $nim = $this->generateNIM();
        $nim = mysqli_real_escape_string($this->db, $nim);

        $qMhs = mysqli_query($this->db, \"INSERT INTO mahasiswa (nim, nama_mahasiswa, status) VALUES ('$nim', '$nama', '$status')\");"""

content = content.replace(old_create_golongan, new_create_golongan)

# Remove beasiswa fields from INSERT in createMahasiswa
old_create_insert = """        $penghasilan = !empty($detailData['penghasilan_orangtua']) ? mysqli_real_escape_string($this->db, $detailData['penghasilan_orangtua']) : '-';

        $pekerjaan_ayah = !empty($detailData['pekerjaan_ayah']) ? mysqli_real_escape_string($this->db, $detailData['pekerjaan_ayah']) : '-';
        $pekerjaan_ibu = !empty($detailData['pekerjaan_ibu']) ? mysqli_real_escape_string($this->db, $detailData['pekerjaan_ibu']) : '-';
        
        $sql_penghasilan_ayah = $this->prepareSqlInt($detailData['penghasilan_ayah'] ?? null);
        $sql_penghasilan_ibu  = $this->prepareSqlInt($detailData['penghasilan_ibu'] ?? null);
        $sql_aset_kendaraan   = $this->prepareSqlInt($detailData['aset_kendaraan'] ?? null);
        $sql_aset_bangunan    = $this->prepareSqlInt($detailData['aset_bangunan'] ?? null);
        $sql_uang_makan       = $this->prepareSqlInt($detailData['uang_makan_harian'] ?? null);

        $qDetail = mysqli_query($this->db, \"
            INSERT INTO data_mahasiswa (
                id_mahasiswa, nik, jenis_kelamin, tempat_tanggal_lahir, asal_sekolah, jurusan_asal, 
                no_hp, email, alamat, penghasilan_orangtua, golongan,
                pekerjaan_ayah, pekerjaan_ibu, penghasilan_ayah, penghasilan_ibu, 
                aset_kendaraan, aset_bangunan, uang_makan_harian
            )
            VALUES (
                '$id_new', '$nik', '$jk', '$ttl', '$sekolah', '$jurusan', 
                '$hp', '$email', '$alamat', '$penghasilan', '$golongan',
                '$pekerjaan_ayah', '$pekerjaan_ibu', $sql_penghasilan_ayah, $sql_penghasilan_ibu,
                $sql_aset_kendaraan, $sql_aset_bangunan, $sql_uang_makan
            )
        \");"""

new_create_insert = """        $qDetail = mysqli_query($this->db, \"
            INSERT INTO data_mahasiswa (
                id_mahasiswa, nik, jenis_kelamin, tempat_tanggal_lahir, asal_sekolah, jurusan_asal, 
                no_hp, email, alamat
            )
            VALUES (
                '$id_new', '$nik', '$jk', '$ttl', '$sekolah', '$jurusan', 
                '$hp', '$email', '$alamat'
            )
        \");"""

content = content.replace(old_create_insert, new_create_insert)

# ===== 6. Fix updateMahasiswa() - remove nim logic, remove golongan, remove beasiswa fields =====
old_update_nim = """        $id = (int)$id;
        $rawNim = trim($mhsData['nim'] ?? '');
        if (!empty($rawNim)) {
            $nim = mysqli_real_escape_string($this->db, $rawNim);
            // Validasi Duplikat NIM saat Edit (Abaikan ID diri sendiri)
            $cekNIM = mysqli_query($this->db, \"SELECT nim FROM mahasiswa WHERE nim = '$nim' AND id_mahasiswa != '$id'\");
            if (mysqli_num_rows($cekNIM) > 0) {
                if (session_status() === PHP_SESSION_NONE) session_start();
                $_SESSION['error_msg'] = \"Gagal Edit! NIM '$nim' sudah dipakai oleh pendaftar lain.\";
                return false;
            }
        } else {
            $qOld = mysqli_query($this->db, \"SELECT nim FROM mahasiswa WHERE id_mahasiswa = '$id'\");
            $rowOld = mysqli_fetch_assoc($qOld);
            $nim = $rowOld['nim'] ?? '';
        }

        // Validasi Duplikat NIK saat Edit"""

new_update_nim = """        $id = (int)$id;

        // Validasi Duplikat NIK saat Edit"""

content = content.replace(old_update_nim, new_update_nim)

# Remove golongan from updateMahasiswa
old_update_golongan = """        $nama = mysqli_real_escape_string($this->db, $mhsData['nama']);
        $golongan = mysqli_real_escape_string($this->db, $mhsData['golongan'] ?? 'Umum');
        $status = mysqli_real_escape_string($this->db, $mhsData['status'] ?? 'Menunggu');

        mysqli_query($this->db, \"UPDATE mahasiswa SET nim='$nim', nama_mahasiswa='$nama', status='$status', golongan='$golongan' WHERE id_mahasiswa='$id'\");"""

new_update_golongan = """        $nama = mysqli_real_escape_string($this->db, $mhsData['nama']);
        $status = mysqli_real_escape_string($this->db, $mhsData['status'] ?? 'Menunggu');

        mysqli_query($this->db, \"UPDATE mahasiswa SET nama_mahasiswa='$nama', status='$status' WHERE id_mahasiswa='$id'\");"""

content = content.replace(old_update_golongan, new_update_golongan)

# Remove beasiswa vars from updateMahasiswa
old_update_beasiswa = """        $penghasilan = !empty($detailData['penghasilan_orangtua']) ? mysqli_real_escape_string($this->db, $detailData['penghasilan_orangtua']) : '-';

        $pekerjaan_ayah = !empty($detailData['pekerjaan_ayah']) ? mysqli_real_escape_string($this->db, $detailData['pekerjaan_ayah']) : '-';
        $pekerjaan_ibu = !empty($detailData['pekerjaan_ibu']) ? mysqli_real_escape_string($this->db, $detailData['pekerjaan_ibu']) : '-';
        
        $sql_penghasilan_ayah = $this->prepareSqlInt($detailData['penghasilan_ayah'] ?? null);
        $sql_penghasilan_ibu  = $this->prepareSqlInt($detailData['penghasilan_ibu'] ?? null);
        $sql_aset_kendaraan   = $this->prepareSqlInt($detailData['aset_kendaraan'] ?? null);
        $sql_aset_bangunan    = $this->prepareSqlInt($detailData['aset_bangunan'] ?? null);
        $sql_uang_makan       = $this->prepareSqlInt($detailData['uang_makan_harian'] ?? null);

        $cek = mysqli_query($this->db, \"SELECT id_data FROM data_mahasiswa WHERE id_mahasiswa='$id'\");
        if ($cek && mysqli_num_rows($cek) > 0) {
            mysqli_query($this->db, \"
                UPDATE data_mahasiswa SET 
                    nik='$nik', jenis_kelamin='$jk', tempat_tanggal_lahir='$ttl', 
                    asal_sekolah='$sekolah', jurusan_asal='$jurusan', no_hp='$hp', email='$email', alamat='$alamat',
                    penghasilan_orangtua='$penghasilan', golongan='$golongan',
                    pekerjaan_ayah='$pekerjaan_ayah', pekerjaan_ibu='$pekerjaan_ibu',
                    penghasilan_ayah=$sql_penghasilan_ayah, penghasilan_ibu=$sql_penghasilan_ibu,
                    aset_kendaraan=$sql_aset_kendaraan, aset_bangunan=$sql_aset_bangunan,
                    uang_makan_harian=$sql_uang_makan
                WHERE id_mahasiswa='$id'
            \");
        } else {
            mysqli_query($this->db, \"
                INSERT INTO data_mahasiswa (
                    id_mahasiswa, nik, jenis_kelamin, tempat_tanggal_lahir, asal_sekolah, jurusan_asal, 
                    no_hp, email, alamat, penghasilan_orangtua, golongan,
                    pekerjaan_ayah, pekerjaan_ibu, penghasilan_ayah, penghasilan_ibu, 
                    aset_kendaraan, aset_bangunan, uang_makan_harian
                )
                VALUES (
                    '$id', '$nik', '$jk', '$ttl', '$sekolah', '$jurusan', 
                    '$hp', '$email', '$alamat', '$penghasilan', '$golongan',
                    '$pekerjaan_ayah', '$pekerjaan_ibu', $sql_penghasilan_ayah, $sql_penghasilan_ibu,
                    $sql_aset_kendaraan, $sql_aset_bangunan, $sql_uang_makan
                )
            \");
        }"""

new_update_beasiswa = """        $cek = mysqli_query($this->db, \"SELECT id_data FROM data_mahasiswa WHERE id_mahasiswa='$id'\");
        if ($cek && mysqli_num_rows($cek) > 0) {
            mysqli_query($this->db, \"
                UPDATE data_mahasiswa SET 
                    nik='$nik', jenis_kelamin='$jk', tempat_tanggal_lahir='$ttl', 
                    asal_sekolah='$sekolah', jurusan_asal='$jurusan', no_hp='$hp', email='$email', alamat='$alamat'
                WHERE id_mahasiswa='$id'
            \");
        } else {
            mysqli_query($this->db, \"
                INSERT INTO data_mahasiswa (
                    id_mahasiswa, nik, jenis_kelamin, tempat_tanggal_lahir, asal_sekolah, jurusan_asal, 
                    no_hp, email, alamat
                )
                VALUES (
                    '$id', '$nik', '$jk', '$ttl', '$sekolah', '$jurusan', 
                    '$hp', '$email', '$alamat'
                )
            \");
        }"""

content = content.replace(old_update_beasiswa, new_update_beasiswa)

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print("MahasiswaModel.php cleaned successfully.")
# Show key sections
lines = content.split('\n')
print("\n--- getAllMahasiswaWithDetail SQL ---")
for i, line in enumerate(lines[107:140], 108):
    print(f"{i}: {line}")

