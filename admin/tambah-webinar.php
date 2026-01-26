<?php
require_once '../config/database.php';
require_once '../includes/header.php';

$is_edit = isset($_GET['edit']);
$webinar_data = null;

// Jika mode edit, ambil data webinar
if ($is_edit) {
    $id = intval($_GET['edit']);
    $query = "SELECT * FROM webinar WHERE id_webinar = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $webinar_data = mysqli_fetch_assoc($result);
    
    if (!$webinar_data) {
        $_SESSION['error'] = "Webinar tidak ditemukan!";
        echo "<script>window.location.href='kelola-webinar.php';</script>";
        exit();
    }
}

// Proses form submit
// Proses form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Ambil Data
    $judul       = clean_input($_POST['judul'] ?? '');
    $deskripsi   = clean_input($_POST['deskripsi'] ?? '');
    $kategori    = clean_input($_POST['kategori'] ?? 'Teknologi');
    $tanggal     = clean_input($_POST['tanggal'] ?? '');
    $waktu_mulai = clean_input($_POST['waktu_mulai'] ?? '');
    $waktu_selesai = clean_input($_POST['waktu_selesai'] ?? '');
    $pembicara   = clean_input($_POST['pembicara'] ?? '');
    $platform    = clean_input($_POST['platform'] ?? 'Zoom');
    $link_group = clean_input($_POST['link_group'] ?? '');
    $poin_skkm   = intval($_POST['poin_skkm'] ?? 0);
    $kuota_peserta = intval($_POST['kuota_peserta'] ?? 0);
    
    // Perbaikan urutan: Biaya dulu baru Tipe
    $tipe_webinar = clean_input($_POST['tipe_webinar'] ?? 'gratis');
    $biaya        = ($tipe_webinar === 'berbayar') ? floatval($_POST['biaya'] ?? 0) : 0;
    
    $status       = clean_input($_POST['status'] ?? 'draft');
    $tgl_mulai_reg = !empty($_POST['tanggal_mulai_pendaftaran']) ? $_POST['tanggal_mulai_pendaftaran'] : null;
    $tgl_akhir_reg = !empty($_POST['tanggal_akhir_pendaftaran']) ? $_POST['tanggal_akhir_pendaftaran'] : null;

// Inisialisasi awal: jika edit pakai yang lama, jika baru kosongkan
$qr_to_save = $is_edit ? $webinar_data['qr_code'] : '';

if (isset($_FILES['qr_code']) && $_FILES['qr_code']['error'] == 0) {
    $target_dir = "../assets/img/qr/";
    // if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    $file_ext = pathinfo($_FILES["qr_code"]["name"], PATHINFO_EXTENSION);
    $new_filename = "QR_" . time() . "_" . rand(100, 999) . "." . $file_ext;
    $target_file = $target_dir . $new_filename;

    if (move_uploaded_file($_FILES["qr_code"]["tmp_name"], $target_dir . $new_filename)) {
        $qr_to_save = $new_filename; // Simpan nama file ke variabel yang akan masuk ke DB
    }
}

// --- PROSES DATABASE ---
if ($is_edit && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $query = "UPDATE webinar SET 
              judul = ?, deskripsi = ?, kategori = ?, tanggal = ?, 
              waktu_mulai = ?, waktu_selesai = ?, pembicara = ?, 
              platform = ?, link_group = ?, poin_skkm = ?, kuota_peserta = ?, 
              biaya = ?, qr_code = ?, tipe_webinar = ?, status = ?, 
              tanggal_mulai_pendaftaran = ?, tanggal_akhir_pendaftaran = ?
              WHERE id_webinar = ?";
    
    $stmt = mysqli_prepare($conn, $query);
    // URUTAN HARUS SAMA: 10 's', 1 'd' (biaya), 5 's', 1 'i' (id)
    mysqli_stmt_bind_param($stmt, 'sssssssssiidsssssi', 
        $judul, $deskripsi, $kategori, $tanggal, 
        $waktu_mulai, $waktu_selesai, $pembicara, $platform, $link_group, 
        $poin_skkm, $kuota_peserta, $biaya, $qr_to_save, $tipe_webinar, 
        $status, $tgl_mulai_reg, $tgl_akhir_reg, $id);
} else {
    $query = "INSERT INTO webinar (
                judul, deskripsi, kategori, tanggal, waktu_mulai, 
                waktu_selesai, pembicara, platform, link_group ,poin_skkm, kuota_peserta, 
                biaya, qr_code, tipe_webinar, status, tanggal_mulai_pendaftaran, 
                tanggal_akhir_pendaftaran, id_penyelenggara, status_verifikasi
              ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 'menunggu')";
    
    $stmt = mysqli_prepare($conn, $query);
    // Bind 16 parameter (disesuaikan dengan jumlah '?' di atas)
    mysqli_stmt_bind_param($stmt, 'sssssssssiidsssss', 
        $judul, $deskripsi, $kategori, $tanggal, 
        $waktu_mulai, $waktu_selesai, $pembicara, $platform, $link_group,
        $poin_skkm, $kuota_peserta, $biaya, $qr_to_save, $tipe_webinar, 
        $status, $tgl_mulai_reg, $tgl_akhir_reg);
}
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = $is_edit ? "Webinar diperbarui!" : "Webinar ditambahkan!";

        echo "<script>window.location.href='kelola-webinar.php';</script>";
        exit();
    } else {
        die("Gagal Simpan: " . mysqli_stmt_error($stmt));
    }
}
?>

