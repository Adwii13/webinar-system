<?php
require_once 'includes/admin-guard.php';
require_once '../config/database.php';
require_once 'includes/header.php';

// Ambil statistik dari database
$query_stats = "SELECT 
    (SELECT COUNT(*) FROM webinar) as total_webinar,
    (SELECT COUNT(*) FROM pemantauan_webinar WHERE status_pendaftaran = 'menunggu') as pending,
    (SELECT COUNT(*) FROM pemantauan_webinar WHERE status_pendaftaran = 'disetujui') as total_peserta,
    (SELECT COUNT(*) FROM webinar WHERE status = 'publish') as webinar_aktif";

$result_stats = mysqli_query($conn, $query_stats);
$stats = mysqli_fetch_assoc($result_stats);

// Ambil pendaftaran menunggu (Gunakan LIMIT untuk efisiensi dashboard)
$query_pendaftaran = "SELECT p.*, w.judul, m.nama_mahasiswa 
                      FROM pemantauan_webinar p 
                      JOIN webinar w ON p.id_webinar = w.id_webinar 
                      JOIN mahasiswa m ON p.npp = m.npp
                      WHERE p.status_pendaftaran = 'menunggu' 
                      ORDER BY p.tanggal_daftar DESC LIMIT 2";
$result_pendaftaran = mysqli_query($conn, $query_pendaftaran);

// Ambil webinar terbaru
$query_webinars = "SELECT * FROM webinar ORDER BY tanggal DESC LIMIT 2";
$result_webinars = mysqli_query($conn, $query_webinars);
?>

