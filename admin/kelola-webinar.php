<?php
require_once 'includes/admin-guard.php';
require_once '../config/database.php';
require_once 'includes/header.php';

// Ambil semua data webinar dengan fitur filter pencarian
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$where = '';
if (!empty($search)) {
    $where = " WHERE judul LIKE '%$search%' OR pembicara LIKE '%$search%' OR kategori LIKE '%$search%'";
}

$query = "SELECT * FROM webinar $where ORDER BY tanggal DESC";
$result = mysqli_query($conn, $query);
?>

<div class="p-4 md:p-8 bg-slate-50 min-h-screen">

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-10">
        <div>
            <h2 class="text-3xl font-black text-slate-800 tracking-tight">Kelola Webinar</h2>
            <p class="text-slate-500 font-medium text-sm">Manajemen data utama, kendali status publikasi, dan monitoring kapasitas slot event.</p>
        </div>
    </div>

    <div class="bg-white rounded-[32px] shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 md:p-8 border-b border-slate-100 flex flex-col lg:flex-row justify-between items-center gap-6">
            <h3 class="text-xl font-bold text-slate-800">Semua Berkas List Webinar</h3>
            
            <form method="GET" class="relative w-full lg:w-96">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                       placeholder="Cari judul, kategori atau nama pembicara..." 
                       class="w-full pl-12 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all font-medium text-sm text-slate-700">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <?php if(!empty($search)): ?>
                    <a href="kelola-webinar.php" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-500 transition-colors">
                        <i class="fas fa-times-circle"></i>
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100">
                        <th class="p-6 text-[11px] font-black text-slate-400 uppercase tracking-widest">Informasi Webinar</th>
                        <th class="p-6 text-[11px] font-black text-slate-400 uppercase tracking-widest text-center">Jadwal Pelaksanaan</th>
                        <th class="p-6 text-[11px] font-black text-slate-400 uppercase tracking-widest text-center">Status Sistem</th>
                        <th class="p-6 text-[11px] font-black text-slate-400 uppercase tracking-widest text-center">Kapasitas Peserta</th>
                        <th class="p-6 text-[11px] font-black text-slate-400 uppercase tracking-widest text-center">Aksi Manajemen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($webinar = mysqli_fetch_assoc($result)): 
                            $id_w = $webinar['id_webinar'];
                            // Query pencarian total peserta tervalidasi
                            $q_count = mysqli_query($conn, "SELECT COUNT(*) as jml FROM pemantauan_webinar WHERE id_webinar = $id_w AND status_pendaftaran = 'disetujui'");
                            $count = mysqli_fetch_assoc($q_count);
                            
                            // Mappings styling badge visual komponen
                            $status_map = [
                                'publish' => 'bg-emerald-50 text-emerald-700 border border-emerald-200/60',
                                'draft'   => 'bg-slate-100 text-slate-600 border border-slate-200/40',
                                'closed'  => 'bg-rose-50 text-rose-700 border border-rose-200/60'
                            ];
                            $verify_map = [
                                'disetujui' => 'bg-teal-50 text-teal-700 border border-teal-200/60',
                                'menunggu'  => 'bg-amber-50 text-amber-700 border border-amber-200/60',
                                'ditolak'   => 'bg-rose-100 text-rose-800'
                            ];
                        ?>
                        <tr class="hover:bg-slate-50/40 transition-colors">
                            <td class="p-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-teal-50 rounded-2xl flex items-center justify-center text-teal-600 font-bold shrink-0">
                                        <i class="fas fa-graduation-cap text-base"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-800 leading-snug mb-1 truncate max-w-md"><?= htmlspecialchars($webinar['judul']) ?></p>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="text-[9px] font-black px-2 py-0.5 bg-slate-100 text-slate-500 rounded uppercase tracking-wider"><?= htmlspecialchars($webinar['kategori']) ?></span>
                                            <span class="text-xs text-slate-400 italic">Oleh: <?= htmlspecialchars($webinar['pembicara']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-6 text-center whitespace-nowrap">
                                <p class="text-sm font-bold text-slate-700"><?= date('d M Y', strtotime($webinar['tanggal'])) ?></p>
                                <p class="text-[11px] text-slate-400 font-semibold tracking-tight mt-0.5"><?= date('H:i', strtotime($webinar['waktu_mulai'])) ?> - <?= date('H:i', strtotime($webinar['waktu_selesai'])) ?> WIB</p>
                            </td>
                            <td class="p-6 text-center space-y-1.5 whitespace-nowrap">
                                <span class="inline-block px-2.5 py-0.5 rounded-md text-[9px] font-black uppercase tracking-widest <?= $status_map[$webinar['status']] ?>">
                                    <?= htmlspecialchars($webinar['status']) ?>
                                </span>
                                <span class="inline-block px-2.5 py-0.5 rounded-md text-[9px] font-black uppercase tracking-widest <?= $verify_map[$webinar['status_verifikasi']] ?>">
                                    <?= htmlspecialchars($webinar['status_verifikasi']) ?>
                                </span>
                            </td>
                            <td class="p-6 text-center whitespace-nowrap">
                                <div class="flex flex-col items-center">
                                    <span class="text-base font-black text-slate-800"><?= $count['jml'] ?></span>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">/<?= $webinar['kuota_peserta'] ?> Kuota</span>
                                </div>
                            </td>
                            <td class="p-6">
                                <div class="flex justify-center gap-2">
                                    <a href="detail-webinar.php?id=<?= $webinar['id_webinar'] ?>" class="p-2 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 hover:text-teal-600 transition-all shadow-sm" title="Detail">
                                        <i class="far fa-eye text-sm"></i>
                                    </a>
                                    <form method="POST" action="proses-aksi.php" onsubmit="confirmDelete(event, this, <?= $count['jml'] ?>, '<?= $webinar['status'] ?>')" class="inline">
                                        <input type="hidden" name="id" value="<?= $webinar['id_webinar'] ?>">
                                        <input type="hidden" name="action" value="delete_webinar">
                                        
                                        <button type="submit" class="p-2 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-rose-50 hover:text-rose-600 transition-all shadow-sm" title="Hapus">
                                            <i class="far fa-trash-alt text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="p-16 text-center">
                                <h3 class="text-lg font-black text-slate-700">Tidak ada webinar ditemukan</h3>
                                <p class="text-sm text-slate-400 mb-6">Kata kunci tidak cocok atau data record kosong dalam sistem.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Fungsi Toast Loader Progresif
function showToast(message, type = 'success') {
    const alert = `
        <div id="toast" class="fixed bottom-10 right-10 z-[100] p-4 rounded-2xl shadow-2xl bg-slate-900 text-white flex items-center gap-4 animate-slide-up">
            <div class="${type === 'success' ? 'text-teal-400' : 'text-rose-400'}">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            </div>
            <p class="text-sm font-bold">${message}</p>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', alert);
    setTimeout(() => {
        const toast = document.getElementById('toast');
        if(toast) toast.remove();
    }, 3000);
}

// Interseptor Konfirmasi Validasi Hapus via SweetAlert2
function confirmDelete(event, form, jumlahPeserta, status) {
    event.preventDefault();

    // Validasi Bisnis: Jika webinar aktif (Publish/Draft) dan sudah ada pendaftar, blokir penghapusan
    if (status !== 'closed' && jumlahPeserta > 0) {
        Swal.fire({
            icon: 'error',
            title: 'Penghapusan Ditolak',
            html: `Webinar berstatus <span class="font-bold text-teal-600">${status.toUpperCase()}</span> yang telah memiliki peserta aktif tidak boleh dihapus demi integritas data.<br><br>Ubah status menjadi <span class="font-bold text-rose-600">Closed</span> terlebih dahulu jika event telah selesai.`,
            confirmButtonColor: '#0d9488',
            borderRadius: '1.5rem'
        });
        return;
    }

    // Konfirmasi final penghapusan data
    Swal.fire({
        title: 'Hapus Berkas Webinar?',
        text: status === 'closed' ? "Webinar ini sudah ditutup. Menghapusnya akan ikut menghapus seluruh riwayat transaksional peserta di dalamnya!" : "Tindakan ini permanen, data yang terhapus tidak dapat dikembalikan.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batalkan',
        reverseButtons: true,
        borderRadius: '1.5rem'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
}
</script>

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
            title: 'Gagal Proses!',
            text: '<?= htmlspecialchars($_SESSION['error']) ?>',
            confirmButtonColor: '#e11d48',
            borderRadius: '1.5rem'
        });
    });
</script>
<?php unset($_SESSION['error']); endif; ?>

<style>
@keyframes slide-up {
    from { transform: translateY(100px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
.animate-slide-up { animation: slide-up 0.4s ease-out forwards; }
</style>

<?php require_once 'includes/footer.php'; ?>