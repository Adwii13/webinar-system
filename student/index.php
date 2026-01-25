<?php
require_once '../config/database.php';
require_once 'includes/header.php';

// Ambil statistik dasar
$total_webinar = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM webinar WHERE status = 'publish' AND status_verifikasi = 'disetujui'"))['total'];

// Ambil webinar terbaru (max 4)
$query_webinar = "SELECT * FROM webinar 
                  WHERE status = 'publish' AND status_verifikasi = 'disetujui' 
                  AND tanggal >= CURDATE()
                  ORDER BY tanggal ASC LIMIT 4";
$result_webinar = mysqli_query($conn, $query_webinar);
?>

<div class="space-y-8">
    
<div class="relative overflow-hidden bg-teal-600 rounded-3xl p-6 md:p-8 text-white shadow-xl shadow-teal-900/10 max-w-4xl mx-auto">
        <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="max-w-xl text-center md:text-left">
                <h1 class="text-2xl md:text-3xl font-black mb-2 leading-tight tracking-tight">
                    Halo, Mahasiswa UNIBI! 👋
                </h1>
                <p class="text-teal-50 text-sm md:text-base font-medium mb-6 opacity-90">
                    Tingkatkan skill dan kumpulkan poin SKKM dengan mengikuti webinar berkualitas.
                </p>
                <div class="flex flex-wrap justify-center md:justify-start gap-3">
                    <a href="daftar-webinar.php" class="px-6 py-2.5 bg-white text-teal-600 text-sm font-bold rounded-lg hover:bg-teal-50 transition-all shadow-md">
                        Jelajahi Webinar
                    </a>
                    <a href="riwayat.php" class="px-6 py-2.5 bg-teal-500 text-white border border-white/20 text-sm font-bold rounded-lg hover:bg-teal-400 transition-all">
                        Riwayat
                    </a>
                </div>
            </div>
            
            <div class="hidden lg:block">
                <div class="w-32 h-32 bg-white/10 rounded-2xl flex items-center justify-center backdrop-blur-3xl rotate-12">
                     <i class="fas fa-user-graduate text-5xl text-white/50 -rotate-12"></i>
                </div>
            </div>
        </div>
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-teal-500 rounded-full opacity-40"></div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex items-center gap-5 ">
            <div class="w-14 h-14 bg-teal-50 text-teal-600 rounded-xl flex items-center justify-center text-2xl shadow-sm">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Tersedia</p>
                <p class="text-2xl font-black text-slate-800"><?= $total_webinar ?></p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex items-center gap-5">
            <div class="w-14 h-14 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center text-2xl shadow-sm">
                <i class="fas fa-star"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Target SKKM</p>
                <p class="text-2xl font-black text-slate-800">20+</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex items-center gap-5">
            <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-2xl shadow-sm">
                <i class="fas fa-user-tie"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Pembicara</p>
                <p class="text-2xl font-black text-slate-800">15+</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex items-center gap-5">
            <div class="w-14 h-14 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center text-2xl shadow-sm">
                <i class="fas fa-tags"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Kategori</p>
                <p class="text-2xl font-black text-slate-800">5</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <div>
                <h3 class="text-xl font-bold text-slate-800 uppercase">Webinar Terbaru</h3>
                <p class="text-sm text-slate-500 font-medium">Jangan lewatkan kesempatan belajar langsung</p>
            </div>
            <a href="daftar-webinar.php" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl text-sm hover:bg-teal-600 hover:text-white transition-all shadow-sm">Lihat Semua</a>
        </div>
        
        <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php if(mysqli_num_rows($result_webinar) > 0): ?>
                    <?php while($webinar = mysqli_fetch_assoc($result_webinar)): 
                        $id_w = $webinar['id_webinar'];
                        $peserta = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jumlah FROM pemantauan_webinar WHERE id_webinar = $id_w AND status_pendaftaran = 'disetujui'"))['jumlah'];
                        $persentase = ($peserta / $webinar['kuota_peserta']) * 100;
                    ?>
                    <div class="bg-white rounded-2xl p-6 border border-slate-100 hover:border-teal-500 transition-all hover:shadow-xl hover:shadow-slate-200/50 group">
                        <div class="flex justify-between items-start mb-6">
                            <span class="px-3 py-1 bg-teal-50 text-teal-600 text-[10px] font-black uppercase rounded-lg"><?= $webinar['kategori'] ?></span>
                            <div class="bg-slate-50 px-3 py-1 rounded-lg border border-slate-100">
                                <span class="text-lg font-black text-slate-800"><?= $webinar['poin_skkm'] ?></span>
                                <span class="text-[8px] font-bold text-slate-400 uppercase">SKKM</span>
                            </div>
                        </div>
                        
                        <h4 class="text-lg font-bold text-slate-800 mb-4 group-hover:text-teal-600 transition-colors line-clamp-1"><?= htmlspecialchars($webinar['judul']) ?></h4>
                        
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="flex items-center gap-2 text-slate-500 text-xs font-semibold uppercase tracking-wider">
                                <i class="far fa-calendar-alt text-teal-500"></i> <?= date('d M Y', strtotime($webinar['tanggal'])) ?>
                            </div>
                            <div class="flex items-center gap-2 text-slate-500 text-xs font-semibold uppercase tracking-wider">
                                <i class="fas fa-user-tie text-teal-500"></i> <?= explode(' ', htmlspecialchars($webinar['pembicara']))[0] ?>...
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div class="flex justify-between text-[10px] font-bold uppercase">
                                <span class="text-slate-400">Kuota Terisi</span>
                                <span class="text-slate-700"><?= $peserta ?>/<?= $webinar['kuota_peserta'] ?></span>
                            </div>
                            <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-teal-500 rounded-full" style="width: <?= min(100, $persentase) ?>%"></div>
                            </div>
                        </div>

                        <a href="detail-webinar.php?id=<?= $webinar['id_webinar'] ?>" 
                           class="mt-6 block w-full py-3 bg-slate-900 text-white font-bold rounded-xl text-center text-sm hover:bg-teal-600 transition-all shadow-lg shadow-slate-200">
                            Detail Webinar
                        </a>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-full py-10 text-center text-slate-400 font-medium italic">
                        Belum ada webinar baru yang tersedia.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>