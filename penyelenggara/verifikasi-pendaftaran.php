<?php

require_once 'includes/guard-penyelenggara.php';
require_once '../config/database.php';
require_once 'includes/header.php';

$id_penyelenggara = $_SESSION['id_user'];

// 1. Eksekusi query dengan JOIN & Proteksi Ketat ID Penyelenggara
// Ditambahkan kondisi 'w.tipe_webinar = "berbayar"' karena webinar gratis otomatis disetujui di awal
$query = "SELECT p.*, w.judul, w.tanggal, w.tipe_webinar,
                 m.nama_mahasiswa, m.fakultas, m.jurusan 
          FROM pemantauan_webinar p 
          JOIN webinar w ON p.id_webinar = w.id_webinar 
          JOIN mahasiswa m ON p.npp = m.npp 
          WHERE p.status_pendaftaran = 'menunggu' 
            AND w.id_penyelenggara = ? 
            AND w.tipe_webinar = 'berbayar'
          ORDER BY p.tanggal_daftar DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $id_penyelenggara);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// 2. Ambil statistik secara dinamis spesifik HANYA untuk webinar milik penyelenggara yang login
$q_waiting = "SELECT COUNT(*) as total FROM pemantauan_webinar p JOIN webinar w ON p.id_webinar = w.id_webinar WHERE p.status_pendaftaran = 'menunggu' AND w.id_penyelenggara = ?";
$stmt_w = mysqli_prepare($conn, $q_waiting);
mysqli_stmt_bind_param($stmt_w, 'i', $id_penyelenggara);
mysqli_stmt_execute($stmt_w);
$count_waiting = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_w))['total'];

$q_approved = "SELECT COUNT(*) as total FROM pemantauan_webinar p JOIN webinar w ON p.id_webinar = w.id_webinar WHERE p.status_pendaftaran = 'disetujui' AND w.id_penyelenggara = ?";
$stmt_a = mysqli_prepare($conn, $q_approved);
mysqli_stmt_bind_param($stmt_a, 'i', $id_penyelenggara);
mysqli_stmt_execute($stmt_a);
$count_approved = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_a))['total'];

$q_rejected = "SELECT COUNT(*) as total FROM pemantauan_webinar p JOIN webinar w ON p.id_webinar = w.id_webinar WHERE p.status_pendaftaran = 'ditolak' AND w.id_penyelenggara = ?";
$stmt_r = mysqli_prepare($conn, $q_rejected);
mysqli_stmt_bind_param($stmt_r, 'i', $id_penyelenggara);
mysqli_stmt_execute($stmt_r);
$count_rejected = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_r))['total'];
?>

