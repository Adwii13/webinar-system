<?php
require_once 'includes/admin-guard.php'; // Proteksi admin tetap aktif
require_once '../config/database.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['action'])) {
    $id = isset($_POST['id']) ? intval($_POST['id']) : intval($_GET['id'] ?? 0);
    $action = isset($_POST['action']) ? $_POST['action'] : ($_GET['action'] ?? '');
    
    $redirect_to = 'verifikasi-webinar.php'; 

    if ($id > 0) {
        switch ($action) {
            case 'approve_webinar':
                $query = "UPDATE webinar SET status_verifikasi = 'disetujui', status = 'publish' WHERE id_webinar = ?";
                $success_msg = "Webinar telah disetujui dan dipublikasikan!";
                $redirect_to = 'verifikasi-webinar.php'; 
                break;

            case 'reject_webinar':
                $query = "UPDATE webinar SET status_verifikasi = 'ditolak', status = 'draft' WHERE id_webinar = ?";
                $success_msg = "Pengajuan webinar telah ditolak.";
                $redirect_to = 'verifikasi-webinar.php';
                break;
        }

        // --- BAGIAN EKSEKUSI ADMIN ---
        if (isset($query)) {
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'i', $id);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success'] = $success_msg;
            } else {
                $_SESSION['error'] = "Gagal memproses ke database: " . mysqli_error($conn);
            }
        }
    }
}

header("Location: " . $redirect_to);
exit();