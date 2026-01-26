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

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
            <div class="bg-white p-6 rounded-[24px] border border-orange-100 shadow-sm relative overflow-hidden">
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

        <!-- <?php if(isset($_SESSION['success'])): ?>
            <div class="mb-6 p-4 bg-teal-50 border-l-4 border-teal-500 text-teal-700 rounded-r-xl flex items-center gap-3 animate-pulse">
                <i class="fas fa-check-circle"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?> -->

        <div class="space-y-4">
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($pendaftaran = mysqli_fetch_assoc($result)): ?>
                <div class="bg-white p-5 md:p-6 rounded-[28px] md:rounded-[32px] shadow-sm border border-slate-100 hover:border-teal-500/30 transition-all group">
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6">

                        <div class="flex flex-col sm:flex-row gap-4 md:gap-5">
                            <div class="w-12 h-12 md:w-14 md:h-14 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-400 text-lg md:text-xl shrink-0 group-hover:bg-teal-600 group-hover:text-white transition-all">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            
                            <div class="min-w-0 flex-1">
                                <h4 class="text-lg md:text-xl font-bold text-slate-800 truncate"><?= htmlspecialchars($pendaftaran['nama_mahasiswa']) ?></h4>
                                <div class="flex flex-wrap gap-x-3 gap-y-2 mt-2 text-[11px] md:text-sm text-slate-500 font-medium">
                                    <span class="bg-slate-50 px-2 py-1 rounded-md border border-slate-100"></span><i class="fas fa-id-badge mr-1 text-teal-500"></i> <?= htmlspecialchars($pendaftaran['npp']) ?></span>
                                    <span class="bg-slate-50 px-2 py-1 rounded-md border border-slate-100"></span><i class="fas fa-university mr-1 text-teal-500"></i> <?= htmlspecialchars($pendaftaran['fakultas']) ?></span>
                                    <span class="bg-slate-50 px-2 py-1 rounded-md border border-slate-100"></span><i class="fas fa-calendar-alt mr-1 text-teal-500"></i> <?= date('d/m/Y', strtotime($pendaftaran['tanggal_daftar'])) ?></span>
                                </div>
                                
                                <div class="mt-4 p-4 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Webinar Target</p>
                                    <p class="text-sm md:text-base text-slate-700 font-bold leading-tight"><?= htmlspecialchars($pendaftaran['judul']) ?></p>
                                    <p class="text-xs text-slate-500 mt-2 italic font-medium leading-relaxed">
                                        <i class="fas fa-quote-left mr-1 text-slate-300"></i> 
                                        <?= htmlspecialchars($pendaftaran['motivasi']) ?>
                                    </p>

                                    <?php if(!empty($pendaftaran['bukti_bayar'])): ?>
                                    <div class="mt-3">
                                        <button onclick="viewImage('../assets/img/qr/<?= $pendaftaran['bukti_bayar'] ?>')" 
                                                class="text-xs font-bold text-teal-600 hover:text-teal-700 flex items-center gap-1 bg-white px-3 py-1.5 rounded-lg border border-teal-100 shadow-sm transition-all">
                                            <i class="fas fa-image"></i> LIHAT BUKTI BAYAR
                                        </button>
                                    </div>
                                    <?php endif; ?>
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

<div id="modalPreview" class="fixed inset-0 bg-slate-900/80 hidden z-50 flex items-center justify-center p-4 backdrop-blur-sm" onclick="this.classList.add('hidden')">
    <div class="max-w-xl w-full bg-white rounded-3xl p-2 shadow-2xl" onclick="event.stopPropagation()">
        <img id="imgSource" src="" class="w-full h-auto rounded-2xl" alt="Bukti Transfer">
        <button onclick="document.getElementById('modalPreview').classList.add('hidden')" class="w-full py-3 text-slate-500 font-bold text-sm">Tutup Preview</button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if(isset($_SESSION['success'])): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '<?= $_SESSION['success'] ?>',
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

// Notifikasi untuk Setujui
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
        reverseButtons: true // Tombol 'Ya' akan pindah ke KANAN
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'proses-aksi.php?action=approve_registration&id=' + id;
        }
    })
}

// Notifikasi untuk Tolak
function rejectRegistration(id) {
    Swal.fire({
        title: 'Tolak Pendaftaran?',
        text: "Alasan penolakan akan membuat mahasiswa tidak bisa mengikuti webinar.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48', // Rose 600
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Tolak!',
        cancelButtonText: 'Batal',
        borderRadius: '1.5rem',
        reverseButtons: false // Tombol 'Ya' akan pindah ke KANAN
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'proses-aksi.php?action=reject_registration&id=' + id;
        }
    })
}
</script>

<?php require_once '../includes/footer.php'; ?>