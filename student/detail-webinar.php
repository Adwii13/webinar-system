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

// --- PROSES LOGIKA PENDAFTARAN ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_daftar'])) {
    $npp = mysqli_real_escape_string($conn, $_POST['npp']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $fakultas = mysqli_real_escape_string($conn, $_POST['fakultas']);
    $jurusan = mysqli_real_escape_string($conn, $_POST['jurusan']);
    $motivasi = mysqli_real_escape_string($conn, $_POST['motivasi']);
    
    // 1. Cek apakah sudah pernah mendaftar
    $check = mysqli_query($conn, "SELECT id_pendaftaran FROM pemantauan_webinar WHERE id_webinar = $id AND npp = '$npp'");
    
    if (mysqli_num_rows($check) > 0) {
        $_SESSION['error'] = "Anda sudah terdaftar di webinar ini!";
    } else {
        $bukti_bayar = "";
        $upload_ok = true;

        // 2. Logika upload jika berbayar
        if ($webinar['tipe_webinar'] == 'berbayar') {
            if (isset($_FILES['bukti_bayar']) && $_FILES['bukti_bayar']['error'] == 0) {
                $target_dir = "../assets/img/qr/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                
                $file_ext = pathinfo($_FILES["bukti_bayar"]["name"], PATHINFO_EXTENSION);
                $new_filename = "BAYAR_" . $npp . "_" . time() . "." . $file_ext;
                $target_file = $target_dir . $new_filename;

                if (getimagesize($_FILES["bukti_bayar"]["tmp_name"])) {
                    if (!move_uploaded_file($_FILES["bukti_bayar"]["tmp_name"], $target_file)) {
                        $upload_ok = false;
                        $_SESSION['error'] = "Gagal mengunggah bukti pembayaran.";
                    } else {
                        $bukti_bayar = $new_filename;
                    }
                } else {
                    $upload_ok = false;
                    $_SESSION['error'] = "File bukan gambar valid.";
                }
            } else {
                $upload_ok = false;
                $_SESSION['error'] = "Bukti pembayaran wajib diunggah.";
            }
        }

        // 3. Eksekusi Simpan Jika Upload OK
        if ($upload_ok) {
            // Update/Insert data mahasiswa
            mysqli_query($conn, "INSERT INTO mahasiswa (npp, nama_mahasiswa, fakultas, jurusan) 
                                VALUES ('$npp', '$nama', '$fakultas', '$jurusan')
                                ON DUPLICATE KEY UPDATE nama_mahasiswa='$nama', fakultas='$fakultas', jurusan='$jurusan'");

            // Simpan pendaftaran ke pemantauan
            $ins = mysqli_query($conn, "INSERT INTO pemantauan_webinar (id_webinar, npp, motivasi, status_pendaftaran, bukti_bayar) 
                                        VALUES ($id, '$npp', '$motivasi', 'menunggu', '$bukti_bayar')");
            if ($ins) {
                $_SESSION['success'] = "Pendaftaran berhasil! Tunggu verifikasi admin.";
                header("Location: detail-webinar.php?id=$id");
                exit();
            } else {
                $_SESSION['error'] = "Gagal menyimpan pendaftaran ke database.";
            }
        }
    }
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

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        
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

        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-sm font-black text-slate-700 uppercase ml-1">NIM / NPP <span class="text-rose-500">*</span></label>
                    <input type="text" name="npp" required placeholder="Contoh: 2021110045"
                           class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-teal-500 transition-all outline-none">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-black text-slate-700 uppercase ml-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama" required placeholder="Nama Lengkap Anda"
                           class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-teal-500 transition-all outline-none">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-black text-slate-700 uppercase ml-1">Fakultas <span class="text-rose-500">*</span></label>
                    <select name="fakultas" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-teal-500 transition-all outline-none appearance-none font-medium text-slate-600">
                        <option value="" disable selected hidden>Pilih Fakultas</option>
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
            <?php if($webinar['tipe_webinar'] == 'berbayar'): ?>
            <div class="bg-amber-50 p-6 rounded-[2rem] border border-amber-200 mb-6 text-center">
                <h4 class="font-black text-amber-800 uppercase text-sm tracking-widest mb-4">Instruksi Pembayaran</h4>
                
                <div class="bg-white p-4 inline-block rounded-2xl shadow-sm mb-4">
                    <img src="../assets/img/qr/<?= $webinar['qr_code'] ?>" alt="QR Code Bayar" class="w-48 h-48 object-contain">
                </div>
                
                <p class="text-slate-600 font-bold mb-4">Total Tagihan: <span class="text-teal-600 text-xl">Rp <?= number_format($webinar['biaya']) ?></span></p>
                
                <div class="text-left">
                    <label class="block font-black text-slate-700 text-xs uppercase mb-2">Upload Bukti Transfer (Screenshot)</label>
                    <input type="file" name="bukti_bayar" required class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-teal-600 file:text-white hover:file:bg-teal-700">
                </div>
                <!-- <input type="file" name="bukti_bayar" accept="image/*" required 
                onchange="validateFile(this)"
                class="w-full p-3 bg-white rounded-xl border border-amber-200"> -->

                <script>
                function validateFile(input) {
                    const filePath = input.value;
                    const allowedExtensions = /(\.jpg|\.jpeg|\.png)$/i;
                    if (!allowedExtensions.exec(filePath)) {
                        alert('Mohon upload file gambar (JPG/PNG)');
                        input.value = '';
                        return false;
                    }
                    if (input.files[0].size > 2000000) { // 2MB
                        alert('Ukuran file terlalu besar (Maksimal 2MB)');
                        input.value = '';
                        return false;
                    }
                }
                </script>
            </div>
        <?php endif; ?>

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