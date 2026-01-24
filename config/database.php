<?php

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'webinar_db';

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Fungsi untuk mencegah SQL injection
function clean_input($data) {
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(strip_tags(trim($data))));
}
?>