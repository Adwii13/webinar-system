<?php
require_once 'config/database.php';

echo "<h2>Proses Inisialisasi Password Hash</h2>";

// 1. UPDATE PASSWORD ADMIN
$password_admin_plain = 'admin123';
$hash_admin = password_hash($password_admin_plain, PASSWORD_DEFAULT);

// Kita update semua admin yang password-nya belum di-hash
$query_admin = "UPDATE admin SET password = '$hash_admin' WHERE password NOT LIKE '$2y$%' OR password IS NULL";
if (mysqli_query($conn, $query_admin)) {
    $affected = mysqli_affected_rows($conn);
    if ($affected > 0) {
        echo "<p style='color: green;'>✔️ Sukses: Password Admin berhasil di-hash menjadi <strong>$password_admin_plain</strong>.</p>";
    } else {
        echo "<p style='color: orange;'>ℹ️ Info: Password Admin sudah dalam bentuk hash (tidak perlu di-update).</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Gagal update Admin: " . mysqli_error($conn) . "</p>";
}


// 2. UPDATE PASSWORD PENYELENGGARA
// Ambil semua data penyelenggara
$q_penyelenggara = mysqli_query($conn, "SELECT id_penyelenggara, nama_penyelenggara, username FROM penyelenggara");

if (mysqli_num_rows($q_penyelenggara) > 0) {
    $berhasil_penyelenggara = 0;
    
    while ($row = mysqli_fetch_assoc($q_penyelenggara)) {
        $id_peny = $row['id_penyelenggara'];
        
        // Cek jika username kosong, kita buatkan username default berdasarkan namanya (tanpa spasi, huruf kecil)
        $username = $row['username'];
        if (empty($username)) {
            $username = strtolower(str_replace(' ', '', $row['nama_penyelenggara']));
        }
        
        // Set password default untuk penyelenggara
        $password_peny_plain = 'penyelenggara123'; 
        $hash_peny = password_hash($password_peny_plain, PASSWORD_DEFAULT);
        
        // Update username (jika tadinya kosong) dan password hash-nya
        $query_update = "UPDATE penyelenggara 
                         SET username = '$username', password = '$hash_peny' 
                         WHERE id_penyelenggara = $id_peny AND (password NOT LIKE '$2y$%' OR password IS NULL)";
        
        if (mysqli_query($conn, $query_update)) {
            if (mysqli_affected_rows($conn) > 0) {
                $berhasil_penyelenggara++;
                echo "<p>🔹 Penyelenggara <strong>{$row['nama_penyelenggara']}</strong> -> Username: <code>$username</code> | Password: <code>$password_peny_plain</code> (Berhasil di-hash)</p>";
            }
        }
    }
    
    if ($berhasil_penyelenggara == 0) {
        echo "<p style='color: orange;'>ℹ️ Info: Semua password Penyelenggara sudah berupa hash.</p>";
    }
} else {
    echo "<p style='color: red;'>ℹ️ Info: Tidak ada data penyelenggara di database.</p>";
}
?>