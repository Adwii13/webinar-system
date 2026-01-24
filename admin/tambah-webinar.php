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
    $poin_skkm   = intval($_POST['poin_skkm'] ?? 0);
    $kuota_peserta = intval($_POST['kuota_peserta'] ?? 0);
    
    // Perbaikan urutan: Biaya dulu baru Tipe
    $tipe_webinar = clean_input($_POST['tipe_webinar'] ?? 'gratis');
    $biaya        = ($tipe_webinar === 'berbayar') ? floatval($_POST['biaya'] ?? 0) : 0;
    
    $status       = clean_input($_POST['status'] ?? 'draft');
    $tgl_mulai_reg = !empty($_POST['tanggal_mulai_pendaftaran']) ? $_POST['tanggal_mulai_pendaftaran'] : null;
    $tgl_akhir_reg = !empty($_POST['tanggal_akhir_pendaftaran']) ? $_POST['tanggal_akhir_pendaftaran'] : null;

    if ($is_edit && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        // UPDATE disesuaikan urutan describe: biaya (d), tipe_webinar (s), status (s)
        $query = "UPDATE webinar SET 
                  judul = ?, deskripsi = ?, kategori = ?, tanggal = ?, 
                  waktu_mulai = ?, waktu_selesai = ?, pembicara = ?, 
                  platform = ?, poin_skkm = ?, kuota_peserta = ?, 
                  biaya = ?, tipe_webinar = ?, status = ?, 
                  tanggal_mulai_pendaftaran = ?, tanggal_akhir_pendaftaran = ?
                  WHERE id_webinar = ?";
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ssssssssiidssssi', 
            $judul, $deskripsi, $kategori, $tanggal, 
            $waktu_mulai, $waktu_selesai, $pembicara, $platform, 
            $poin_skkm, $kuota_peserta, $biaya, $tipe_webinar, 
            $status, $tgl_mulai_reg, $tgl_akhir_reg, $id);
    } else {
        // INSERT disesuaikan urutan describe
        $query = "INSERT INTO webinar (
                    judul, deskripsi, kategori, tanggal, waktu_mulai, 
                    waktu_selesai, pembicara, platform, poin_skkm, kuota_peserta, 
                    biaya, tipe_webinar, status, tanggal_mulai_pendaftaran, 
                    tanggal_akhir_pendaftaran, id_penyelenggara, status_verifikasi
                  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 'menunggu')";
        
        $stmt = mysqli_prepare($conn, $query);
        // Bind 15 parameter: biaya menggunakan 'd'
        mysqli_stmt_bind_param($stmt, 'ssssssssiidssss', 
            $judul, $deskripsi, $kategori, $tanggal, 
            $waktu_mulai, $waktu_selesai, $pembicara, $platform, 
            $poin_skkm, $kuota_peserta, $biaya, $tipe_webinar, 
            $status, $tgl_mulai_reg, $tgl_akhir_reg);
    }

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = $is_edit ? "Webinar diperbarui!" : "Webinar ditambahkan!";
        
        // Ganti header() dengan script ini:
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

        <form method="POST" class="space-y-8">
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
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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

                        <div id="biaya_field" style="<?= ($is_edit && $webinar_data['tipe_webinar'] == 'berbayar') ? 'display: block;' : 'display: none;'; ?>">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Harga Tiket (Rp)</label>
                            <input type="number" name="biaya" min="0" 
                                   value="<?= $is_edit ? $webinar_data['biaya'] : ''; ?>"
                                   class="w-full px-5 py-3 bg-white border-2 border-teal-500 rounded-2xl outline-none shadow-lg shadow-teal-500/10">
                        </div>

                        <div>
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