<div class="p-4 md:p-8 bg-slate-50 min-h-screen">
    <div class="max-w-6xl mx-auto">
        
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-black text-slate-800 tracking-tight italic uppercase">Verifikasi Pendaftaran</h2>
                <p class="text-slate-500 font-medium">Tinjau bukti transfer dan setujui partisipasi mahasiswa dalam webinar berbayar.</p>
            </div>
            <div class="flex gap-2">
                <span class="px-4 py-2 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-600 shadow-sm">
                    Total Pengajuan Anda: <?= number_format($count_waiting + $count_approved + $count_rejected) ?>
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
            <div class="bg-white p-6 rounded-[24px] border border-orange-100 shadow-sm relative overflow-hidden">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-orange-50 rounded-full"></div>
                <p class="text-orange-600 font-black uppercase tracking-wider text-xs mb-1 relative">Menunggu Konfirmasi</p>
                <h3 class="text-4xl font-black text-slate-800 relative"><?= number_format($count_waiting) ?></h3>
                <p class="text-slate-400 text-sm mt-2">Perlu pemeriksaan bukti transfer</p>
            </div>

            <div class="bg-white p-6 rounded-[24px] border border-teal-100 shadow-sm relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-teal-50 rounded-full"></div>
                <p class="text-teal-600 font-black uppercase tracking-wider text-xs mb-1 relative">Disetujui</p>
                <h3 class="text-4xl font-black text-slate-800 relative"><?= number_format($count_approved) ?></h3>
                <p class="text-slate-400 text-sm mt-2">Telah mendapatkan tiket/akses grup</p>
            </div>

            <div class="bg-white p-6 rounded-[24px] border border-red-100 shadow-sm relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-red-50 rounded-full"></div>
                <p class="text-red-600 font-black uppercase tracking-wider text-xs mb-1 relative">Ditolak</p>
                <h3 class="text-4xl font-black text-slate-800 relative"><?= number_format($count_rejected) ?></h3>
                <p class="text-slate-400 text-sm mt-2">Bukti transfer tidak valid/salah</p>
            </div>
        </div>

        <div class="space-y-4">
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($pendaftaran = mysqli_fetch_assoc($result)): ?>
                <div class="bg-white p-5 md:p-6 rounded-[28px] md:rounded-[32px] shadow-sm border border-slate-100 hover:border-teal-500/30 transition-all group">
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6">

                        <div class="flex flex-col sm:flex-row gap-4 md:gap-5 flex-1">
                            <div class="w-12 h-12 md:w-14 md:h-14 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-400 text-lg md:text-xl shrink-0 group-hover:bg-teal-600 group-hover:text-white transition-all">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            
                            <div class="min-w-0 flex-1">
                                <h4 class="text-lg md:text-xl font-bold text-slate-800 truncate"><?= htmlspecialchars($pendaftaran['nama_mahasiswa']) ?></h4>
                                <div class="flex flex-wrap gap-x-3 gap-y-2 mt-2 text-[11px] md:text-sm text-slate-500 font-medium">
                                    <span class="bg-slate-50 px-2 py-1 rounded-md border border-slate-100"><i class="fas fa-id-badge mr-1 text-teal-500"></i> <?= htmlspecialchars($pendaftaran['npp']) ?></span>
                                    <span class="bg-slate-50 px-2 py-1 rounded-md border border-slate-100"><i class="fas fa-university mr-1 text-teal-500"></i> <?= htmlspecialchars($pendaftaran['fakultas']) ?></span>
                                    <span class="bg-slate-50 px-2 py-1 rounded-md border border-slate-100"><i class="fas fa-calendar-alt mr-1 text-teal-500"></i> <?= date('d/m/Y', strtotime($pendaftaran['tanggal_daftar'])) ?></span>
                                </div>
                                
                                <div class="mt-4 p-4 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Webinar Target</p>
                                    <p class="text-sm md:text-base text-slate-700 font-bold leading-tight"><?= htmlspecialchars($pendaftaran['judul']) ?></p>
                                    
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 mt-3">Motivasi Mengikuti</p>
                                    <p class="text-xs text-slate-500 italic font-medium leading-relaxed">
                                        <i class="fas fa-quote-left mr-1 text-slate-300"></i> 
                                        <?= htmlspecialchars($pendaftaran['motivasi'] ?? 'Tidak ada motivasi yang ditulis.') ?>
                                    </p>

                                    <?php if(!empty($pendaftaran['bukti_bayar'])): ?>
                                    <div class="mt-4">
                                        <button onclick="viewImage('../assets/img/bukti_bayar/<?= htmlspecialchars($pendaftaran['bukti_bayar']) ?>')" 
                                                class="text-xs font-bold text-teal-600 hover:text-teal-700 flex items-center gap-1 bg-white px-3 py-1.5 rounded-lg border border-teal-100 shadow-sm transition-all shadow-teal-500/5">
                                            <i class="fas fa-image"></i> PERIKSA BUKTI PEMBAYARAN
                                        </button>
                                    </div>
                                    <?php else: ?>
                                    <div class="mt-3 text-xs font-bold text-rose-600 flex items-center gap-1">
                                        <i class="fas fa-exclamation-circle"></i> Peringatan: Mahasiswa belum mengunggah bukti bayar.
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-row lg:flex-col gap-2 shrink-0 w-full lg:w-auto">
                            <button onclick="approveRegistration(<?= $pendaftaran['id_pendaftaran'] ?>)" 
                                    class="flex-1 lg:w-36 px-4 py-3 bg-teal-600 text-white rounded-xl font-bold text-sm hover:bg-teal-700 transition-all flex items-center justify-center gap-2 shadow-lg shadow-teal-600/20">
                                <i class="fas fa-check"></i> Setujui
                            </button>
                            <button onclick="rejectRegistration(<?= $pendaftaran['id_pendaftaran'] ?>)" 
                                    class="flex-1 lg:w-36 px-4 py-3 bg-white border-2 border-red-100 text-red-600 rounded-xl font-bold text-sm hover:bg-red-50 transition-all flex items-center justify-center gap-2">
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
                    <h3 class="text-xl font-bold text-slate-800">Tidak ada pengajuan verifikasi baru</h3>
                    <p class="text-slate-500 mt-1">Semua pendaftaran berbayar telah diproses, atau belum ada mahasiswa yang mendaftar.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="modalPreview" class="fixed inset-0 bg-slate-900/80 hidden z-50 flex items-center justify-center p-4 backdrop-blur-sm" onclick="this.classList.add('hidden')">
    <div class="max-w-xl w-full bg-white rounded-3xl p-2 shadow-2xl animate-slide-down" onclick="event.stopPropagation()">
        <div class="relative max-h-[80vh] overflow-y-auto rounded-2xl bg-slate-100">
            <img id="imgSource" src="" class="w-full h-auto rounded-2xl block mx-auto" alt="Bukti Transfer Mahasiswa">
        </div>
        <button onclick="document.getElementById('modalPreview').classList.add('hidden')" class="w-full py-3 mt-2 text-slate-500 hover:text-slate-800 font-bold text-sm transition-colors">Tutup Preview</button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if(isset($_SESSION['success'])): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '<?= htmlspecialchars($_SESSION['success']) ?>',
        timer: 2000,
        showConfirmButton: false,
        borderRadius: '1.5rem'
    });
</script>
<?php unset($_SESSION['success']); ?>
<?php endif; ?>

<script>
function viewImage(path) {
    document.getElementById('imgSource').src = path;
    document.getElementById('modalPreview').classList.remove('hidden');
}

// Handler Konfirmasi Setujui Pendaftaran via SweetAlert
function approveRegistration(id) {
    Swal.fire({
        title: 'Setujui Pendaftaran?',
        text: "Mahasiswa akan langsung mendapatkan akses link grup WhatsApp dan hak klaim poin SKKM.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0d9488', 
        cancelButtonColor: '#64748b', 
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

// Handler Konfirmasi Tolak Pendaftaran via SweetAlert
function rejectRegistration(id) {
    Swal.fire({
        title: 'Tolk Pendaftaran?',
        text: "Pastikan bukti bayar tidak sesuai/palsu sebelum menolak pengajuan ini.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48', 
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

<style>
@keyframes slide-down {
    from { opacity: 0; transform: translateY(-15px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-slide-down { animation: slide-down 0.25s ease-out forwards; }
</style>

<?php require_once 'includes/footer.php'; ?>