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

 Date: 10/11/2026 16:30:06
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

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
  `id_data` int NOT NULL AUTO_INCREMENT,
  `nik` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `jenis_kelamin` enum('L','P') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `tempat_tanggal_lahir` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `asal_sekolah` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `jurusan_asal` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `no_hp` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `alamat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `status` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `golongan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `nama_mahasiswa` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `total` double NULL DEFAULT NULL,
  `TAS` double NULL DEFAULT NULL,
  `RSK` double NULL DEFAULT NULL,
  PRIMARY KEY (`id_data`) USING BTREE,
  UNIQUE INDEX `nik`(`nik` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of mahasiswa
-- ----------------------------
INSERT INTO `mahasiswa` VALUES (2, '3171011508980001', 'L', 'Denpasar, 1998-08-15', 'SMA 2', 'Teknik Informatika', '082247011652', '-', '-', 'Menunggu', 'Umum', 'Data Dummy', 0.635, 3.0, 0.6);

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
  INDEX `fk_nilai_sub`(`id_sub` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of nilai
-- ----------------------------
INSERT INTO `nilai` VALUES (1, 2, 1, 3, 82);
INSERT INTO `nilai` VALUES (2, 2, 2, 5, 0.4);
INSERT INTO `nilai` VALUES (3, 2, 3, 7, 0.2);
INSERT INTO `nilai` VALUES (4, 2, 4, 12, 78);
INSERT INTO `nilai` VALUES (5, 2, 5, 15, 360);

-- ----------------------------
-- Table structure for prodi
-- ----------------------------
DROP TABLE IF EXISTS `prodi`;
CREATE TABLE `prodi`  (
  `id_prodi` int NOT NULL,
  `nama_prodi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `kode_prodi` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id_prodi`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of prodi
-- ----------------------------
INSERT INTO `prodi` VALUES (1, 'Teknik Informatika', '');

-- ----------------------------
-- Table structure for program_studi
-- ----------------------------
DROP TABLE IF EXISTS `program_studi`;
CREATE TABLE `program_studi`  (
  `id_program` int NOT NULL AUTO_INCREMENT,
  `nama_program` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id_program`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of program_studi
-- ----------------------------
INSERT INTO `program_studi` VALUES (1, 'Teknik Informatika');

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
