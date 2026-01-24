<?php
require_once '../config/database.php';
require_once 'includes/header.php';

// Helper function jika belum ada di config
if (!function_exists('clean_input')) {
    function clean_input($data) {
        global $conn;
        return mysqli_real_escape_string($conn, htmlspecialchars(strip_tags(trim($data))));
    }
}

// Filter kategori
$kategori_filter = isset($_GET['kategori']) ? clean_input($_GET['kategori']) : '';
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';

// Query dasar (Disesuaikan dengan tabel Anda)
$query = "SELECT w.*, 
          (SELECT COUNT(*) FROM pemantauan_webinar p 
           WHERE p.id_webinar = w.id_webinar AND p.status_pendaftaran = 'disetujui') as peserta_terdaftar
          FROM webinar w 
          WHERE w.status = 'publish' 
          AND w.status_verifikasi = 'disetujui'
          AND w.tanggal >= CURDATE()";

if (!empty($kategori_filter)) $query .= " AND w.kategori = '$kategori_filter'";
if (!empty($search)) $query .= " AND (w.judul LIKE '%$search%' OR w.pembicara LIKE '%$search%')";

$query .= " ORDER BY w.tanggal ASC";
$result = mysqli_query($conn, $query);

// Ambil kategori unik untuk tab filter
$kategori_result = mysqli_query($conn, "SELECT DISTINCT kategori FROM webinar WHERE status = 'publish' AND status_verifikasi = 'disetujui'");
?>

<div class="space-y-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h2 class="text-3xl font-black text-slate-800 tracking-tight italic uppercase">Eksplorasi Webinar</h2>
            <p class="text-slate-500 font-medium">Temukan topik menarik dan kumpulkan poin SKKM Anda.</p>
        </div>
        
        <form method="GET" class="relative group w-full md:w-80">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                   placeholder="Cari judul atau pembicara..." 
                   class="w-full pl-12 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all outline-none text-sm shadow-sm">
            <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-teal-500 transition-colors"></i>
            <?php if($kategori_filter): ?> <input type="hidden" name="kategori" value="<?= $kategori_filter ?>"> <?php endif; ?>
        </form>
    </div>

    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-hide">
        <a href="daftar-webinar.php" 
           class="px-6 py-2.5 rounded-xl font-bold text-sm transition-all whitespace-nowrap <?= empty($kategori_filter) ? 'bg-teal-600 text-white shadow-lg shadow-teal-200' : 'bg-white text-slate-500 hover:bg-slate-100 border border-slate-200' ?>">
            Semua
        </a>
        <?php while($cat = mysqli_fetch_assoc($kategori_result)): ?>
            <a href="daftar-webinar.php?kategori=<?= urlencode($cat['kategori']) ?>&search=<?= $search ?>" 
               class="px-6 py-2.5 rounded-xl font-bold text-sm transition-all whitespace-nowrap <?= $kategori_filter == $cat['kategori'] ? 'bg-teal-600 text-white shadow-lg shadow-teal-200' : 'bg-white text-slate-500 hover:bg-slate-100 border border-slate-200' ?>">
                <?= htmlspecialchars($cat['kategori']) ?>
            </a>
        <?php endwhile; ?>
    </div>

    <?php if(mysqli_num_rows($result) > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php while($webinar = mysqli_fetch_assoc($result)): 
                $sisa_kuota = $webinar['kuota_peserta'] - $webinar['peserta_terdaftar'];
                $persentase = ($webinar['peserta_terdaftar'] / $webinar['kuota_peserta']) * 100;
                
                $today = date('Y-m-d H:i:s');
                $is_open = ($today >= $webinar['tanggal_mulai_pendaftaran'] && $today <= $webinar['tanggal_akhir_pendaftaran']);
                $is_full = $sisa_kuota <= 0;
            ?>
            <div class="group bg-white rounded-3xl border border-slate-200 overflow-hidden hover:shadow-2xl hover:shadow-slate-200 hover:-translate-y-1 transition-all duration-300">
                <div class="p-6 bg-gradient-to-br from-teal-500 to-teal-700 text-white relative">
                    <div class="flex justify-between items-start">
                        <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-lg text-[10px] font-black uppercase tracking-widest italic">
                            <?= htmlspecialchars($webinar['kategori']) ?>
                        </span>
                        <div class="text-right">
                            <p class="text-2xl font-black leading-none"><?= $webinar['poin_skkm'] ?></p>
                            <p class="text-[10px] font-bold opacity-70 uppercase">SKKM</p>
                        </div>
                    </div>
                    <h3 class="mt-4 text-lg font-bold leading-tight line-clamp-2 h-14"><?= htmlspecialchars($webinar['judul']) ?></h3>
                </div>

                <div class="p-6 space-y-5">
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 text-slate-500 text-sm font-medium">
                            <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-teal-600">
                                <i class="far fa-calendar-alt"></i>
                            </div>
                            <?= date('d M Y', strtotime($webinar['tanggal'])) ?>
                        </div>
                        <div class="flex items-center gap-3 text-slate-500 text-sm font-medium">
                            <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-teal-600">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <span class="truncate"><?= htmlspecialchars($webinar['pembicara']) ?></span>
                        </div>
                    </div>

                    <div class="pt-2">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs font-bold text-slate-400 uppercase">Ketersediaan Kursi</span>
                            <span class="text-xs font-black <?= $is_full ? 'text-rose-500' : 'text-teal-600' ?>">
                                <?= $webinar['peserta_terdaftar'] ?> / <?= $webinar['kuota_peserta'] ?>
                            </span>
                        </div>
                        <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-teal-500 transition-all duration-700" style="width: <?= min(100, $persentase) ?>%"></div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 pt-2">
                        <?php if(!$is_open): ?>
                            <div class="w-full py-3 bg-amber-50 text-amber-600 text-xs font-black uppercase text-center rounded-xl border border-amber-100">
                                <i class="fas fa-lock mr-2"></i> Pendaftaran Belum Dibuka
                            </div>
                        <?php elseif($is_full): ?>
                            <div class="w-full py-3 bg-rose-50 text-rose-600 text-xs font-black uppercase text-center rounded-xl border border-rose-100">
                                <i class="fas fa-times-circle mr-2"></i> Kuota Sudah Penuh
                            </div>
                        <?php else: ?>
                            <a href="detail-webinar.php?id=<?= $webinar['id_webinar'] ?>&daftar=true" 
                               class="w-full py-3 bg-teal-600 text-white font-bold rounded-xl text-center hover:bg-teal-700 transition-all shadow-lg shadow-teal-100">
                                Daftar Sekarang
                            </a>
                        <?php endif; ?>
                        
                        <a href="detail-webinar.php?id=<?= $webinar['id_webinar'] ?>" 
                           class="w-full py-3 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl text-center hover:bg-slate-50 transition-all text-sm">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-[3rem] border border-slate-200 p-20 text-center">
            <div class="w-24 h-24 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-6 text-4xl">
                <i class="fas fa-search"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-2">Tidak ada webinar ditemukan</h3>
            <p class="text-slate-500 mb-8 max-w-xs mx-auto text-sm">Coba ubah kata kunci pencarian Anda atau pilih kategori lain.</p>
            <a href="daftar-webinar.php" class="inline-flex items-center gap-2 px-8 py-3 bg-teal-600 text-white font-bold rounded-xl hover:bg-teal-700 transition-all">
                <i class="fas fa-redo"></i> Reset Pencarian
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>