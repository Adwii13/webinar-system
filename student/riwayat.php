<?php
require_once '../config/database.php';
require_once 'includes/header.php';

// Ambil filter status dari URL jika ada
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

$query_str = "SELECT p.*, w.judul, w.tanggal, w.pembicara, w.kategori, w.poin_skkm, w.platform
              FROM pemantauan_webinar p 
              JOIN webinar w ON p.id_webinar = w.id_webinar";

if ($status_filter) {
    $query_str .= " WHERE p.status_pendaftaran = '$status_filter'";
}

$query_str .= " ORDER BY p.tanggal_daftar DESC";
$result = mysqli_query($conn, $query_str);

// Statistik untuk Card Atas
$stats_query = mysqli_query($conn, "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status_pendaftaran = 'disetujui' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status_pendaftaran = 'menunggu' THEN 1 ELSE 0 END) as waiting
    FROM pemantauan_webinar");
$stats = mysqli_fetch_assoc($stats_query);

$poin_query = mysqli_query($conn, "SELECT SUM(w.poin_skkm) as total_poin 
               FROM pemantauan_webinar p 
               JOIN webinar w ON p.id_webinar = w.id_webinar 
               WHERE p.status_pendaftaran = 'disetujui'");
$total_poin = mysqli_fetch_assoc($poin_query)['total_poin'] ?? 0;
?>

<div class="p-4 md:p-8 bg-slate-50 min-h-screen">
    <div class="max-w-6xl mx-auto">
        
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <h2 class="text-4xl font-black text-slate-800 tracking-tight italic uppercase">Riwayat Webinar</h2>
                <p class="text-slate-500 font-medium mt-2">Pantau status pendaftaran dan perolehan poin SKKM Anda.</p>
            </div>
            
            <div class="flex gap-4 overflow-x-auto pb-2 md:pb-0">
                <div class="bg-white px-6 py-3 rounded-2xl border border-slate-200 shadow-sm shrink-0">
                    <p class="text-[10px] font-black text-slate-400 uppercase">Total Poin</p>
                    <p class="text-xl font-black text-teal-600"><?= $total_poin ?> <span class="text-xs">SKKM</span></p>
                </div>
                <div class="bg-white px-6 py-3 rounded-2xl border border-slate-200 shadow-sm shrink-0">
                    <p class="text-[10px] font-black text-slate-400 uppercase">Webinar Diikuti</p>
                    <p class="text-xl font-black text-slate-800"><?= $stats['approved'] ?></p>
                </div>
            </div>
        </div>

        <div class="flex gap-2 mb-8 overflow-x-auto pb-2">
            <a href="riwayat.php" class="px-6 py-2 rounded-full font-bold text-sm transition-all <?= !$status_filter ? 'bg-slate-900 text-white shadow-lg' : 'bg-white text-slate-500 border border-slate-200 hover:border-slate-300' ?>">Semua</a>
            <a href="riwayat.php?status=disetujui" class="px-6 py-2 rounded-full font-bold text-sm transition-all <?= $status_filter == 'disetujui' ? 'bg-teal-600 text-white shadow-lg' : 'bg-white text-slate-500 border border-slate-200 hover:border-teal-200' ?>">Disetujui</a>
            <a href="riwayat.php?status=menunggu" class="px-6 py-2 rounded-full font-bold text-sm transition-all <?= $status_filter == 'menunggu' ? 'bg-amber-500 text-white shadow-lg' : 'bg-white text-slate-500 border border-slate-200 hover:border-amber-200' ?>">Menunggu</a>
            <a href="riwayat.php?status=ditolak" class="px-6 py-2 rounded-full font-bold text-sm transition-all <?= $status_filter == 'ditolak' ? 'bg-rose-500 text-white shadow-lg' : 'bg-white text-slate-500 border border-slate-200 hover:border-rose-200' ?>">Ditolak</a>
        </div>

        <div class="grid gap-4">
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($p = mysqli_fetch_assoc($result)): 
                    // Logika Warna Status
                    $color = 'slate';
                    $icon = 'clock';
                    if($p['status_pendaftaran'] == 'disetujui') { $color = 'teal'; $icon = 'check-circle'; }
                    elseif($p['status_pendaftaran'] == 'ditolak') { $color = 'rose'; $icon = 'times-circle'; }
                    elseif($p['status_pendaftaran'] == 'menunggu') { $color = 'amber'; $icon = 'hourglass-half'; }
                ?>
                <div class="bg-white p-5 md:p-7 rounded-[2rem] border border-slate-200 hover:border-<?= $color ?>-300 transition-all shadow-sm group">
                    <div class="flex flex-col md:flex-row justify-between gap-6">
                        <div class="flex gap-6">
                            <div class="hidden md:flex flex-col items-center justify-center w-16 bg-<?= $color ?>-50 rounded-2xl border border-<?= $color ?>-100 text-<?= $color ?>-600">
                                <i class="fas fa-<?= $icon ?> text-xl"></i>
                                <span class="text-[10px] font-black uppercase mt-1 tracking-tighter"><?= $p['poin_skkm'] ?> pts</span>
                            </div>

                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="px-3 py-0.5 bg-<?= $color ?>-50 text-<?= $color ?>-600 text-[10px] font-black uppercase rounded-full border border-<?= $color ?>-100">
                                        <?= $p['status_pendaftaran'] ?>
                                    </span>
                                    <span class="text-xs text-slate-400 font-medium italic">Daftar: <?= date('d M Y', strtotime($p['tanggal_daftar'])) ?></span>
                                </div>
                                <h4 class="text-xl font-black text-slate-800 leading-tight group-hover:text-<?= $color ?>-600 transition-colors">
                                    <?= htmlspecialchars($p['judul']) ?>
                                </h4>
                                <div class="flex flex-wrap gap-x-5 gap-y-1 mt-3 text-sm text-slate-500 font-bold">
                                    <span class="flex items-center gap-2"><i class="fas fa-user-tie text-teal-500"></i> <?= htmlspecialchars($p['pembicara']) ?></span>
                                    <span class="flex items-center gap-2"><i class="fas fa-calendar text-teal-500"></i> <?= date('d F Y', strtotime($p['tanggal'])) ?></span>
                                    <span class="flex items-center gap-2"><i class="fas fa-laptop text-teal-500"></i> <?= htmlspecialchars($p['platform']) ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center md:justify-end gap-3 border-t md:border-t-0 pt-4 md:pt-0">
                            <a href="detail-webinar.php?id=<?= $p['id_webinar'] ?>" 
                               class="flex-1 md:flex-none px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-black text-xs uppercase tracking-widest transition-all text-center">
                                Detail
                            </a>
                            <?php if($p['status_pendaftaran'] == 'disetujui'): ?>
                            <button class="flex-1 md:flex-none px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-black text-xs uppercase tracking-widest shadow-lg shadow-teal-200 transition-all text-center">
                                <i class="fas fa-ticket-alt mr-2"></i> Tiket
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="bg-white rounded-[3rem] p-20 text-center border-2 border-dashed border-slate-200">
                    <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-200 text-4xl">
                        <i class="fas fa-history"></i>
                    </div>
                    <h3 class="text-2xl font-black text-slate-800 uppercase italic">Belum Ada Riwayat</h3>
                    <p class="text-slate-500 mt-2 max-w-xs mx-auto font-medium">Anda belum mendaftar ke webinar manapun saat ini.</p>
                    <a href="daftar-webinar.php" class="inline-block mt-8 px-8 py-4 bg-slate-900 text-white rounded-2xl font-black uppercase tracking-widest hover:bg-teal-600 transition-all shadow-xl">
                        Cari Webinar Sekarang
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>