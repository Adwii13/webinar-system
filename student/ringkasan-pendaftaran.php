<?php
require_once 'includes/guard-mahasiswa.php';
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proteksi halaman: Pastikan hanya mahasiswa login yang bisa mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../index.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: riwayat.php');
    exit();
}

$id_pendaftaran = intval($_GET['id']);
$session_npp = $_SESSION['npp']; // Ambil NPP dari session login aktif

// Ambil data pendaftaran join ke webinar + Proteksi Akun (WHERE p.npp = ?)
$query = "SELECT p.*, w.judul, w.tanggal, w.waktu, w.platform, w.link_group, w.pembicara, m.nama_mahasiswa
          FROM pemantauan_webinar p 
          JOIN webinar w ON p.id_webinar = w.id_webinar 
          JOIN mahasiswa m ON p.npp = m.npp 
          WHERE p.id_pendaftaran = ? AND p.npp = ?"; // Mencegah manipulasi URL oleh user lain

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'is', $id_pendaftaran, $session_npp);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

// Jika data tidak ditemukan atau pendaftaran ini bukan milik user yang login
if (!$data) {
    header('Location: riwayat.php');
    exit();
}

// Pengondisian komponen warna badge status pendaftaran
$status_color = 'amber'; // Default: menunggu
if ($data['status_pendaftaran'] == 'disetujui') {
    $status_color = 'teal';
} elseif ($data['status_pendaftaran'] == 'ditolak') {
    $status_color = 'rose';
}

require_once 'includes/header.php';
?>

<div class="p-4 md:p-8 bg-slate-50 min-h-screen flex justify-center">
    <div class="max-w-2xl w-full">
        <div class="mb-6 flex items-center justify-between">
            <a href="riwayat.php" class="inline-flex items-center gap-2 text-slate-500 hover:text-teal-600 font-bold transition-colors group">
                <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center group-hover:bg-teal-50 group-hover:border-teal-200">
                    <i class="fas fa-arrow-left text-xs"></i>
                </div>
                Kembali ke Riwayat
            </a>
            <button onclick="window.print()" class="p-2 px-4 bg-white border border-slate-200 text-xs font-bold rounded-xl hover:bg-slate-100 transition-all text-slate-600 flex items-center gap-2">
                <i class="fas fa-print"></i> Cetak Bukti
            </button>
        </div>

        <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 overflow-hidden border border-slate-100">
            <div class="bg-<?= $status_color ?>-600 p-6 text-center text-white transition-colors">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] opacity-80 mb-1">Status Pendaftaran</p>
                <h3 class="text-xl font-bold uppercase italic tracking-wider"><?= htmlspecialchars($data['status_pendaftaran']) ?></h3>
            </div>

            <div class="p-8 md:p-10">
                <div class="mb-8 text-center">
                    <h2 class="text-2xl font-black text-slate-800 leading-tight mb-2"><?= htmlspecialchars($data['judul']) ?></h2>
                    <p class="text-slate-500 font-medium text-sm">Bersama: <?= htmlspecialchars($data['pembicara']) ?></p>
                </div>

                <hr class="border-dashed border-slate-200 mb-8">

                <div class="grid grid-cols-2 gap-y-6 mb-8 text-sm">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase mb-1">Nama Mahasiswa</p>
                        <p class="font-bold text-slate-800"><?= htmlspecialchars($data['nama_mahasiswa']) ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-black text-slate-400 uppercase mb-1">NPP / NIM</p>
                        <p class="font-bold text-slate-800 font-mono"><?= htmlspecialchars($data['npp']) ?></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase mb-1">Tanggal Pelaksanaan</p>
                        <p class="font-bold text-slate-800"><?= date('d M Y', strtotime($data['tanggal'])) ?> - <?= date('H:i', strtotime($data['waktu'])) ?> WIB</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-black text-slate-400 uppercase mb-1">Platform</p>
                        <p class="font-bold text-slate-700"><?= htmlspecialchars($data['platform']) ?></p>
                    </div>
                </div>

                <?php if ($data['status_pendaftaran'] == 'disetujui'): ?>
                    <div class="bg-emerald-50 border-2 border-emerald-100 rounded-3xl p-6 text-center">
                        <p class="text-xs font-black text-emerald-600 uppercase mb-3 tracking-wider">Link Akses Grup WhatsApp Peserta</p>
                        <?php if (!empty($data['link_group'])): ?>
                            <a href="<?= htmlspecialchars($data['link_group']) ?>" target="_blank" class="inline-flex items-center gap-3 px-6 py-3 bg-emerald-500 text-white rounded-2xl font-black text-sm hover:bg-emerald-600 transition-all shadow-lg shadow-emerald-200 uppercase tracking-wider">
                                <i class="fab fa-whatsapp text-lg"></i>
                                Gabung Grup Sekarang
                            </a>
                        <?php else: ?>
                            <p class="text-sm text-slate-400 italic">Link grup belum disediakan oleh panitia penyelenggara.</p>
                        <?php endif; ?>
                    </div>
                <?php elseif ($data['status_pendaftaran'] == 'menunggu'): ?>
                    <div class="bg-amber-50 border border-amber-200 rounded-3xl p-6 text-center">
                        <p class="text-sm text-amber-700 font-bold flex flex-col items-center gap-2">
                            <span><i class="fas fa-spinner fa-spin mr-1"></i> Data Anda sedang diperiksa oleh Admin UNIBI.</span>
                            <span class="text-xs text-slate-400 font-normal">Grup koordinasi akan muncul di halaman ini setelah pendaftaran disetujui.</span>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="bg-rose-50 border border-rose-200 rounded-3xl p-6 text-center">
                        <p class="text-sm text-rose-600 font-bold italic mb-1">
                            Maaf, pendaftaran Anda ditolak.
                        </p>
                        <p class="text-xs text-slate-400">Silakan cek riwayat Anda untuk melakukan perbaikan data atau unggah ulang bukti transaksi pembayaran.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="bg-slate-50 p-6 border-t border-slate-100 text-center text-xs text-slate-400 font-mono">
                <p>ID Pendaftaran: #WEB-<?= str_pad($data['id_pendaftaran'], 4, '0', STR_PAD_LEFT) ?></p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>