<?php
require_once '../config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['action'])) {
    $id = isset($_POST['id']) ? intval($_POST['id']) : intval($_GET['id'] ?? 0);
    $action = isset($_POST['action']) ? $_POST['action'] : ($_GET['action'] ?? '');
    
    $redirect_to = 'verifikasi-pendaftaran.php'; 

    if ($id > 0) {
        switch ($action) {
            case 'delete_webinar':
                $redirect_to = 'kelola-webinar.php';
                
                // 1. Ambil data status dan file QR
                $q_info = mysqli_query($conn, "SELECT status, qr_code FROM webinar WHERE id_webinar = $id");
                $data_w = mysqli_fetch_assoc($q_info);

                // 2. Cek apakah ada peserta
                $check_participants = mysqli_query($conn, "SELECT id_pendaftaran FROM pemantauan_webinar WHERE id_webinar = $id LIMIT 1");
                $has_participants = mysqli_num_rows($check_participants) > 0;

                // 3. Logika Pencegahan (Hanya allow delete jika closed atau tidak ada peserta)
                if ($data_w['status'] !== 'closed' && $has_participants) {
                    $_SESSION['error'] = "Webinar Aktif/Draft tidak bisa dihapus karena memiliki peserta!";
                    header("Location: " . $redirect_to);
                    exit();
                }

                // 4. JIKA LOLOS, Hapus peserta pendaftaran dulu agar tidak error Foreign Key
                if ($has_participants) {
                    mysqli_query($conn, "DELETE FROM pemantauan_webinar WHERE id_webinar = $id");
                }

                // 5. Set variabel untuk eksekusi final di bawah switch
                $query = "DELETE FROM webinar WHERE id_webinar = ?";
                $success_msg = "Webinar dan seluruh data terkait berhasil dihapus!";
                $file_to_delete = !empty($data_w['qr_code']) ? "../assets/img/qr/" . $data_w['qr_code'] : null;
                break; // Cukup satu break di sini

            case 'approve_registration':
                $query = "UPDATE pemantauan_webinar SET status_pendaftaran = 'disetujui' WHERE id_pendaftaran = ?";
                $success_msg = "Pendaftaran mahasiswa disetujui!";
                $redirect_to = 'verifikasi-pendaftaran.php';
                break;

            case 'reject_registration':
                $query = "UPDATE pemantauan_webinar SET status_pendaftaran = 'ditolak' WHERE id_pendaftaran = ?";
                $success_msg = "Pendaftaran mahasiswa telah ditolak!";
                $redirect_to = 'verifikasi-pendaftaran.php';
                break;

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

        // --- BAGIAN EKSEKUSI ---
        if (isset($query)) {
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'i', $id);
            
            if (mysqli_stmt_execute($stmt)) {
                // Hapus file fisik jika aksi adalah delete_webinar
                if ($action === 'delete_webinar' && isset($file_to_delete) && $file_to_delete && file_exists($file_to_delete)) {
                    unlink($file_to_delete);
                }
                $_SESSION['success'] = $success_msg;
            } else {
                $_SESSION['error'] = "Gagal memproses ke database: " . mysqli_error($conn);
            }
        }
    }
}

header("Location: " . $redirect_to);
exit();