/*
 Navicat Premium Dump SQL

 Source Server         : my_connect
 Source Server Type    : MySQL
 Source Server Version : 80030 (8.0.30)
 Source Host           : localhost:3306
 Source Schema         : pmbdb

 Target Server Type    : MySQL
 Target Server Version : 80030 (8.0.30)
 File Encoding         : 65001

 Date: 01/11/2026 03:03:47
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for data_mahasiswa
-- ----------------------------
DROP TABLE IF EXISTS `data_mahasiswa`;
CREATE TABLE `data_mahasiswa`  (
  `id_data` int NOT NULL AUTO_INCREMENT,
  `id_mahasiswa` int NOT NULL,
  `nik` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `jenis_kelamin` enum('L','P') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `tempat_tanggal_lahir` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `asal_sekolah` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `jurusan_asal` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `no_hp` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `alamat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `penghasilan_orangtua` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `golongan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id_data`) USING BTREE,
  UNIQUE INDEX `nik`(`nik` ASC) USING BTREE,
  INDEX `fk_data_mahasiswa`(`id_mahasiswa` ASC) USING BTREE,
  CONSTRAINT `fk_data_mahasiswa` FOREIGN KEY (`id_mahasiswa`) REFERENCES `mahasiswa` (`id_mahasiswa`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of data_mahasiswa
-- ----------------------------
DELIMITER $$
CREATE PROCEDURE InsertDummyMahasiswa()
BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE v_id_mhs INT;
    
    WHILE i <= 100 DO
        -- 1. Insert ke tabel mahasiswa utama
        INSERT INTO mahasiswa (nim, nama_mahasiswa, status, golongan) 
        VALUES (
            CONCAT('22010', LPAD(i, 3, '0')), 
            CONCAT('Mahasiswa Dummy ', i), 
            IF(i % 5 = 0, 'Diterima', 'Ditolak'), 
            IF(i % 3 = 0, 'Beasiswa', 'Umum')
        );
        
        SET v_id_mhs = LAST_INSERT_ID();
        
        -- 2. Insert ke tabel data_mahasiswa (Relasi detail)
        INSERT INTO data_mahasiswa (id_mahasiswa, nik, jenis_kelamin, tempat_tanggal_lahir, asal_sekolah, jurusan_asal, no_hp, email, alamat, penghasilan_orangtua, golongan)
        VALUES (
            v_id_mhs, 
            CONCAT('3201', LPAD(i, 12, '0')), 
            IF(i % 2 = 0, 'L', 'P'), 
            'Bandung, 01-01-2004', 
            'SMA Negeri 1 Dummy', 
            IF(i % 2 = 0, 'Teknik Informatika', 'Sistem Informasi'), 
            CONCAT('08123456', LPAD(i, 4, '0')), 
            CONCAT('dummy', i, '@mail.com'), 
            CONCAT('Jl. Dummy Data No. ', i), 
            IF(i % 3 = 0, '< Rp 1.500.000 / bulan', 'Rp 3.000.000 - Rp 5.000.000 / bulan'), 
            IF(i % 3 = 0, 'Beasiswa', 'Umum')
        );
        
        -- 3. Insert ke tabel nilai (Otomatis memilih sub_kriteria acak/terpola)
        INSERT INTO nilai (id_mahasiswa, id_kriteria, id_sub, nilai) VALUES (v_id_mhs, 1, IF(i % 3 = 0, 3, 2), NULL);
        INSERT INTO nilai (id_mahasiswa, id_kriteria, id_sub, nilai) VALUES (v_id_mhs, 2, IF(i % 3 = 0, 6, 5), NULL);
        INSERT INTO nilai (id_mahasiswa, id_kriteria, id_sub, nilai) VALUES (v_id_mhs, 3, IF(i % 3 = 0, 9, 8), NULL);
        INSERT INTO nilai (id_mahasiswa, id_kriteria, id_sub, nilai) VALUES (v_id_mhs, 4, IF(i % 3 = 0, 12, 11), NULL);
        INSERT INTO nilai (id_mahasiswa, id_kriteria, id_sub, nilai) VALUES (v_id_mhs, 5, IF(i % 3 = 0, 15, 14), NULL);
        
        SET i = i + 1;
    END WHILE;
END$$
DELIMITER ;

-- Eksekusi Procedure
CALL InsertDummyMahasiswa();

-- Hapus Procedure setelah selesai digunakan
DROP PROCEDURE InsertDummyMahasiswa;

-- ----------------------------
-- Table structure for kriteria
-- ----------------------------
DROP TABLE IF EXISTS `kriteria`;
CREATE TABLE `kriteria`  (
  `id_kriteria` int NOT NULL AUTO_INCREMENT,
  `nama_kriteria` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `jenis` enum('Benefit','Cost') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'Benefit',
  `bobot` float NOT NULL,
  PRIMARY KEY (`id_kriteria`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of kriteria
-- ----------------------------
INSERT INTO `kriteria` VALUES (1, 'Nilai Rapor', 'Benefit', 25);
INSERT INTO `kriteria` VALUES (2, 'Prestasi Akademik', 'Benefit', 20);
INSERT INTO `kriteria` VALUES (3, 'Prestasi Non Akademik', 'Benefit', 15);
INSERT INTO `kriteria` VALUES (4, 'Nilai TKA', 'Benefit', 25);
INSERT INTO `kriteria` VALUES (5, 'Skor Bahasa Inggris', 'Benefit', 15);

-- ----------------------------
-- Table structure for mahasiswa
-- ----------------------------
DROP TABLE IF EXISTS `mahasiswa`;
CREATE TABLE `mahasiswa`  (
  `id_mahasiswa` int NOT NULL AUTO_INCREMENT,
  `nim` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nama_mahasiswa` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `status` enum('Diterima','Ditolak') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'Ditolak',
  `golongan` enum('Umum','Beasiswa') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'Umum',
  PRIMARY KEY (`id_mahasiswa`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of mahasiswa
-- ----------------------------
INSERT INTO `mahasiswa` VALUES (1, '220101001', 'Ahmad Rizky', 'Ditolak', 'Umum');
INSERT INTO `mahasiswa` VALUES (2, '220101002', 'Budi Santoso', 'Ditolak', 'Umum');
INSERT INTO `mahasiswa` VALUES (3, '210101003', 'Citra Kirana', 'Ditolak', 'Umum');
INSERT INTO `mahasiswa` VALUES (4, '2355201020', 'Yoga Prakusya', 'Ditolak', 'Umum');

-- ----------------------------
-- Table structure for nilai
-- ----------------------------
DROP TABLE IF EXISTS `nilai`;
CREATE TABLE `nilai`  (
  `id_nilai` int NOT NULL AUTO_INCREMENT,
  `id_mahasiswa` int NOT NULL,
  `id_kriteria` int NOT NULL,
  `id_sub` int NOT NULL,
  `nilai` float NULL DEFAULT NULL,
  PRIMARY KEY (`id_nilai`) USING BTREE,
  INDEX `fk_nilai_mahasiswa`(`id_mahasiswa` ASC) USING BTREE,
  INDEX `fk_nilai_kriteria`(`id_kriteria` ASC) USING BTREE,
  INDEX `fk_nilai_sub`(`id_sub` ASC) USING BTREE,
  CONSTRAINT `fk_nilai_kriteria` FOREIGN KEY (`id_kriteria`) REFERENCES `kriteria` (`id_kriteria`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_nilai_mahasiswa` FOREIGN KEY (`id_mahasiswa`) REFERENCES `mahasiswa` (`id_mahasiswa`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_nilai_sub` FOREIGN KEY (`id_sub`) REFERENCES `sub_kriteria` (`id_sub`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of nilai
-- ----------------------------

-- ----------------------------
-- Table structure for prodi
-- ----------------------------
DROP TABLE IF EXISTS prodi;

CREATE TABLE prodi (
    id_prodi INT AUTO_INCREMENT PRIMARY KEY,
    nama_prodi VARCHAR(100) NOT NULL
);

INSERT INTO prodi (nama_prodi) VALUES 
('S1 Teknik Informatika'), 
('S1 Sistem Informasi'), 
('S1 Manajemen'), 
('S1 Akuntansi'), 
('S1 Pariwisata'), 
('S1 Pendidikan Guru Sekolah Dasar (PGSD)'), 
('S1 Pendidikan Kepelatihan Olahraga (PKO)'), 
('S1 Pendidikan Bahasa Inggris');
-- ----------------------------
-- Records of prodi
-- ----------------------------

-- ----------------------------
-- Table structure for sub_kriteria
-- ----------------------------
DROP TABLE IF EXISTS `sub_kriteria`;
CREATE TABLE `sub_kriteria`  (
  `id_sub` int NOT NULL AUTO_INCREMENT,
  `id_kriteria` int NOT NULL,
  `nama_sub` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nilai` float NOT NULL,
  PRIMARY KEY (`id_sub`) USING BTREE,
  INDEX `id_kriteria`(`id_kriteria` ASC) USING BTREE,
  CONSTRAINT `sub_kriteria_ibfk_1` FOREIGN KEY (`id_kriteria`) REFERENCES `kriteria` (`id_kriteria`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 16 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of sub_kriteria
-- ----------------------------
INSERT INTO `sub_kriteria` VALUES (1, 1, '< 70', 0.25);
INSERT INTO `sub_kriteria` VALUES (2, 1, '70 <= nilai < 80', 0.5);
INSERT INTO `sub_kriteria` VALUES (3, 1, '>= 80', 0.75);
INSERT INTO `sub_kriteria` VALUES (4, 2, 'Lokal', 0.2);
INSERT INTO `sub_kriteria` VALUES (5, 2, 'Nasional', 0.4);
INSERT INTO `sub_kriteria` VALUES (6, 2, 'Internasional', 0.8);
INSERT INTO `sub_kriteria` VALUES (7, 3, 'Lokal', 0.2);
INSERT INTO `sub_kriteria` VALUES (8, 3, 'Nasional', 0.4);
INSERT INTO `sub_kriteria` VALUES (9, 3, 'Internasional', 0.8);
INSERT INTO `sub_kriteria` VALUES (10, 4, '< 65', 0.3);
INSERT INTO `sub_kriteria` VALUES (11, 4, '65 <= nilai < 75', 0.6);
INSERT INTO `sub_kriteria` VALUES (12, 4, '>= 75', 0.9);
INSERT INTO `sub_kriteria` VALUES (13, 5, '< 300', 0.25);
INSERT INTO `sub_kriteria` VALUES (14, 5, '300 <= nilai < 350', 0.5);
INSERT INTO `sub_kriteria` VALUES (15, 5, '>= 350', 0.75);

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `password` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (1, 'admin', '506a0f5a91de41b4486d46325d1ebc420910146cc4f30f932e8982b6b10972f39a06f487cf606d55123715f478af6919d81ca6e71f80be6c863ee4eadea50208');

SET FOREIGN_KEY_CHECKS = 1;
