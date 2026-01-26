<?php
require_once '../config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['action'])) {
    $id = isset($_POST['id']) ? intval($_POST['id']) : intval($_GET['id'] ?? 0);
    $action = isset($_POST['action']) ? $_POST['action'] : ($_GET['action'] ?? '');
    
    $redirect_to = 'verifikasi-pendaftaran.php'; // Default fallback

    if ($id > 0) {
        switch ($action) {
            // --- AKSI HAPUS WEBINAR ---
            case 'delete_webinar':

                $check_participants = mysqli_query($conn, "SELECT id_pendaftaran FROM pemantauan_webinar WHERE id_webinar = $id");
                // 1. Cari nama file QR dulu supaya tidak jadi sampah di folder
                if (mysqli_num_rows($check_participants) > 0) {
                    $_SESSION['error'] = "Webinar tidak bisa dihapus karena sudah memiliki peserta!";
                    header("Location: kelola-webinar.php");
                    exit();
                }

                // Hapus file QR jika ada
                $q_file = mysqli_query($conn, "SELECT qr_code FROM webinar WHERE id_webinar = $id");
                $data_w = mysqli_fetch_assoc($q_file);
                if ($data_w && !empty($data_w['qr_code'])) {
                    $path = "../assets/img/qr/" . $data_w['qr_code'];
                    if (file_exists($path)) unlink($path);
                }

                $query = "DELETE FROM webinar WHERE id_webinar = ?";
                $redirect_to = 'kelola-webinar.php';
                $success_msg = "Webinar berhasil dihapus selamanya!";
                break;

            case 'approve_registration':
                $query = "UPDATE pemantauan_webinar SET status_pendaftaran = 'disetujui' WHERE id_pendaftaran = ?";
                $success_msg = "Pendaftaran mahasiswa disetujui!";
                $redirect_to = 'verifikasi-pendaftaran.php';
                break;

            case 'reject_registration':
                $query = "UPDATE pemantauan_webinar SET status_pendaftaran = 'ditolak' WHERE id_pendaftaran = ?";
                $redirect_to = 'verifikasi-pendaftaran.php';
                break;

            case 'approve_webinar':
                $query = "UPDATE webinar SET status_verifikasi = 'disetujui', status = 'publish' WHERE id_webinar = ?";
                $redirect_to = 'verifikasi-webinar.php'; 
                break;

            case 'reject_webinar':
                $query = "UPDATE webinar SET status_verifikasi = 'ditolak', status = 'draft' WHERE id_webinar = ?";
                $redirect_to = 'verifikasi-webinar.php';
                break;
        }

        // Eksekusi Query jika ada
        if (isset($query)) {
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'i', $id);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success'] = $success_msg ?? "Aksi berhasil diproses!";
            } else {
                // Jika error, kemungkinan ada Foreign Key (webinar masih punya peserta)
                $_SESSION['error'] = "Gagal memproses.";
            }
        }
    }
}

header("Location: " . $redirect_to);
exit();