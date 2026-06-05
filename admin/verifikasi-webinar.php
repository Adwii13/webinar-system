<?php
require_once 'includes/admin-guard.php';
require_once '../config/database.php';
require_once 'includes/header.php';

// Ambil statistik verifikasi secara realtime
$query_stats = "SELECT 
    (SELECT COUNT(*) FROM webinar WHERE status_verifikasi = 'menunggu') as menunggu,
    (SELECT COUNT(*) FROM webinar WHERE status_verifikasi = 'disetujui') as disetujui,
    (SELECT COUNT(*) FROM webinar WHERE status_verifikasi = 'ditolak') as ditolak";

$result_stats = mysqli_query($conn, $query_stats);
$stats = mysqli_fetch_assoc($result_stats);

// Ambil data webinar berdasarkan filter tab status aktif
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

        <!-- HEADER HALAMAN -->
        <div class="mb-8">
            <h2 class="text-3xl font-black text-slate-800 tracking-tight">Verifikasi Webinar</h2>
            <p class="text-slate-500 font-medium text-sm">Review dan tinjau kelayakan pengajuan agenda webinar baru dari pihak penyelenggara.</p>
        </div>

        <!-- STATISTIK BADGE FILTER CARDS (TEMA AMBER / TEAL / ROSE) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <a href="?status=menunggu" class="bg-white p-6 rounded-3xl border-2 <?= $status == 'menunggu' ? 'border-amber-500 shadow-xl shadow-amber-100/50' : 'border-transparent shadow-sm' ?> hover:shadow-md transition-all group">
                <p class="text-[10px] font-black text-amber-600 uppercase tracking-wider mb-1">Menunggu Verifikasi</p>
                <h3 class="text-3xl font-black text-slate-800"><?= $stats['menunggu'] ?></h3>
            </a>
            <a href="?status=disetujui" class="bg-white p-6 rounded-3xl border-2 <?= $status == 'disetujui' ? 'border-teal-500 shadow-xl shadow-teal-100/50' : 'border-transparent shadow-sm' ?> hover:shadow-md transition-all group">
                <p class="text-[10px] font-black text-teal-600 uppercase tracking-wider mb-1">Telah Disetujui</p>
                <h3 class="text-3xl font-black text-slate-800"><?= $stats['disetujui'] ?></h3>
            </a>
            <a href="?status=ditolak" class="bg-white p-6 rounded-3xl border-2 <?= $status == 'ditolak' ? 'border-rose-500 shadow-xl shadow-rose-100/50' : 'border-transparent shadow-sm' ?> hover:shadow-md transition-all group">
                <p class="text-[10px] font-black text-rose-600 uppercase tracking-wider mb-1">Ditolak / Draft</p>
                <h3 class="text-3xl font-black text-slate-800"><?= $stats['ditolak'] ?></h3>
            </a>
        </div>

        <!-- FORM INPUT PENCARIAN LIVE CLIENT-SIDE -->
        <div class="relative mb-8">
            <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-slate-400">
                <i class="fas fa-search text-sm"></i>
            </span>
            <input type="text" 
                   class="w-full pl-13 pr-5 py-4 bg-white border-none rounded-2xl shadow-sm focus:ring-2 focus:ring-teal-500 font-medium text-slate-700 placeholder-slate-400 transition-all text-sm focus:outline-none" 
                   placeholder="Cari judul webinar atau nama pembicara yang diajukan..."
                   onkeyup="searchWebinar(this.value)">
        </div>

        <!-- DAFTAR ITEM WEBINAR -->
        <div id="webinar-list" class="space-y-6">
            <?php if(mysqli_num_rows($result_webinars) > 0): ?>
                <?php while($webinar = mysqli_fetch_assoc($result_webinars)): ?>
                <div class="webinar-item-card bg-white rounded-[2.5rem] p-6 md:p-8 shadow-sm border border-slate-100 hover:shadow-xl shadow-slate-200/40 transition-all group">
                    <div class="flex flex-col lg:flex-row gap-6 lg:gap-8">
                        
                        <div class="flex-1">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <span class="px-3 py-1 bg-teal-50 text-teal-600 rounded-lg text-[10px] font-black uppercase tracking-widest leading-none">
                                        <?= htmlspecialchars($webinar['kategori']) ?>
                                    </span>
                                    <h4 class="text-xl md:text-2xl font-black text-slate-800 mt-2 leading-tight"><?= htmlspecialchars($webinar['judul']) ?></h4>
                                </div>
                            </div>

                            <!-- Detail metadata grid informasi webinar -->
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 text-xs md:text-sm">
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-0.5">Pembicara</p>
                                    <p class="font-bold text-slate-700 truncate"><?= htmlspecialchars($webinar['pembicara']) ?></p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-0.5">Penyelenggara</p>
                                    <p class="font-bold text-slate-700 truncate"><?= htmlspecialchars($webinar['nama_penyelenggara'] ?? 'Umum / Universitas') ?></p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-0.5">Jadwal Acara</p>
                                    <p class="font-bold text-slate-700"><?= date('d M Y', strtotime($webinar['tanggal'])) ?></p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-0.5">Atribut Regulasi</p>
                                    <p class="font-bold text-slate-600"><?= $webinar['poin_skkm'] ?> Poin SKKM / <?= $webinar['kuota_peserta'] ?> Sisa Slot</p>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL ACTION BUTTONS (DINAMIS SISI KANAN) -->
                        <div class="flex flex-row lg:flex-col gap-3 justify-center items-center lg:border-l lg:border-slate-100 lg:pl-8 pt-4 lg:pt-0 border-t lg:border-t-0 border-slate-100">
                            <?php if($status == 'menunggu'): ?>
                                <form id="form-approve-<?= $webinar['id_webinar'] ?>" method="POST" action="proses-aksi.php" class="contents">
                                    <input type="hidden" name="id" value="<?= $webinar['id_webinar'] ?>">
                                    <input type="hidden" name="action" value="approve_webinar">
                                    <button type="button" onclick="confirmAction('approve', <?= $webinar['id_webinar'] ?>)" 
                                            class="w-full py-3 px-4 bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl font-bold text-sm transition-all shadow-md shadow-emerald-200 flex items-center justify-center gap-2">
                                        <i class="fas fa-check"></i> Setujui
                                    </button>
                                </form>

                                <form id="form-reject-<?= $webinar['id_webinar'] ?>" method="POST" action="proses-aksi.php" class="contents">
                                    <input type="hidden" name="id" value="<?= $webinar['id_webinar'] ?>">
                                    <input type="hidden" name="action" value="reject_webinar">
                                    <button type="button" onclick="confirmAction('reject', <?= $webinar['id_webinar'] ?>)"
                                            class="w-full py-3 px-4 bg-white border-2 border-slate-200 text-rose-500 hover:bg-rose-50 rounded-2xl font-bold text-sm transition-all flex items-center justify-center gap-2">
                                        <i class="fas fa-times"></i> Tolak
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <button onclick="viewWebinar(<?= $webinar['id_webinar'] ?>)" 
                                    class="w-full py-3 px-4 bg-slate-800 hover:bg-black text-white rounded-2xl font-bold text-sm transition-all flex items-center justify-center gap-2">
                                <i class="fas fa-eye"></i> Detail
                            </button>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <!-- KONDISI KOSONG (DATA EMPTI STATE CARD) -->
                <div class="bg-white rounded-[3rem] p-16 text-center border-4 border-dashed border-slate-200/60 max-w-xl mx-auto mt-10">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300 text-3xl">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <h3 class="text-xl font-black text-slate-800 uppercase tracking-tight">Tidak ada data</h3>
                    <p class="text-sm text-slate-400 mt-1">Saat ini tidak ada pengajuan agenda webinar dengan klasifikasi status <span class="font-bold text-teal-600 underline uppercase"><?= htmlspecialchars($status) ?></span></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Fungsi live filter pencarian baris kartu data
