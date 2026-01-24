<?php
require_once '../config/database.php';
require_once '../includes/header.php';

// Ambil statistik dari database
$query_stats = "SELECT 
    (SELECT COUNT(*) FROM webinar) as total_webinar,
    (SELECT COUNT(*) FROM pemantauan_webinar WHERE status_pendaftaran = 'menunggu') as pending,
    (SELECT COUNT(*) FROM pemantauan_webinar WHERE status_pendaftaran = 'disetujui') as total_peserta,
    (SELECT COUNT(*) FROM webinar WHERE status = 'publish') as webinar_aktif";

$result_stats = mysqli_query($conn, $query_stats);
$stats = mysqli_fetch_assoc($result_stats);

// Ambil pendaftaran menunggu
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

<div class="p-8 bg-slate-50 min-h-screen">
    <div class="flex justify-end items-center mb-6">
        <div class="flex items-center gap-3 bg-white p-2 pr-4 rounded-2xl shadow-sm border border-slate-100">
            <img src="https://ui-avatars.com/api/?name=Bayu+Anggara&background=0D9488&color=fff" class="w-10 h-10 rounded-xl" alt="Profile">
            <div class="text-left">
                <p class="text-sm font-bold text-slate-800 leading-none">Bayu Anggara</p>
                <p class="text-[11px] text-slate-400 font-medium">Administrator</p>
            </div>
        </div>
    </div>

    <div class="bg-teal-600 rounded-[32px] p-10 text-white mb-10 shadow-lg shadow-teal-900/10 relative overflow-hidden">
        <div class="relative z-10">
            <h2 class="text-4xl font-bold mb-2">Admin Dashboard</h2>
            <p class="text-teal-50 text-lg opacity-90">Kelola webinar dan verifikasi pendaftaran mahasiswa</p>
        </div>
        <div class="absolute -right-10 -top-10 w-64 h-64 bg-white/10 rounded-full"></div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex justify-between items-start">
            <div>
                <p class="text-slate-400 text-sm font-medium">Total Webinar</p>
                <h3 class="text-4xl font-bold text-slate-800 mt-1"><?= number_format($stats['total_webinar']); ?></h3>
            </div>
            <div class="p-3 bg-blue-50 rounded-2xl text-blue-600">
                <i class="far fa-calendar-alt text-xl"></i>
            </div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex justify-between items-start">
            <div>
                <p class="text-slate-400 text-sm font-medium">Pendaftaran Pending</p>
                <h3 class="text-4xl font-bold text-slate-800 mt-1"><?= number_format($stats['pending']); ?></h3>
            </div>
            <div class="p-3 bg-amber-50 rounded-2xl text-amber-600">
                <i class="far fa-clock text-xl"></i>
            </div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex justify-between items-start">
            <div>
                <p class="text-slate-400 text-sm font-medium">Total Peserta</p>
                <h3 class="text-4xl font-bold text-slate-800 mt-1"><?= number_format($stats['total_peserta']); ?></h3>
            </div>
            <div class="p-3 bg-emerald-50 rounded-2xl text-emerald-600">
                <i class="fas fa-users text-xl"></i>
            </div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex justify-between items-start">
            <div>
                <p class="text-slate-400 text-sm font-medium">Webinar Aktif</p>
                <h3 class="text-4xl font-bold text-slate-800 mt-1"><?= number_format($stats['webinar_aktif']); ?></h3>
            </div>
            <div class="p-3 bg-purple-50 rounded-2xl text-purple-600">
                <i class="fas fa-chart-line text-xl"></i>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-[32px] shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-8 border-b border-slate-50 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-slate-800">Pendaftaran Menunggu Persetujuan</h3>
                    <span class="bg-amber-100 text-amber-700 px-4 py-1 rounded-full text-xs font-bold"><?= $stats['pending']; ?> Pending</span>
                </div>
                <div class="p-8 space-y-6">
                    <?php while($daftar = mysqli_fetch_assoc($result_pendaftaran)): ?>
                    <div class="bg-white border border-slate-100 rounded-2xl p-6 transition-hover hover:shadow-md">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h4 class="text-lg font-bold text-slate-800"><?= $daftar['nama_mahasiswa']; ?></h4>
                                <p class="text-slate-400 text-sm italic">NIM: <?= $daftar['npp']; ?></p>
                            </div>
                            <span class="text-xs text-slate-400 font-medium"><?= date('d M Y', strtotime($daftar['tanggal_daftar'])); ?></span>
                        </div>
                        <div class="bg-blue-50/50 border-l-4 border-blue-500 p-4 rounded-r-xl mb-4">
                            <p class="text-sm text-slate-600 font-medium text-blue-700">Webinar: <?= $daftar['judul']; ?></p>
                        </div>
                        <p class="text-xs text-slate-400 font-semibold uppercase mb-2">Motivasi:</p>
                        <p class="text-sm text-slate-600 bg-slate-50 p-4 rounded-xl mb-6">
                            "<?= $daftar['motivasi']; ?>"
                        </p>
                        <div class="flex gap-3">
                            <button onclick="approve(<?= $daftar['id_pendaftaran']; ?>)" class="flex-1 py-3 bg-emerald-600 text-white rounded-xl font-bold flex items-center justify-center gap-2 hover:bg-emerald-700 shadow-lg shadow-emerald-900/10 transition-all">
                                <i class="far fa-check-circle"></i> Setujui
                            </button>
                            <button onclick="reject(<?= $daftar['id_pendaftaran']; ?>)" class="flex-1 py-3 bg-red-600 text-white rounded-xl font-bold flex items-center justify-center gap-2 hover:bg-red-700 shadow-lg shadow-red-900/10 transition-all">
                                <i class="far fa-times-circle"></i> Tolak
                            </button>
                            <button class="px-4 bg-slate-100 text-slate-500 rounded-xl hover:bg-slate-200 transition-all">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <div class="space-y-8">
            <div class="bg-white rounded-[32px] shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-6 border-b border-slate-50">
                    <h3 class="text-xl font-bold text-slate-800">Webinar Terbaru</h3>
                </div>
                <div class="p-6 space-y-4">
                    <?php while($webinar = mysqli_fetch_assoc($result_webinars)): ?>
                    <div class="p-4 bg-white border border-slate-50 rounded-2xl hover:bg-slate-50/50 transition-all">
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="font-bold text-slate-800 text-sm pr-2"><?= $webinar['judul']; ?></h4>
                            <span class="bg-emerald-100 text-emerald-700 text-[10px] font-black px-2 py-0.5 rounded uppercase">Upcoming</span>
                        </div>
                        <div class="space-y-1.5 mb-4">
                            <div class="flex items-center gap-2 text-[11px] text-slate-400">
                                <i class="far fa-calendar w-4"></i> <?= date('d M Y', strtotime($webinar['tanggal'])); ?>
                            </div>
                            <div class="flex items-center gap-2 text-[11px] text-slate-400">
                                <i class="fas fa-users w-4"></i> 150/<?= $webinar['kuota_peserta']; ?> peserta
                            </div>
                        </div>
                        <div class="bg-amber-50 text-amber-700 text-[10px] font-bold p-2 rounded-lg flex items-center gap-2 mb-3">
                            <i class="far fa-clock"></i> 8 pendaftaran menunggu
                        </div>
                        <a href="#" class="text-blue-600 text-xs font-bold hover:underline flex items-center justify-center">Lihat Detail →</a>
                    </div>
                    <div class="h-px bg-slate-50 mx-2"></div>
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="bg-white rounded-[32px] shadow-sm border border-slate-100 overflow-hidden p-8">
                <h3 class="text-xl font-bold text-slate-800 mb-6 tracking-tight">Quick Actions</h3>
                <div class="space-y-4">
                    <a href="tambah-webinar.php" class="flex items-center gap-4 p-4 bg-blue-50/50 rounded-2xl group hover:bg-blue-600 transition-all">
                        <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center text-blue-600 group-hover:bg-blue-500 group-hover:text-white transition-all">
                            <i class="far fa-calendar-plus text-xl"></i>
                        </div>
                        <span class="font-bold text-blue-600 group-hover:text-white transition-all">Buat Webinar Baru</span>
                    </a>
                    <button class="w-full flex items-center gap-4 p-4 bg-emerald-50/50 rounded-2xl group hover:bg-emerald-600 transition-all text-left">
                        <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center text-emerald-600 group-hover:bg-emerald-500 group-hover:text-white transition-all">
                            <i class="far fa-file-alt text-xl"></i>
                        </div>
                        <span class="font-bold text-emerald-600 group-hover:text-white transition-all">Export Laporan</span>
                    </button>
                    <a href="verifikasi-pendaftaran.php" class="flex items-center gap-4 p-4 bg-purple-50/50 rounded-2xl group hover:bg-purple-600 transition-all">
                        <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center text-purple-600 group-hover:bg-purple-500 group-hover:text-white transition-all">
                            <i class="fas fa-users-cog text-xl"></i>
                        </div>
                        <span class="font-bold text-purple-600 group-hover:text-white transition-all">Kelola Peserta</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function approve(id) {
    if(confirm('Terima pendaftaran mahasiswa ini?')) {
        window.location.href = 'proses-aksi.php?action=approve_registration&id=' + id;
    }
}
function reject(id) {
    if(confirm('Tolak pendaftaran mahasiswa ini?')) {
        window.location.href = 'proses-aksi.php?action=reject_registration&id=' + id;
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>