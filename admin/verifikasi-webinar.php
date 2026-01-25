<?php
require_once '../config/database.php';
require_once '../includes/header.php';

// Ambil statistik verifikasi
$query_stats = "SELECT 
    (SELECT COUNT(*) FROM webinar WHERE status_verifikasi = 'menunggu') as menunggu,
    (SELECT COUNT(*) FROM webinar WHERE status_verifikasi = 'disetujui') as disetujui,
    (SELECT COUNT(*) FROM webinar WHERE status_verifikasi = 'ditolak') as ditolak";

$result_stats = mysqli_query($conn, $query_stats);
$stats = mysqli_fetch_assoc($result_stats);

// Ambil webinar berdasarkan status
$status = isset($_GET['status']) ? $_GET['status'] : 'menunggu';
$query_webinars = "SELECT w.*, p.nama_penyelenggara 
                   FROM webinar w 
                   LEFT JOIN penyelenggara p ON w.id_penyelenggara = p.id_penyelenggara 
                   WHERE w.status_verifikasi = ? 
                   ORDER BY w.created_at DESC";
$stmt = mysqli_prepare($conn, $query_webinars);
mysqli_stmt_bind_param($stmt, 's', $status);
mysqli_stmt_execute($stmt);
$result_webinars = mysqli_stmt_get_result($stmt);
?>

<div class="min-h-screen bg-slate-50 p-4 md:p-8">
    <div class="max-w-6xl mx-auto">

        <div class="mb-8">
            <h2 class="text-3xl font-black text-slate-800 tracking-tight">Verifikasi Webinar</h2>
            <p class="text-slate-500 font-medium">Review dan tinjau kelayakan pengajuan webinar baru dari penyelenggara.</p>
        </div>

        <?php if (isset($_SESSION['success']) || isset($_SESSION['error'])): ?>
            <div class="mb-6 animate-bounce">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded-r-xl flex items-center gap-3">
                        <i class="fas fa-check-circle"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                <?php else: ?>
                    <div class="p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-700 rounded-r-xl flex items-center gap-3">
                        <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
            <a href="?status=menunggu" class="bg-white p-6 rounded-3xl border-2 <?= $status == 'menunggu' ? 'border-amber-500 shadow-lg shadow-amber-100' : 'border-transparent shadow-sm' ?> transition-all">
                <p class="text-xs font-black text-amber-600 uppercase mb-1">Menunggu</p>
                <h3 class="text-3xl font-black text-slate-800"><?= $stats['menunggu'] ?></h3>
            </a>
            <a href="?status=disetujui" class="bg-white p-6 rounded-3xl border-2 <?= $status == 'disetujui' ? 'border-emerald-500 shadow-lg shadow-emerald-100' : 'border-transparent shadow-sm' ?> transition-all">
                <p class="text-xs font-black text-emerald-600 uppercase mb-1">Disetujui</p>
                <h3 class="text-3xl font-black text-slate-800"><?= $stats['disetujui'] ?></h3>
            </a>
            <a href="?status=ditolak" class="bg-white p-6 rounded-3xl border-2 <?= $status == 'ditolak' ? 'border-rose-500 shadow-lg shadow-rose-100' : 'border-transparent shadow-sm' ?> transition-all">
                <p class="text-xs font-black text-rose-600 uppercase mb-1">Ditolak</p>
                <h3 class="text-3xl font-black text-slate-800"><?= $stats['ditolak'] ?></h3>
            </a>
        </div>

        <div class="relative mb-8">
            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" 
                   class="w-full pl-12 pr-4 py-4 bg-white border-none rounded-2xl shadow-sm focus:ring-2 focus:ring-indigo-500 font-medium text-slate-600" 
                   placeholder="Cari judul webinar atau nama pembicara..."
                   onkeyup="searchWebinar(this.value)">
        </div>

        <div id="webinar-list" class="space-y-6">
            <?php if(mysqli_num_rows($result_webinars) > 0): ?>
                <?php while($webinar = mysqli_fetch_assoc($result_webinars)): ?>
                <div class="webinar-item-card bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-100 hover:shadow-xl transition-all group">
                    <div class="flex flex-col lg:flex-row gap-8">
                        
                        <div class="flex-1">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-[10px] font-black uppercase tracking-widest leading-none">
                                        <?= htmlspecialchars($webinar['kategori']) ?>
                                    </span>
                                    <h4 class="text-2xl font-black text-slate-800 mt-2 leading-tight"><?= htmlspecialchars($webinar['judul']) ?></h4>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Pembicara</p>
                                    <p class="text-sm font-bold text-slate-700"><?= htmlspecialchars($webinar['pembicara']) ?></p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Penyelenggara</p>
                                    <p class="text-sm font-bold text-slate-700"><?= htmlspecialchars($webinar['nama_penyelenggara'] ?? 'Umum') ?></p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Jadwal</p>
                                    <p class="text-sm font-bold text-slate-700"><?= date('d M Y', strtotime($webinar['tanggal'])) ?></p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">SKKM & Kuota</p>
                                    <p class="text-sm font-bold text-slate-700"><?= $webinar['poin_skkm'] ?> Poin / <?= $webinar['kuota_peserta'] ?> Peserta</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex lg:flex-col gap-3 justify-center lg:border-l lg:border-slate-100 lg:pl-8">
                            <?php if($status == 'menunggu'): ?>
                                <form method="POST" action="proses-aksi.php" class="contents">
                                    <input type="hidden" name="id" value="<?= $webinar['id_webinar'] ?>">
                                    <input type="hidden" name="action" value="approve_webinar">
                                    <button type="submit" onclick="return confirm('Setujui penayangan webinar ini?')" 
                                            class="flex-1 lg:w-40 py-4 bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl font-bold transition-all shadow-lg shadow-emerald-200 flex items-center justify-center gap-2">
                                        <i class="fas fa-check"></i> Setujui
                                    </button>
                                </form>
                                
                                <form method="POST" action="proses-aksi.php" class="contents">
                                    <input type="hidden" name="id" value="<?= $webinar['id_webinar'] ?>">
                                    <input type="hidden" name="action" value="reject_webinar">
                                    <button type="submit" onclick="return confirm('Tolak pengajuan ini?')"
                                            class="flex-1 lg:w-40 py-4 bg-white border-2 border-slate-100 text-rose-500 hover:bg-rose-50 rounded-2xl font-bold transition-all flex items-center justify-center gap-2">
                                        <i class="fas fa-times"></i> Tolak
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <button onclick="viewWebinar(<?= $webinar['id_webinar'] ?>)" 
                                    class="flex-1 lg:w-40 py-4 bg-slate-800 hover:bg-black text-white rounded-2xl font-bold transition-all flex items-center justify-center gap-2">
                                <i class="fas fa-eye"></i> Detail
                            </button>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="bg-white rounded-[3rem] p-20 text-center border-4 border-dashed border-slate-100">
                    <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300 text-4xl">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Tidak ada data</h3>
                    <p class="text-slate-500">Saat ini tidak ada pengajuan webinar dengan status <span class="font-bold text-indigo-600 underline"><?= $status ?></span></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function searchWebinar(keyword) {
    const cards = document.querySelectorAll('.webinar-item-card');
    cards.forEach(card => {
        const text = card.textContent.toLowerCase();
        card.style.display = text.includes(keyword.toLowerCase()) ? 'block' : 'none';
    });
}

function viewWebinar(id) {
    window.location.href = 'detail-webinar.php?id=' + id;
}
</script>

<?php require_once '../includes/footer.php'; ?>