function searchWebinar(keyword) {
    const cards = document.querySelectorAll('.webinar-item-card');
    cards.forEach(card => {
        const text = card.textContent.toLowerCase();
        card.style.display = text.includes(keyword.toLowerCase()) ? 'block' : 'none';
    });
}

// Navigasi masuk ke halaman detail berkas
function viewWebinar(id) {
    window.location.href = 'detail-webinar.php?id=' + id;
}

// SweetAlert2 Modal Interaktif Verifikasi Aksi Admin
function confirmAction(type, id) {
    const isApprove = type === 'approve';
    
    Swal.fire({
        title: isApprove ? 'Setujui Penerbitan Webinar?' : 'Tolak Pengajuan Webinar?',
        text: isApprove ? 'Agenda webinar akan segera dipublikasikan secara global dan dapat didaftar oleh mahasiswa.' : 'Pengajuan materi webinar ini akan ditolak secara resmi dan dikembalikan ke draft milik penyelenggara.',
        icon: isApprove ? 'success' : 'warning',
        showCancelButton: true,
        confirmButtonColor: isApprove ? '#0d9488' : '#e11d48', // Menggunakan warna tema Teal dan Rose dasar hex
        cancelButtonColor: '#64748b',
        confirmButtonText: isApprove ? 'Ya, Publikasikan!' : 'Ya, Tolak!',
        cancelButtonText: 'Batal',
        borderRadius: '1.5rem',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            const formId = isApprove ? 'form-approve-' + id : 'form-reject-' + id;
            document.getElementById(formId).submit();
        }
    });
}
</script>

<!-- SweetAlert2 Toast Pemicu Session Notification Interseptor dari file proses-aksi.php -->
<?php if(isset($_SESSION['success'])): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?= htmlspecialchars($_SESSION['success']) ?>',
            timer: 2500,
            showConfirmButton: false,
            borderRadius: '1.5rem'
        });
    });
</script>
<?php unset($_SESSION['success']); endif; ?>

<?php if(isset($_SESSION['error'])): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '<?= htmlspecialchars($_SESSION['error']) ?>',
            timer: 2500,
            showConfirmButton: false,
            borderRadius: '1.5rem'
        });
    });
</script>
<?php unset($_SESSION['error']); endif; ?>

<?php require_once 'includes/footer.php'; ?>