<div class="p-4 md:p-8 bg-slate-50 min-h-screen">
    <div class="max-w-4xl mx-auto">
        <div class="mb-8">
            <a href="kelola-webinar.php" class="text-teal-600 font-bold flex items-center gap-2 mb-2 hover:gap-3 transition-all">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar
            </a>
            <h2 class="text-3xl font-black text-slate-800 tracking-tight">
                <?= $is_edit ? 'Modifikasi Webinar' : 'Ciptakan Webinar Baru'; ?>
            </h2>
            <p class="text-slate-500 font-medium italic">Harap lengkapi semua field bertanda bintang (*)</p>
        </div>

        <?php if (isset($error_msg)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-xl text-red-700 shadow-sm flex items-center gap-3">
                <i class="fas fa-exclamation-triangle"></i> <?= $error_msg ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-8">
            <?php if ($is_edit): ?>
                <input type="hidden" name="id" value="<?= $webinar_data['id_webinar']; ?>">
            <?php endif; ?>

            <div class="bg-white rounded-[32px] p-8 shadow-sm border border-slate-100 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-8 opacity-5 text-8xl text-teal-600">
                    <i class="fas fa-info-circle"></i>
                </div>
                
                <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-3">
                    <span class="w-8 h-8 bg-teal-600 text-white rounded-lg flex items-center justify-center text-sm italic font-black">1</span>
                    Detail Utama Webinar
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Judul Webinar *</label>
                        <input type="text" name="judul" required placeholder="Contoh: Masterclass AI Untuk Pemula" 
                               value="<?= $is_edit ? htmlspecialchars($webinar_data['judul']) : ''; ?>"
                               class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Deskripsi Lengkap *</label>
                        <textarea name="deskripsi" rows="4" required placeholder="Jelaskan apa yang akan dipelajari peserta..."
                                  class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all"><?= $is_edit ? htmlspecialchars($webinar_data['deskripsi']) : ''; ?></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Kategori</label>
                        <select name="kategori" required class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all appearance-none cursor-pointer">
                            <?php 
                            $cats = ['Teknologi', 'Bisnis', 'Pendidikan', 'Kesehatan', 'Lingkungan'];
                            foreach($cats as $cat): ?>
                                <option value="<?= $cat ?>" <?= ($is_edit && $webinar_data['kategori'] == $cat) ? 'selected' : ''; ?>><?= $cat ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Platform</label>
                        <select name="platform" class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all appearance-none cursor-pointer">
                            <?php 
                            $platforms = ['Zoom', 'Google Meet', 'Microsoft Teams', 'YouTube Live'];
                            foreach($platforms as $plat): ?>
                                <option value="<?= $plat ?>" <?= ($is_edit && $webinar_data['platform'] == $plat) ? 'selected' : ''; ?>><?= $plat ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-700 ml-1">Link Grup WhatsApp (Opsional)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-emerald-500">
                                <i class="fa-brands fa-whatsapp"></i>
                            </div>
                            <input type="url" name="link_group" 
                                placeholder="https://chat.whatsapp.com/..." 
                                class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all outline-none text-sm">
                        </div>
                        <p class="text-[10px] text-slate-400 ml-1 leading-relaxed">
                            *Link ini hanya akan muncul di halaman riwayat mahasiswa yang pendaftarannya telah disetujui.
                        </p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Nama Pembicara *</label>
                        <input type="text" name="pembicara" required placeholder="Contoh: Dr. Jane Doe" 
                               value="<?= $is_edit ? htmlspecialchars($webinar_data['pembicara']) : ''; ?>"
                               class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[32px] p-8 shadow-sm border border-slate-100">
                <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-3">
                    <span class="w-8 h-8 bg-teal-600 text-white rounded-lg flex items-center justify-center text-sm italic font-black">2</span>
                    Waktu & Kapasitas
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Hari Pelaksanaan *</label>
                        <input type="date" name="tanggal" required 
                               value="<?= $is_edit ? $webinar_data['tanggal'] : ''; ?>"
                               class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Jam Mulai *</label>
                        <input type="time" name="waktu_mulai" required 
                               value="<?= $is_edit ? $webinar_data['waktu_mulai'] : ''; ?>"
                               class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Jam Selesai *</label>
                        <input type="time" name="waktu_selesai" required 
                               value="<?= $is_edit ? $webinar_data['waktu_selesai'] : ''; ?>"
                               class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">SKKM (Poin) *</label>
                        <input type="number" name="poin_skkm" required min="0" placeholder="0"
                               value="<?= $is_edit ? $webinar_data['poin_skkm'] : ''; ?>"
                               class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Kapasitas (User) *</label>
                        <input type="number" name="kuota_peserta" required min="1" placeholder="100"
                               value="<?= $is_edit ? $webinar_data['kuota_peserta'] : ''; ?>"
                               class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-white rounded-[32px] p-8 shadow-sm border border-slate-100">
                    <h3 class="text-xl font-bold text-slate-800 mb-6">Timeline Pendaftaran</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Start Registration</label>
                            <input type="datetime-local" name="tanggal_mulai_pendaftaran" required
                                   value="<?= ($is_edit && $webinar_data['tanggal_mulai_pendaftaran']) ? date('Y-m-d\TH:i', strtotime($webinar_data['tanggal_mulai_pendaftaran'])) : ''; ?>"
                                   class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">End Registration</label>
                            <input type="datetime-local" name="tanggal_akhir_pendaftaran" required
                                   value="<?= ($is_edit && $webinar_data['tanggal_akhir_pendaftaran']) ? date('Y-m-d\TH:i', strtotime($webinar_data['tanggal_akhir_pendaftaran'])) : ''; ?>"
                                   class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none">
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-[32px] p-8 shadow-sm border border-slate-100">
                    <h3 class="text-xl font-bold text-slate-800 mb-6">Metode & Status</h3>
                    <div class="space-y-6">
                        <div class="flex items-center gap-6 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-600">
                                <input type="radio" name="tipe_webinar" value="gratis" class="w-4 h-4 text-teal-600 focus:ring-teal-500"
                                       <?php echo (!$is_edit || $webinar_data['tipe_webinar'] == 'gratis') ? 'checked' : ''; ?>> Gratis
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-600">
                                <input type="radio" name="tipe_webinar" value="berbayar" class="w-4 h-4 text-teal-600 focus:ring-teal-500"
                                       <?php echo ($is_edit && $webinar_data['tipe_webinar'] == 'berbayar') ? 'checked' : ''; ?>> Berbayar
                            </label>
                        </div>

                <div id="biaya_field" class="<?= ($is_edit && $webinar_data['tipe_webinar'] == 'berbayar') ? '' : 'hidden'; ?> space-y-4 mt-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Harga Tiket (Rp)</label>
                        <input type="number" id="biaya_input" name="biaya" min="0" required
                        value="<?= $is_edit ? $webinar_data['biaya'] : ''; ?>"
                            class="w-full px-5 py-3 bg-white border-2 border-teal-500 rounded-2xl outline-none shadow-lg shadow-teal-500/10"
                            placeholder="Contoh: 50000">
                    </div>

                    <div class="p-4 bg-teal-50 rounded-2xl border border-dashed border-teal-200">
                        <label class="block text-sm font-bold text-teal-800 mb-2">Upload QR Code Pembayaran (Dummy)</label>
                        <input type="file" name="qr_code" id="qr_input" accept="image/*" <?= $is_edit ? '' : 'required' ?>
                            class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-teal-600 file:text-white hover:file:bg-teal-700">
                        <?php if($is_edit && !empty($webinar_data['qr_code'])): ?>
                            <p class="text-xs text-teal-600 mt-2 italic font-medium">QR Code sudah tersedia. Upload ulang jika ingin mengganti.</p>
                        <?php endif; ?>
                    </div>
                </div>

<script>
function toggleBiaya() {
    const tipe = document.querySelector('input[name="tipe_webinar"]:checked').value;
    const biayaField = document.getElementById('biaya_field');
    const biayaInput = document.getElementById('biaya_input');
    const qrInput = document.getElementById('qr_input');

    if (tipe === 'berbayar') {
        biayaField.classList.remove('hidden');
        biayaField.style.display = 'block';
        biayaInput.setAttribute('required', 'required');
        // Hanya wajibkan QR jika sedang tambah baru (bukan edit)
        <?php if(!$is_edit): ?>
        if (qrInput) qrInput.setAttribute('required', 'required');
        <?php endif; ?>
    } else {
        biayaField.classList.add('hidden');
        biayaField.style.display = 'none';
        biayaInput.removeAttribute('required');
        if (qrInput) qrInput.removeAttribute('required');
        biayaInput.value = 0; // Reset harga ke 0 jika gratis
    }
}

// Jalankan saat ada perubahan klik
document.querySelectorAll('input[name="tipe_webinar"]').forEach(radio => {
    radio.addEventListener('change', toggleBiaya);
});

// Jalankan sekali saat halaman dimuat (untuk mode edit)
document.addEventListener('DOMContentLoaded', toggleBiaya);

// Validasi Tanggal
document.querySelector('form').addEventListener('submit', function(e) {
    const startReg = new Date(document.querySelector('input[name="tanggal_mulai_pendaftaran"]').value);
    const endReg = new Date(document.querySelector('input[name="tanggal_akhir_pendaftaran"]').value);
    const eventDate = new Date(document.querySelector('input[name="tanggal"]').value);
    
    if (endReg <= startReg) {
        e.preventDefault();
        alert('❌ Error: Pendaftaran tidak bisa ditutup sebelum atau pada saat dibuka!');
    } else if (eventDate < endReg) {
        e.preventDefault();
        alert('❌ Error: Hari pelaksanaan webinar tidak boleh mendahului penutupan pendaftaran!');
    }
});
</script>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Status Publikasi</label>
                            <select name="status" class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none">
                                <option value="draft" <?= ($is_edit && $webinar_data['status'] == 'draft') ? 'selected' : ''; ?>>Simpan Sebagai Draft</option>
                                <option value="publish" <?= ($is_edit && $webinar_data['status'] == 'publish') ? 'selected' : ''; ?>>Publish Sekarang</option>
                                <option value="closed" <?= ($is_edit && $webinar_data['status'] == 'closed') ? 'selected' : ''; ?>>Tutup Pendaftaran</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col md:flex-row gap-4 justify-end pt-8">
                <a href="kelola-webinar.php" class="px-8 py-4 text-slate-500 font-bold hover:bg-slate-100 rounded-2xl transition-all text-center">
                    Batalkan
                </a>
                <button type="submit" class="px-10 py-4 bg-teal-600 text-white rounded-2xl font-black text-lg hover:bg-teal-700 transition-all shadow-xl shadow-teal-900/20 active:scale-95">
                    <?= $is_edit ? 'Simpan Perubahan' : 'Terbitkan Webinar'; ?>
                </button>
            </div>
    </form>
</div>

<script>
// Logic Toggle Biaya
document.querySelectorAll('input[name="tipe_webinar"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const biayaField = document.getElementById('biaya_field');
        if(this.value === 'berbayar') {
            biayaField.style.display = 'block';
            biayaField.classList.add('animate-slide-down');
        } else {
            biayaField.style.display = 'none';
        }
    });
});

// Validasi Tanggal JS yang lebih user-friendly
document.querySelector('form').addEventListener('submit', function(e) {
    const startReg = new Date(document.querySelector('input[name="tanggal_mulai_pendaftaran"]').value);
    const endReg = new Date(document.querySelector('input[name="tanggal_akhir_pendaftaran"]').value);
    const eventDate = new Date(document.querySelector('input[name="tanggal"]').value);
    
    if (endReg <= startReg) {
        e.preventDefault();
        alert('❌ Error: Pendaftaran tidak bisa ditutup sebelum atau pada saat dibuka!');
    }
    
    if (eventDate < endReg) {
        e.preventDefault();
        alert('❌ Error: Hari pelaksanaan webinar tidak boleh mendahului penutupan pendaftaran!');
    }
});
</script>

<style>
@keyframes slide-down {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-slide-down { animation: slide-down 0.3s ease-out forwards; }
</style>

<?php require_once '../includes/footer.php'; ?>