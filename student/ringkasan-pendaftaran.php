<?php
require_once '../config/database.php';
require_once 'includes/header.php';

$id_pendaftaran = intval($_GET['id']);

// Ambil data pendaftaran join ke webinar
$query = "SELECT p.*, w.judul, w.tanggal, w.waktu_mulai, w.platform, w.link_group, w.pembicara, m.nama_mahasiswa
          FROM pemantauan_webinar p 
          JOIN webinar w ON p.id_webinar = w.id_webinar 
          JOIN mahasiswa m ON p.npp = m.npp 
          WHERE p.id_pendaftaran = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $id_pendaftaran);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    header('Location: riwayat.php');
    exit();
}
?>

<div class="p-4 md:p-8 bg-slate-50 min-h-screen flex justify-center">
    <div class="max-w-2xl w-full">
        <div class="mb-6 flex items-center justify-between">
            <a href="riwayat.php" class="text-slate-500 hover:text-slate-800 font-bold text-sm flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <!-- <button onclick="window.print()" class="p-2 bg-white border border-slate-200 rounded-xl hover:bg-slate-100 transition-all">
                <i class="fas fa-print text-slate-600"></i>
            </button> -->
        </div>

        <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 overflow-hidden border border-slate-100">
            <div class="bg-<?= $data['status_pendaftaran'] == 'disetujui' ? 'teal' : 'rose' ?>-600 p-6 text-center text-white">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] opacity-80 mb-1">Status Pendaftaran</p>
                <h3 class="text-xl font-bold uppercase italic"><?= $data['status_pendaftaran'] ?></h3>
            </div>

            <div class="p-8 md:p-10">
                <div class="mb-8 text-center">
                    <h2 class="text-2xl font-black text-slate-800 leading-tight mb-2"><?= htmlspecialchars($data['judul']) ?></h2>
                    <p class="text-slate-500 font-medium">Bersama: <?= htmlspecialchars($data['pembicara']) ?></p>
                </div>

                <hr class="border-dashed border-slate-200 mb-8">

                <div class="grid grid-cols-2 gap-y-6 mb-8">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase mb-1">Nama Mahasiswa</p>
                        <p class="text-sm font-bold text-slate-800"><?= htmlspecialchars($data['nama_mahasiswa'] ?? 'User') ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-black text-slate-400 uppercase mb-1">NPP / NIM</p>
                        <p class="text-sm font-bold text-slate-800"><?= htmlspecialchars($data['npp']) ?></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase mb-1">Tanggal Pelaksanaan</p>
                        <p class="text-sm font-bold text-slate-800"><?= date('d M Y', strtotime($data['tanggal'])) ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-black text-slate-400 uppercase mb-1">Platform</p>
                        <p class="text-sm font-bold text-slate-800"><?= htmlspecialchars($data['platform']) ?></p>
                    </div>
                </div>

                <?php if ($data['status_pendaftaran'] == 'disetujui'): ?>
                    <div class="bg-emerald-50 border-2 border-emerald-100 rounded-3xl p-6 text-center">
                        <p class="text-xs font-black text-emerald-600 uppercase mb-3">Link Akses Grup WhatsApp</p>
                        <?php if (!empty($data['link_group'])): ?>
                            <a href="<?= $data['link_group'] ?>" target="_blank" class="inline-flex items-center gap-3 px-6 py-3 bg-emerald-500 text-white rounded-2xl font-black text-sm hover:bg-emerald-600 transition-all shadow-lg shadow-emerald-200">
                                <i class="fab fa-whatsapp text-lg"></i>
                                GABUNG GRUP SEKARANG
                            </a>
                        <?php else: ?>
                            <p class="text-sm text-slate-400 italic">Link belum disediakan oleh penyelenggara</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-rose-50 border border-rose-100 rounded-3xl p-6 text-center">
                        <p class="text-sm text-rose-600 font-bold italic">
                            Maaf, pendaftaran Anda ditolak. Silakan cek kembali kelengkapan data atau bukti pembayaran Anda.
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="bg-slate-50 p-6 border-t border-slate-100 text-center">
                <p class="text-[10px] font-bold text-slate-400 uppercase">ID Pendaftaran: #WEB-<?= $data['id_pendaftaran'] . $data['id_webinar'] ?></p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>