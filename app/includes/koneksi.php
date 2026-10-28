<?php
// app/includes/koneksi.php

$host = 'localhost';
$user = 'root'; // User default Laragon
$pass = '';     // Password default Laragon
$db   = 'pmbDB'; // Nama database diperbarui

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>