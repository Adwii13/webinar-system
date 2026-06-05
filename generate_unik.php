<?php
require_once 'config/database.php';

// Ambil semua data mahasiswa yang belum punya password hash
$query_mhs = mysqli_query($conn, "SELECT npp FROM mahasiswa WHERE password NOT LIKE '$2y$%' OR password IS NULL");

$berhasil = 0;
while ($row = mysqli_fetch_assoc($query_mhs)) {
    $npp_Mhs = $row['npp'];
    
    // Hash NPP masing-masing mahasiswa menjadi password unik mereka sendiri
    $password_unik = password_hash($npp_Mhs, PASSWORD_DEFAULT);
    
    // Update ke database berdasarkan NPP
    mysqli_query($conn, "UPDATE mahasiswa SET password = '$password_unik' WHERE npp = '$npp_Mhs'");
    $berhasil++;
}

echo "Sukses! Sebanyak $berhasil mahasiswa kini memiliki password unik berupa NPP mereka masing-masing.";
?>