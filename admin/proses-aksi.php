<?php
require_once '../config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['action'])) {
    // Ambil data dari POST (untuk form) atau GET (untuk link)
    $id = isset($_POST['id']) ? intval($_POST['id']) : intval($_GET['id'] ?? 0);
    $action = isset($_POST['action']) ? $_POST['action'] : ($_GET['action'] ?? '');
    
    $redirect_to = 'verifikasi-pendaftaran.php'; // Default redirect

    if ($id > 0) {
        switch ($action) {
            // --- AKSI UNTUK PENDAFTARAN MAHASISWA ---
            case 'approve_registration':
                $query = "UPDATE pemantauan_webinar SET status_pendaftaran = 'disetujui' WHERE id_pendaftaran = ?";
                $redirect_to = 'verifikasi-pendaftaran.php';
                break;

            case 'reject_registration':
                $query = "UPDATE pemantauan_webinar SET status_pendaftaran = 'ditolak' WHERE id_pendaftaran = ?";
                $redirect_to = 'verifikasi-pendaftaran.php';
                break;

            // --- AKSI UNTUK VERIFIKASI WEBINAR (PENYELENGGARA) ---
            case 'approve_webinar':
                $query = "UPDATE webinar SET status_verifikasi = 'disetujui', status = 'publish' WHERE id_webinar = ?";
                // Karena ini verifikasi webinar, arahkan kembali ke halaman verifikasi webinar
                $redirect_to = 'verifikasi-webinar.php'; 
                break;

            case 'reject_webinar':
                $query = "UPDATE webinar SET status_verifikasi = 'ditolak', status = 'draft' WHERE id_webinar = ?";
                $redirect_to = 'verifikasi-webinar.php';
                break;
        }

        // Eksekusi Query
        if (isset($query)) {
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'i', $id);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success'] = "Aksi berhasil diproses!";
            } else {
                $_SESSION['error'] = "Terjadi kesalahan saat memproses data.";
            }
        }
    }
}

// Redirect dinamis sesuai jenis aksi
header("Location: " . $redirect_to);
exit();