<?php
require_once '../config/database.php';
require_once '../includes/header.php';

// 1. Eksekusi query dengan JOIN (Mendapatkan data dari 3 tabel sekaligus)
$query = "SELECT p.*, w.judul, w.tanggal, 
                 m.nama_mahasiswa, m.fakultas, m.jurusan 
          FROM pemantauan_webinar p 
          JOIN webinar w ON p.id_webinar = w.id_webinar 
          JOIN mahasiswa m ON p.npp = m.npp 
          WHERE p.status_pendaftaran = 'menunggu' 
          ORDER BY p.tanggal_daftar DESC";
$result = mysqli_query($conn, $query);

// 2. Ambil statistik secara dinamis
$count_waiting = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pemantauan_webinar WHERE status_pendaftaran = 'menunggu'"))['total'];
$count_approved = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pemantauan_webinar WHERE status_pendaftaran = 'disetujui'"))['total'];
$count_rejected = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pemantauan_webinar WHERE status_pendaftaran = 'ditolak'"))['total'];
?>

<div class="p-4 md:p-8 bg-slate-50 min-h-screen">
    <div class="max-w-6xl mx-auto">
        
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-black text-slate-800 tracking-tight italic uppercase">Verifikasi Pendaftaran</h2>
                <p class="text-slate-500 font-medium">Tinjau dan setujui partisipasi mahasiswa dalam webinar.</p>
            </div>
            <div class="flex gap-2">
                <span class="px-4 py-2 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-600 shadow-sm">
                    Total Pengajuan: <?= number_format($count_waiting + $count_approved + $count_rejected) ?>
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="bg-white p-6 rounded-[24px] border border-orange-100 shadow-sm relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-orange-50 rounded-full group-hover:scale-110 transition-transform"></div>
                <p class="text-orange-600 font-black uppercase tracking-wider text-xs mb-1 relative">Menunggu</p>
                <h3 class="text-4xl font-black text-slate-800 relative"><?= number_format($count_waiting) ?></h3>
                <p class="text-slate-400 text-sm mt-2">Perlu tindakan segera</p>
            </div>

            <div class="bg-white p-6 rounded-[24px] border border-teal-100 shadow-sm relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-teal-50 rounded-full group-hover:scale-110 transition-transform"></div>
                <p class="text-teal-600 font-black uppercase tracking-wider text-xs mb-1 relative">Disetujui</p>
                <h3 class="text-4xl font-black text-slate-800 relative"><?= number_format($count_approved) ?></h3>
                <p class="text-slate-400 text-sm mt-2">Telah mendapatkan akses</p>
            </div>

            <div class="bg-white p-6 rounded-[24px] border border-red-100 shadow-sm relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-red-50 rounded-full group-hover:scale-110 transition-transform"></div>
                <p class="text-red-600 font-black uppercase tracking-wider text-xs mb-1 relative">Ditolak</p>
                <h3 class="text-4xl font-black text-slate-800 relative"><?= number_format($count_rejected) ?></h3>
                <p class="text-slate-400 text-sm mt-2">Tidak memenuhi syarat</p>
            </div>
        </div>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="mb-6 p-4 bg-teal-50 border-l-4 border-teal-500 text-teal-700 rounded-r-xl flex items-center gap-3 animate-pulse">
                <i class="fas fa-check-circle"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <div class="space-y-4">
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($pendaftaran = mysqli_fetch_assoc($result)): ?>
                <div class="bg-white p-6 rounded-[32px] shadow-sm border border-slate-100 hover:border-teal-500/30 transition-all group">
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
                        <div class="flex gap-5">
                            <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-400 text-xl shrink-0 group-hover:bg-teal-600 group-hover:text-white transition-all">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            
                            <div>
                                <h4 class="text-xl font-bold text-slate-800"><?= htmlspecialchars($pendaftaran['nama_mahasiswa']) ?></h4>
                                <div class="flex flex-wrap gap-x-4 gap-y-1 mt-1 text-sm text-slate-500 font-medium">
                                    <span><i class="fas fa-id-badge mr-1 text-teal-500"></i> <?= htmlspecialchars($pendaftaran['npp']) ?></span>
                                    <span><i class="fas fa-university mr-1 text-teal-500"></i> <?= htmlspecialchars($pendaftaran['fakultas']) ?></span>
                                    <span><i class="fas fa-calendar-alt mr-1 text-teal-500"></i> <?= date('d/m/Y', strtotime($pendaftaran['tanggal_daftar'])) ?></span>
                                </div>
                                
                                <div class="mt-4 p-4 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">Webinar Target</p>
                                    <p class="text-slate-700 font-bold"><?= htmlspecialchars($pendaftaran['judul']) ?></p>
                                    <p class="text-xs text-slate-500 mt-2 italic font-medium leading-relaxed">
                                        <i class="fas fa-quote-left mr-1 text-slate-300"></i> 
                                        <?= htmlspecialchars($pendaftaran['motivasi']) ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-row md:flex-col gap-2 shrink-0">
                            <button onclick="approveRegistration(<?= $pendaftaran['id_pendaftaran'] ?>)" 
                                    class="flex-1 px-6 py-3 bg-teal-600 text-white rounded-xl font-bold text-sm hover:bg-teal-700 transition-all flex items-center justify-center gap-2 shadow-lg shadow-teal-600/20">
                                <i class="fas fa-check"></i> Setujui
                            </button>
                            <button onclick="rejectRegistration(<?= $pendaftaran['id_pendaftaran'] ?>)" 
                                    class="flex-1 px-6 py-3 bg-white border-2 border-red-50 text-red-600 rounded-xl font-bold text-sm hover:bg-red-50 transition-all flex items-center justify-center gap-2">
                                <i class="fas fa-times"></i> Tolak
                            </button>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="bg-white rounded-[32px] p-12 text-center border-2 border-dashed border-slate-200">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300 text-3xl">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800">Tidak ada pengajuan baru</h3>
                    <p class="text-slate-500">Semua pendaftaran telah diproses atau belum ada pendaftar.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function approveRegistration(id) {
    if (confirm('Konfirmasi Setujui: Berikan akses webinar kepada mahasiswa ini?')) {
        window.location.href = 'proses-aksi.php?action=approve_registration&id=' + id;
    }
}

function rejectRegistration(id) {
    if (confirm('Konfirmasi Tolak: Mahasiswa ini tidak akan bisa mengikuti webinar?')) {
        window.location.href = 'proses-aksi.php?action=reject_registration&id=' + id;
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>