<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Khusus mengunci halaman untuk Role Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../index.php");
    exit();
}