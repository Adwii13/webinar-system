<?php
require_once '../config/database.php';
require_once '../includes/header.php';

// Ambil semua webinar dengan pencarian
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$where = '';
if (!empty($search)) {
    $where = " WHERE judul LIKE '%$search%' OR pembicara LIKE '%$search%' OR kategori LIKE '%$search%'";
}

$query = "SELECT * FROM webinar $where ORDER BY tanggal DESC";
$result = mysqli_query($conn, $query);
?>

<div class="p-8 bg-slate-50 min-h-screen">
    <?php if (isset($_SESSION['success']) || isset($_SESSION['error'])): ?>
        <div id="alert-box" class="mb-6 flex items-center p-4 rounded-2xl shadow-lg border <?= isset($_SESSION['success']) ? 'bg-emerald-50 border-emerald-100 text-emerald-800' : 'bg-red-50 border-red-100 text-red-800' ?> animate-bounce-short">
            <i class="fas <?= isset($_SESSION['success']) ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-3 text-xl"></i>
            <span class="font-bold"><?= $_SESSION['success'] ?? $_SESSION['error'] ?></span>
            <button onclick="document.getElementById('alert-box').remove()" class="ml-auto bg-white/50 hover:bg-white p-1 rounded-lg transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <?php unset($_SESSION['success']); unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-10">
        <div>
            <h2 class="text-3xl font-black text-slate-800 tracking-tight">Kelola Webinar</h2>
            <p class="text-slate-500 font-medium">Manajemen data, status publikasi, dan laporan webinar</p>
        </div>
        <a href="tambah-webinar.php" class="px-6 py-3 bg-teal-600 text-white rounded-2xl font-bold hover:bg-teal-700 transition-all flex items-center gap-3 shadow-lg shadow-teal-900/20 group">
            <i class="fas fa-plus group-hover:rotate-90 transition-transform"></i>
            Tambah Webinar Baru
        </a>
    </div>

    <div class="bg-white rounded-[32px] p-8 shadow-sm border border-slate-100 mb-8">
        <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-6">Aksi Cepat</h3>
        <div class="flex flex-wrap gap-4">
            <button onclick="exportReport('pdf')" class="flex items-center gap-3 px-5 py-3 bg-slate-100 text-slate-700 rounded-xl font-bold hover:bg-slate-200 transition-all border border-slate-200">
                <i class="far fa-file-pdf text-red-500"></i> Export PDF
            </button>
            <a href="verifikasi-pendaftaran.php" class="flex items-center gap-3 px-5 py-3 bg-teal-50 text-teal-700 rounded-xl font-bold hover:bg-teal-100 transition-all border border-teal-100">
                <i class="fas fa-user-check"></i> Verifikasi Pendaftaran
            </a>
        </div>
    </div>

    <div class="bg-white rounded-[32px] shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-8 border-b border-slate-50 flex flex-col lg:flex-row justify-between items-center gap-6">
            <h3 class="text-xl font-bold text-slate-800">Semua List Webinar</h3>
            
            <form method="GET" class="relative w-full lg:w-96">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                       placeholder="Cari judul, kategori atau pembicara..." 
                       class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all font-medium">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <?php if(!empty($search)): ?>
                    <a href="kelola-webinar.php" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-500">
                        <i class="fas fa-times-circle"></i>
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="p-6 text-[11px] font-black text-slate-400 uppercase tracking-widest">Informasi Webinar</th>
                        <th class="p-6 text-[11px] font-black text-slate-400 uppercase tracking-widest text-center">Jadwal</th>
                        <th class="p-6 text-[11px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                        <th class="p-6 text-[11px] font-black text-slate-400 uppercase tracking-widest text-center">Peserta</th>
                        <th class="p-6 text-[11px] font-black text-slate-400 uppercase tracking-widest text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($webinar = mysqli_fetch_assoc($result)): 
                            // Query jumlah peserta
                            $id_w = $webinar['id_webinar'];
                            $q_count = mysqli_query($conn, "SELECT COUNT(*) as jml FROM pemantauan_webinar WHERE id_webinar = $id_w AND status_pendaftaran = 'disetujui'");
                            $count = mysqli_fetch_assoc($q_count);
                            
                            // Badge logic
                            $status_map = [
                                'publish' => 'bg-emerald-100 text-emerald-700',
                                'draft' => 'bg-slate-100 text-slate-600',
                                'closed' => 'bg-red-100 text-red-700'
                            ];
                            $verify_map = [
                                'disetujui' => 'bg-blue-100 text-blue-700',
                                'menunggu' => 'bg-amber-100 text-amber-700',
                                'ditolak' => 'bg-rose-100 text-rose-700'
                            ];
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="p-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-teal-50 rounded-2xl flex items-center justify-center text-teal-600 font-bold">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800 leading-tight mb-1"><?= htmlspecialchars($webinar['judul']) ?></p>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-black px-2 py-0.5 bg-slate-100 text-slate-500 rounded uppercase tracking-tighter"><?= $webinar['kategori'] ?></span>
                                            <span class="text-xs text-slate-400 italic">By <?= $webinar['pembicara'] ?></span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-6 text-center">
                                <p class="text-sm font-bold text-slate-700"><?= date('d M Y', strtotime($webinar['tanggal'])) ?></p>
                                <p class="text-[11px] text-slate-400 font-medium tracking-tighter"><?= date('H:i', strtotime($webinar['waktu_mulai'])) ?> - <?= date('H:i', strtotime($webinar['waktu_selesai'])) ?> WIB</p>
                            </td>
                            <td class="p-6 text-center space-y-2">
                                <span class="block px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest <?= $status_map[$webinar['status']] ?>">
                                    <?= $webinar['status'] ?>
                                </span>
                                <span class="block px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest <?= $verify_map[$webinar['status_verifikasi']] ?>">
                                    <?= $webinar['status_verifikasi'] ?>
                                </span>
                            </td>
                            <td class="p-6 text-center">
                                <div class="flex flex-col items-center">
                                    <span class="text-lg font-black text-slate-800"><?= $count['jml'] ?></span>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">/<?= $webinar['kuota_peserta'] ?> Seats</span>
                                </div>
                            </td>
                            <td class="p-6">
                                <div class="flex justify-center gap-2">
                                    <a href="detail-webinar.php?id=<?= $webinar['id_webinar'] ?>" class="p-2.5 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 hover:text-teal-600 transition-all shadow-sm">
                                        <i class="far fa-eye"></i>
                                    </a>
                                    <a href="tambah-webinar.php?edit=<?= $webinar['id_webinar'] ?>" class="p-2.5 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 hover:text-amber-600 transition-all shadow-sm">
                                        <i class="far fa-edit"></i>
                                    </a>
                                    <form method="POST" action="proses-aksi.php" onsubmit="return confirm('Hapus permanen webinar ini?')" class="inline">
                                        <input type="hidden" name="id" value="<?= $webinar['id_webinar'] ?>">
                                        <input type="hidden" name="action" value="delete_webinar">
                                        <button class="p-2.5 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-red-50 hover:text-red-600 transition-all shadow-sm">
                                            <i class="far fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="p-20 text-center">
                                <img src="https://illustrations.popsy.co/teal/empty-folder.svg" class="w-48 mx-auto mb-6 opacity-20" alt="Empty">
                                <h3 class="text-xl font-bold text-slate-400">Tidak ada webinar ditemukan</h3>
                                <p class="text-slate-300 mb-6">Mulai buat event baru untuk melihat data di sini.</p>
                                <a href="tambah-webinar.php" class="inline-flex items-center px-5 py-2.5 bg-teal-600 text-white rounded-xl font-bold hover:bg-teal-700 transition-all">
                                    Tambah Webinar Pertama
                                </a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Notification Helper
function showToast(message, type = 'success') {
    const alert = `
        <div id="toast" class="fixed bottom-10 right-10 z-[100] p-4 rounded-2xl shadow-2xl bg-slate-900 text-white flex items-center gap-4 animate-slide-up">
            <div class="${type === 'success' ? 'text-emerald-400' : 'text-red-400'}">
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

function exportReport(type) {
    showToast(`Menyiapkan data ${type.toUpperCase()}...`, 'success');
}
</script>

<style>
@keyframes slide-up {
    from { transform: translateY(100px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
.animate-slide-up { animation: slide-up 0.4s ease-out forwards; }
.animate-bounce-short { animation: bounce 1s ease-in-out 1; }
</style>

<?php require_once '../includes/footer.php'; ?>