<div class="p-4 md:p-8 bg-slate-50 min-h-screen">
    <div class="bg-teal-600 rounded-[32px] p-8 md:p-10 text-white mb-10 shadow-lg shadow-teal-900/10 relative overflow-hidden">
        <div class="relative z-10">
            <h2 class="text-3xl md:text-4xl font-bold mb-2">
                Selamat Datang, <?= isset($_SESSION['nama_admin']) ? htmlspecialchars($_SESSION['nama_admin']) : 'Admin'; ?>!
            </h2>
            <p class="text-teal-50 text-base md:text-lg opacity-90">Kelola operasional webinar dan verifikasi pendaftaran mahasiswa.</p>
        </div>
        <div class="absolute -right-10 -top-10 w-64 h-64 bg-white/10 rounded-full hidden md:block"></div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100/60 flex justify-between items-start">
            <div>
                <p class="text-slate-400 text-sm font-medium">Total Webinar</p>
                <h3 class="text-3xl font-bold text-slate-800 mt-1"><?= number_format($stats['total_webinar'] ?? 0); ?></h3>
            </div>
            <div class="p-3 bg-blue-50 rounded-2xl text-blue-600 shrink-0">
                <i class="far fa-calendar-alt text-xl"></i>
            </div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100/60 flex justify-between items-start">
            <div>
                <p class="text-slate-400 text-sm font-medium">Pendaftaran Pending</p>
                <h3 class="text-3xl font-bold text-slate-800 mt-1"><?= number_format($stats['pending'] ?? 0); ?></h3>
            </div>
            <div class="p-3 bg-amber-50 rounded-2xl text-amber-600 shrink-0">
                <i class="far fa-clock text-xl"></i>
            </div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100/60 flex justify-between items-start">
            <div>
                <p class="text-slate-400 text-sm font-medium">Total Peserta</p>
                <h3 class="text-3xl font-bold text-slate-800 mt-1"><?= number_format($stats['total_peserta'] ?? 0); ?></h3>
            </div>
            <div class="p-3 bg-emerald-50 rounded-2xl text-emerald-600 shrink-0">
                <i class="fas fa-users text-xl"></i>
            </div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100/60 flex justify-between items-start">
            <div>
                <p class="text-slate-400 text-sm font-medium">Webinar Aktif</p>
                <h3 class="text-3xl font-bold text-slate-800 mt-1"><?= number_format($stats['webinar_aktif'] ?? 0); ?></h3>
            </div>
            <div class="p-3 bg-purple-50 rounded-2xl text-purple-600 shrink-0">
                <i class="fas fa-chart-line text-xl"></i>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2">
            <div class="bg-white rounded-[32px] shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-6 md:p-8 border-b border-slate-100 flex justify-between items-center gap-4">
                    <h3 class="text-xl font-bold text-slate-800">Pendaftaran Menunggu Persetujuan</h3>
                    <span class="bg-amber-50 text-amber-700 border border-amber-200/60 px-4 py-1 rounded-full text-xs font-bold shrink-0">
                        <?= intval($stats['pending'] ?? 0); ?> Pending
                    </span>
                </div>
                <div class="p-6 md:p-8 space-y-6">
                    <?php if (mysqli_num_rows($result_pendaftaran) > 0): ?>
                        <?php while($daftar = mysqli_fetch_assoc($result_pendaftaran)): ?>
                        <div class="bg-white border border-slate-100 rounded-2xl p-6 hover:shadow-md transition-shadow">
                            <div class="flex justify-between items-start gap-4 mb-4">
                                <div>
                                    <h4 class="text-lg font-bold text-slate-800"><?= htmlspecialchars($daftar['nama_mahasiswa']); ?></h4>
                                    <p class="text-slate-400 text-sm font-medium">NPP: <?= htmlspecialchars($daftar['npp']); ?></p>
                                </div>
                                <span class="text-xs text-slate-400 font-semibold whitespace-nowrap"><?= date('d M Y', strtotime($daftar['tanggal_daftar'])); ?></span>
                            </div>
                            <div class="bg-blue-50/60 border-l-4 border-blue-500 p-4 rounded-r-xl mb-4">
                                <p class="text-sm font-bold text-blue-800">Webinar: <?= htmlspecialchars($daftar['judul']); ?></p>
                            </div>
                            <p class="text-[10px] text-slate-400 font-black uppercase tracking-wider mb-1">Esai Motivasi:</p>
                            <p class="text-sm text-slate-600 bg-slate-50 p-4 rounded-xl mb-6 italic whitespace-pre-line">
                                "<?= htmlspecialchars($daftar['motivasi']); ?>"
                            </p>
                            <div class="flex flex-wrap sm:flex-nowrap gap-3">
                                <button onclick="approveRegistration(<?= intval($daftar['id_pendaftaran']); ?>)" class="flex-1 py-3 bg-emerald-600 text-white rounded-xl font-bold flex items-center justify-center gap-2 hover:bg-emerald-700 shadow-lg shadow-emerald-900/10 transition-all text-sm">
                                    <i class="far fa-check-circle"></i> Setujui
                                </button>
                                <button onclick="rejectRegistration(<?= intval($daftar['id_pendaftaran']); ?>)" class="flex-1 py-3 bg-rose-600 text-white rounded-xl font-bold flex items-center justify-center gap-2 hover:bg-rose-700 shadow-lg shadow-rose-900/10 transition-all text-sm">
                                    <i class="far fa-times-circle"></i> Tolak
                                </button>
                                <a href="detail-webinar.php?id=<?= intval($daftar['id_webinar']); ?>" class="px-4 py-3 bg-slate-50 border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-100 transition-all flex items-center justify-center" title="Lihat Detail Webinar">
                                    <i class="far fa-eye"></i>
                                </a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="p-12 text-center">
                            <div class="w-16 h-16 bg-slate-50 border border-slate-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-check text-xl text-emerald-500"></i>
                            </div>
                            <p class="text-sm text-slate-400 font-medium italic">Semua pendaftaran telah diproses. Bersih!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="space-y-8">
            <div class="bg-white rounded-[32px] shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <h3 class="text-xl font-bold text-slate-800">Webinar Terbaru</h3>
                </div>
                <div class="p-6 space-y-4">
                    <?php if (mysqli_num_rows($result_webinars) > 0): ?>
                        <?php while($webinar = mysqli_fetch_assoc($result_webinars)): ?>
                        <div class="p-4 bg-white border border-slate-100 rounded-2xl hover:bg-slate-50/50 transition-all">
                            <div class="flex justify-between items-start gap-3 mb-2">
                                <h4 class="font-bold text-slate-800 text-sm line-clamp-2"><?= htmlspecialchars($webinar['judul']); ?></h4>
                                <span class="bg-teal-50 text-teal-700 border border-teal-200/50 text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-wider shrink-0">
                                    <?= htmlspecialchars($webinar['status']); ?>
                                </span>
                            </div>
                            <div class="space-y-1.5 mb-4">
                                <div class="flex items-center gap-2 text-[11px] text-slate-400 font-medium">
                                    <i class="far fa-calendar w-4"></i> <?= date('d M Y', strtotime($webinar['tanggal'])); ?>
                                </div>
                                <div class="flex items-center gap-2 text-[11px] text-slate-400 font-medium">
                                    <i class="fas fa-users w-4"></i> Kuota: <?= intval($webinar['kuota_peserta']); ?> Seats
                                </div>
                            </div>
                            <a href="detail-webinar.php?id=<?= intval($webinar['id_webinar']); ?>" class="text-blue-600 text-xs font-bold hover:text-blue-700 flex items-center justify-center border-t border-slate-50 pt-3 mt-2">Lihat Perkembangan Event →</a>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-xs text-slate-400 italic text-center py-6">Belum ada agenda webinar dibuat.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function approveRegistration(id) {
    Swal.fire({
        title: 'Setujui Pendaftaran?',
        text: "Mahasiswa akan mendapatkan akses penuh ke webinar ini.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0d9488', // Teal 600
        cancelButtonColor: '#64748b', // Slate 500
        confirmButtonText: 'Ya, Setujui!',
        cancelButtonText: 'Batal',
        borderRadius: '1.5rem',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'proses-aksi.php?action=approve_registration&id=' + id;
        }
    })
}

function rejectRegistration(id) {
    Swal.fire({
        title: 'Tolak Pendaftaran?',
        text: "Mahasiswa yang ditolak tidak dapat mengikuti webinar.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48', // Rose 600
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Tolak!',
        cancelButtonText: 'Batal',
        borderRadius: '1.5rem',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'proses-aksi.php?action=reject_registration&id=' + id;
        }
    })
}
</script>

<?php require_once 'includes/footer.php'; ?>