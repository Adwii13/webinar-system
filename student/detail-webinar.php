<?php
require_once '../config/database.php';

// Pastikan session dimulai jika belum (biasanya di config, tapi untuk jaga-jaga)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_GET['id'])) {
    header('Location: daftar-webinar.php');
    exit();
}

$id = intval($_GET['id']);

// --- PROSES LOGIKA PENDAFTARAN ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_daftar'])) {
    $npp = mysqli_real_escape_string($conn, $_POST['npp']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $fakultas = mysqli_real_escape_string($conn, $_POST['fakultas']);
    $jurusan = mysqli_real_escape_string($conn, $_POST['jurusan']);
    $motivasi = mysqli_real_escape_string($conn, $_POST['motivasi']);
    
    // 1. Update/Insert ke tabel mahasiswa (Identitas)
    $query_mhs = "INSERT INTO mahasiswa (npp, nama_mahasiswa, fakultas, jurusan) 
                  VALUES ('$npp', '$nama', '$fakultas', '$jurusan')
                  ON DUPLICATE KEY UPDATE 
                  nama_mahasiswa = '$nama', fakultas = '$fakultas', jurusan = '$jurusan'";
    mysqli_query($conn, $query_mhs);

    // 2. Cek apakah sudah pernah mendaftar di webinar ini
    $check = mysqli_query($conn, "SELECT id_pendaftaran FROM pemantauan_webinar WHERE id_webinar = $id AND npp = '$npp'");
    
    if (mysqli_num_rows($check) > 0) {
        $_SESSION['error'] = "Anda sudah terdaftar di webinar ini!";
    } else {
        // 3. Simpan ke pemantauan_webinar (Relasi)
        $ins = mysqli_query($conn, "INSERT INTO pemantauan_webinar (id_webinar, npp, motivasi, status_pendaftaran) 
                                   VALUES ($id, '$npp', '$motivasi', 'menunggu')");
        if ($ins) {
            $_SESSION['success'] = "Pendaftaran berhasil! Mohon tunggu verifikasi admin.";
            header("Location: detail-webinar.php?id=$id");
            exit();
        } else {
            $_SESSION['error'] = "Terjadi kesalahan sistem.";
        }
    }
}

// --- AMBIL DATA WEBINAR UNTUK TAMPILAN ---
$query = "SELECT w.*, 
          (SELECT COUNT(*) FROM pemantauan_webinar p 
           WHERE p.id_webinar = w.id_webinar AND p.status_pendaftaran = 'disetujui') as peserta_terdaftar
          FROM webinar w 
          WHERE w.id_webinar = $id";
$result = mysqli_query($conn, $query);
$webinar = mysqli_fetch_assoc($result);

if (!$webinar) {
    header('Location: daftar-webinar.php');
    exit();
}

// Hitung variabel untuk UI
$sisa_kuota = $webinar['kuota_peserta'] - $webinar['peserta_terdaftar'];
$persentase = ($webinar['peserta_terdaftar'] / $webinar['kuota_peserta']) * 100;
$today = date('Y-m-d H:i:s');
$is_open = ($today >= $webinar['tanggal_mulai_pendaftaran'] && $today <= $webinar['tanggal_akhir_pendaftaran']);
$is_full = ($sisa_kuota <= 0);

require_once 'includes/header.php';

?>
<div class="max-w-7xl mx-auto px-4">
    <?php if(isset($_SESSION['success'])): ?>
        <div class="bg-emerald-100 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-6 flex justify-between items-center">
            <span><i class="fas fa-check-circle mr-2"></i> <?= $_SESSION['success'] ?></span>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="bg-rose-100 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl mb-6 flex justify-between items-center">
            <span><i class="fas fa-exclamation-circle mr-2"></i> <?= $_SESSION['error'] ?></span>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
</div>

<div class="space-y-6">
    <a href="daftar-webinar.php" class="inline-flex items-center gap-2 text-slate-500 hover:text-teal-600 font-bold transition-colors group">
        <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center group-hover:bg-teal-50 group-hover:border-teal-200">
            <i class="fas fa-arrow-left text-xs"></i>
        </div>
        Kembali ke Daftar
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-[2rem] p-8 border border-slate-200 shadow-sm">
                <div class="flex flex-wrap gap-3 mb-6">
                    <span class="px-4 py-1 bg-teal-50 text-teal-600 text-xs font-black uppercase rounded-full border border-teal-100 italic">
                        <?= $webinar['kategori'] ?>
                    </span>
                    <span class="px-4 py-1 bg-amber-50 text-amber-600 text-xs font-black uppercase rounded-full border border-amber-100">
                        <i class="fas fa-certificate mr-1"></i> E-Certificate
                    </span>
                </div>
                
                <h1 class="text-3xl md:text-4xl font-black text-slate-800 leading-tight mb-8"><?= htmlspecialchars($webinar['judul']) ?></h1>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 py-8 border-y border-slate-100">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Tanggal</p>
                        <p class="font-bold text-slate-700"><?= date('d M Y', strtotime($webinar['tanggal'])) ?></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Waktu</p>
                        <p class="font-bold text-slate-700"><?= date('H:i', strtotime($webinar['waktu_mulai'])) ?> WIB</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">SKKM</p>
                        <p class="font-bold text-teal-600"><?= $webinar['poin_skkm'] ?> Poin</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Platform</p>
                        <p class="font-bold text-slate-700"><?= htmlspecialchars($webinar['platform']) ?></p>
                    </div>
                </div>

                <div class="mt-8">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-align-left text-teal-500"></i> Deskripsi Webinar
                    </h3>
                    <div class="text-slate-600 leading-relaxed space-y-4">
                        <?= nl2br(htmlspecialchars($webinar['deskripsi'])) ?>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] p-8 border border-slate-200 shadow-sm flex flex-col md:flex-row items-center gap-8">
                <div class="w-32 h-32 bg-teal-100 rounded-full flex items-center justify-center flex-shrink-0 border-4 border-teal-50">
                    <i class="fas fa-user-tie text-5xl text-teal-600"></i>
                </div>
                <div class="text-center md:text-left">
                    <p class="text-teal-600 font-black uppercase text-xs tracking-widest mb-1">Narasumber / Speaker</p>
                    <h4 class="text-2xl font-black text-slate-800 mb-2"><?= htmlspecialchars($webinar['pembicara']) ?></h4>
                    <p class="text-slate-500 font-medium">Expert Speaker di bidang <?= $webinar['kategori'] ?></p>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-slate-900 rounded-[2rem] p-8 text-white shadow-xl shadow-slate-200 sticky top-6">
                <h3 class="text-xl font-bold mb-6 italic">Status Pendaftaran</h3>
                
                <div class="space-y-3 mb-8">
                    <div class="flex justify-between text-xs font-bold uppercase">
                        <span class="text-slate-400">Okupansi</span>
                        <span><?= $webinar['peserta_terdaftar'] ?> / <?= $webinar['kuota_peserta'] ?> Peserta</span>
                    </div>
                    <div class="h-2 bg-white/10 rounded-full overflow-hidden">
                        <div class="h-full bg-teal-400 rounded-full" style="width: <?= min(100, $persentase) ?>%"></div>
                    </div>
                    <?php if($is_full): ?>
                        <p class="text-rose-400 text-[10px] font-bold uppercase"><i class="fas fa-exclamation-triangle mr-1"></i> Maaf, kuota sudah penuh</p>
                    <?php endif; ?>
                </div>

                <div class="bg-white/5 border border-white/10 rounded-2xl p-4 mb-8">
                    <p class="text-xs text-slate-400 mb-1 italic">Investasi Pelatihan:</p>
                    <p class="text-2xl font-black <?= $webinar['tipe_webinar'] == 'gratis' ? 'text-teal-400' : 'text-rose-400' ?>">
                        <?= $webinar['tipe_webinar'] == 'gratis' ? 'GRATIS' : 'Rp '.number_format($webinar['biaya'], 0, ',', '.') ?>
                    </p>
                </div>

                <?php if($is_open && !$is_full): ?>
                    <button onclick="document.getElementById('form-pendaftaran').scrollIntoView({behavior: 'smooth'})" 
                            class="w-full py-4 bg-teal-500 hover:bg-teal-400 text-white font-black rounded-xl transition-all shadow-lg shadow-teal-900/20 uppercase tracking-widest">
                        Daftar Sekarang
                    </button>
                <?php else: ?>
                    <button disabled class="w-full py-4 bg-slate-800 text-slate-500 font-black rounded-xl cursor-not-allowed uppercase tracking-widest">
                        Pendaftaran Ditutup
                    </button>
                <?php endif; ?>

                <div class="mt-6 flex flex-col gap-3">
                    <div class="flex items-center gap-3 text-xs text-slate-400">
                        <i class="fas fa-clock text-teal-500 w-4"></i>
                        <span>Buka: <?= date('d M Y, H:i', strtotime($webinar['tanggal_mulai_pendaftaran'])) ?></span>
                    </div>
                    <div class="flex items-center gap-3 text-xs text-slate-400">
                        <i class="fas fa-calendar-times text-rose-500 w-4"></i>
                        <span>Tutup: <?= date('d M Y, H:i', strtotime($webinar['tanggal_akhir_pendaftaran'])) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="form-pendaftaran" class="mt-12 bg-white rounded-[2.5rem] p-8 md:p-12 border border-slate-200 shadow-sm max-w-4xl mx-auto">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-black text-slate-800 italic uppercase">Formulir Peserta</h2>
            <p class="text-slate-500 font-medium">Pastikan data yang Anda masukkan sesuai dengan KTM (Kartu Tanda Mahasiswa)</p>
        </div>

        <form method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-sm font-black text-slate-700 uppercase ml-1">NIM / NPP <span class="text-rose-500">*</span></label>
                    <input type="text" name="npp" required placeholder="Contoh: 2021110045"
                           class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-teal-500 transition-all outline-none">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-black text-slate-700 uppercase ml-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama" required placeholder="Nama sesuai kartu mahasiswa"
                           class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-teal-500 transition-all outline-none">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-black text-slate-700 uppercase ml-1">Fakultas <span class="text-rose-500">*</span></label>
                    <select name="fakultas" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-teal-500 transition-all outline-none appearance-none font-medium text-slate-600">
                        <option value="">Pilih Fakultas</option>
                        <option value="Teknik">Teknik</option>
                        <option value="Ekonomi">Ekonomi</option>
                        <option value="Ilmu Komputer">Ilmu Komputer</option>
                        </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-black text-slate-700 uppercase ml-1">Program Studi <span class="text-rose-500">*</span></label>
                    <input type="text" name="jurusan" required placeholder="Contoh: Teknik Informatika"
                           class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-teal-500 transition-all outline-none">
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-black text-slate-700 uppercase ml-1">Motivasi Mengikuti <span class="text-rose-500">*</span></label>
                <textarea name="motivasi" rows="4" required placeholder="Jelaskan alasan Anda mengikuti webinar ini..."
                          class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-teal-500 transition-all outline-none"></textarea>
            </div>

            <div class="pt-6">
                <button type="submit" name="btn_daftar" class="w-full py-5 bg-slate-900 text-white font-black rounded-2xl hover:bg-teal-600 transition-all shadow-xl shadow-slate-200 tracking-widest uppercase">
                    Kirim Pendaftaran Saya
                </button>
                <p class="text-center text-xs text-slate-400 mt-4 italic italic">
                    <i class="fas fa-info-circle mr-1"></i> Data akan dikirimkan ke Admin UNIBI untuk proses verifikasi.
                </p>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>