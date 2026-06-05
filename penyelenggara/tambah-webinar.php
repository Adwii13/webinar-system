<?php
require_once 'includes/guard-penyelenggara.php';
require_once '../config/database.php';
require_once 'includes/header.php';

// Fungsi helper penyehat input
if (!function_exists('clean_input')) {
    function clean_input($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }
}

$id_penyelenggara = $_SESSION['id_user'];
$is_edit = isset($_GET['edit']);
$webinar_data = null;

// Jika mode edit, ambil data webinar
if ($is_edit) {
    $id = intval($_GET['edit']);
    $query = "SELECT * FROM webinar WHERE id_webinar = ? AND id_penyelenggara = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $id, $id_penyelenggara);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $webinar_data = mysqli_fetch_assoc($result);
    
    if (!$webinar_data) {
        $_SESSION['error'] = "Webinar tidak ditemukan atau Anda tidak memiliki hak akses!";
        echo "<script>window.location.href='tambah-webinar.php';</script>";
        exit();
    }
}

$show_success_modal = false;
$success_message = "";

// Proses form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul          = clean_input($_POST['judul'] ?? '');
    $deskripsi      = clean_input($_POST['deskripsi'] ?? '');
    $kategori       = clean_input($_POST['kategori'] ?? 'Teknologi');
    $target_jurusan = clean_input($_POST['target_jurusan'] ?? 'Semua'); 
    $tanggal        = clean_input($_POST['tanggal'] ?? '');
    $waktu_mulai    = clean_input($_POST['waktu_mulai'] ?? '');
    $waktu_selesai  = clean_input($_POST['waktu_selesai'] ?? '');
    $pembicara      = clean_input($_POST['pembicara'] ?? '');
    $platform       = clean_input($_POST['platform'] ?? 'Zoom');
    $link_group     = clean_input($_POST['link_group'] ?? '');
    $poin_skkm      = intval($_POST['poin_skkm'] ?? 0);
    $kuota_peserta  = intval($_POST['kuota_peserta'] ?? 0);
    
    $tipe_webinar   = clean_input($_POST['tipe_webinar'] ?? 'gratis');
    $biaya          = ($tipe_webinar === 'berbayar') ? floatval($_POST['biaya'] ?? 0) : 0;
    
    $tgl_mulai_reg  = !empty($_POST['tanggal_mulai_pendaftaran']) ? $_POST['tanggal_mulai_pendaftaran'] : null;
    $tgl_akhir_reg  = !empty($_POST['tanggal_akhir_pendaftaran']) ? $_POST['tanggal_akhir_pendaftaran'] : null;

    $qr_to_save = $is_edit ? $webinar_data['qr_code'] : '';

    if (isset($_FILES['qr_code']) && $_FILES['qr_code']['error'] == 0) {
        $target_dir = "../assets/img/qr/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_ext = pathinfo($_FILES["qr_code"]["name"], PATHINFO_EXTENSION);
        $new_filename = "QR_" . time() . "_" . rand(100, 999) . "." . $file_ext;
        
        if (move_uploaded_file($_FILES["qr_code"]["tmp_name"], $target_dir . $new_filename)) {
            $qr_to_save = $new_filename; 
        }
    }

    // --- PROSES DATABASE ---
    if ($is_edit && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        
        $query = "UPDATE webinar SET 
                  judul = ?, deskripsi = ?, kategori = ?, target_jurusan = ?, tanggal = ?, 
                  waktu_mulai = ?, waktu_selesai = ?, pembicara = ?, platform = ?, link_group = ?, 
                  poin_skkm = ?, kuota_peserta = ?, biaya = ?, tipe_webinar = ?, qr_code = ?, 
                  status = ?, status_verifikasi = ?, tanggal_mulai_pendaftaran = ?, tanggal_akhir_pendaftaran = ?
                  WHERE id_webinar = ? AND id_penyelenggara = ?";
        
        $stmt = mysqli_prepare($conn, $query);
        
        $status_draft = 'draft';
        $status_verifikasi_menunggu = 'menunggu';

        mysqli_stmt_bind_param($stmt, 'ssssssssssiisssssssii', 
            $judul, $deskripsi, $kategori, $target_jurusan, $tanggal, 
            $waktu_mulai, $waktu_selesai, $pembicara, $platform, $link_group, 
            $poin_skkm, $kuota_peserta, $biaya, $tipe_webinar, $qr_to_save, 
            $status_draft, $status_verifikasi_menunggu, $tgl_mulai_reg, $tgl_akhir_reg,
            $id, $id_penyelenggara);
            
    } else {
        $query = "INSERT INTO webinar (
                    judul, deskripsi, kategori, target_jurusan, tanggal, 
                    waktu_mulai, waktu_selesai, pembicara, platform, link_group, 
                    poin_skkm, kuota_peserta, biaya, tipe_webinar, qr_code, 
                    status, status_verifikasi, tanggal_mulai_pendaftaran, tanggal_akhir_pendaftaran, id_penyelenggara
                  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $query);
        
        $status_draft = 'draft';
        $status_verifikasi_menunggu = 'menunggu';

        mysqli_stmt_bind_param($stmt, 'ssssssssssiisssssssi', 
            $judul, $deskripsi, $kategori, $target_jurusan, $tanggal, 
            $waktu_mulai, $waktu_selesai, $pembicara, $platform, $link_group, 
            $poin_skkm, $kuota_peserta, $biaya, $tipe_webinar, $qr_to_save, 
            $status_draft, $status_verifikasi_menunggu, $tgl_mulai_reg, $tgl_akhir_reg, $id_penyelenggara);
    }

    if (mysqli_stmt_execute($stmt)) {
        $show_success_modal = true;
        $success_message = $is_edit ? "Perubahan webinar Anda berhasil disimpan dan sekarang masuk kembali ke antrean verifikasi." : "Webinar Anda berhasil diajukan! Harap tunggu proses verifikasi oleh Admin.";
    } else {
        die("Gagal Simpan ke Database: " . mysqli_stmt_error($stmt));
    }
}
?>

<div class="p-4 md:p-8 bg-slate-50 min-h-screen">
    <div class="max-w-4xl mx-auto">
        <div class="mb-8">
            <h2 class="text-3xl font-black text-slate-800 tracking-tight">
                <?= $is_edit ? 'Modifikasi Webinar' : 'Ciptakan Webinar Baru'; ?>
            </h2>
            <p class="text-slate-500 font-medium italic">Isi data dengan benar sebelum mengajukan verifikasi</p>
        </div>

        <form id="webinarForm" method="POST" enctype="multipart/form-data" class="space-y-8">
            <?php if ($is_edit): ?>
                <input type="hidden" name="id" value="<?= $webinar_data['id_webinar']; ?>">
            <?php endif; ?>

            <div class="bg-white rounded-[32px] p-8 shadow-sm border border-slate-100 relative overflow-hidden">
                <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-3">
                    <span class="w-8 h-8 bg-teal-600 text-white rounded-lg flex items-center justify-center text-sm font-black">1</span>
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
                        <select name="kategori" required class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all cursor-pointer">
                            <?php 
                            $cats = ['Teknologi', 'Bisnis', 'Pendidikan', 'Kesehatan', 'Lingkungan'];
                            foreach($cats as $cat): ?>
                                <option value="<?= $cat ?>" <?= ($is_edit && $webinar_data['kategori'] == $cat) ? 'selected' : ''; ?>><?= $cat ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Target Jurusan / Prodi *</label>
                        <select name="target_jurusan" required class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all cursor-pointer">
                            <?php 
                            $jurusans = ['Semua', 'Teknik Informatika', 'Sistem Informasi', 'Desain Komunikasi Visual', 'Ilmu Komunikasi', 'Akuntansi', 'Manajemen'];
                            foreach($jurusans as $j): ?>
                                <option value="<?= $j ?>" <?= (($is_edit && isset($webinar_data['target_jurusan']) && $webinar_data['target_jurusan'] == $j) || (!$is_edit && $j == 'Semua')) ? 'selected' : ''; ?>><?= $j ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Platform</label>
                        <select name="platform" class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all cursor-pointer">
                            <?php 
                            $platforms = ['Zoom', 'Google Meet', 'Microsoft Teams', 'YouTube Live'];
                            foreach($platforms as $plat): ?>
                                <option value="<?= $plat ?>" <?= ($is_edit && $webinar_data['platform'] == $plat) ? 'selected' : ''; ?>><?= $plat ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label class="text-sm font-bold text-slate-700 ml-1">Link Grup WhatsApp *</label>
                        <input type="url" name="link_group" required placeholder="https://chat.whatsapp.com/..." 
                               value="<?= $is_edit ? htmlspecialchars($webinar_data['link_group'] ?? '') : ''; ?>"
                               class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all text-sm">
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
                    <span class="w-8 h-8 bg-teal-600 text-white rounded-lg flex items-center justify-center text-sm font-black">2</span>
                    Waktu & Kapasitas
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Hari Pelaksanaan *</label>
                        <input type="date" name="tanggal" required value="<?= $is_edit ? $webinar_data['tanggal'] : ''; ?>" class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Jam Mulai *</label>
                        <input type="time" name="waktu_mulai" required value="<?= $is_edit ? $webinar_data['waktu_mulai'] : ''; ?>" class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Jam Selesai *</label>
                        <input type="time" name="waktu_selesai" required value="<?= $is_edit ? $webinar_data['waktu_selesai'] : ''; ?>" class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">SKKM (Poin) *</label>
                        <input type="number" name="poin_skkm" required min="0" value="<?= $is_edit ? $webinar_data['poin_skkm'] : ''; ?>" class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Kapasitas (User) *</label>
                        <input type="number" name="kuota_peserta" required min="1" value="<?= $is_edit ? $webinar_data['kuota_peserta'] : ''; ?>" class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-white rounded-[32px] p-8 shadow-sm border border-slate-100">
                    <h3 class="text-xl font-bold text-slate-800 mb-6">Timeline Pendaftaran</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Start Registration</label>
                            <input type="datetime-local" name="tanggal_mulai_pendaftaran" required value="<?= ($is_edit && $webinar_data['tanggal_mulai_pendaftaran']) ? date('Y-m-d\TH:i', strtotime($webinar_data['tanggal_mulai_pendaftaran'])) : ''; ?>" class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">End Registration</label>
                            <input type="datetime-local" name="tanggal_akhir_pendaftaran" required value="<?= ($is_edit && $webinar_data['tanggal_akhir_pendaftaran']) ? date('Y-m-d\TH:i', strtotime($webinar_data['tanggal_akhir_pendaftaran'])) : ''; ?>" class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none">
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-[32px] p-8 shadow-sm border border-slate-100">
                    <h3 class="text-xl font-bold text-slate-800 mb-6">Metode Pembayaran</h3>
                    <div class="space-y-6">
                        <div class="flex items-center gap-6 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-600">
                                <input type="radio" name="tipe_webinar" value="gratis" class="w-4 h-4 text-teal-600 focus:ring-teal-500" <?php echo (!$is_edit || $webinar_data['tipe_webinar'] == 'gratis') ? 'checked' : ''; ?>> Gratis
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-600">
                                <input type="radio" name="tipe_webinar" value="berbayar" class="w-4 h-4 text-teal-600 focus:ring-teal-500" <?php echo ($is_edit && $webinar_data['tipe_webinar'] == 'berbayar') ? 'checked' : ''; ?>> Berbayar
                            </label>
                        </div>

                        <div id="biaya_field" class="<?= ($is_edit && $webinar_data['tipe_webinar'] == 'berbayar') ? '' : 'hidden'; ?> space-y-4 mt-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Harga Tiket (Rp)</label>
                                <input type="number" id="biaya_input" name="biaya" min="0" value="<?= $is_edit ? $webinar_data['biaya'] : ''; ?>" class="w-full px-5 py-3 bg-white border-2 border-teal-500 rounded-2xl outline-none">
                            </div>
                            <div class="p-4 bg-teal-50 rounded-2xl border border-dashed border-teal-200">
                                <label class="block text-sm font-bold text-teal-800 mb-2">Upload QR Code Pembayaran</label>
                                <input type="file" name="qr_code" id="qr_input" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:bg-teal-600 file:text-white hover:file:bg-teal-700">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-8">
                <button type="button" onclick="openConfirmModal()" class="px-12 py-4 bg-teal-600 text-white rounded-2xl font-black text-lg hover:bg-teal-700 transition-all shadow-xl shadow-teal-900/10 active:scale-95">
                    <?= $is_edit ? 'Simpan Perubahan' : 'Ajukan Webinar'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<div id="confirmModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-[32px] max-w-md w-full p-8 shadow-2xl border border-slate-100 text-center transform scale-95 transition-transform duration-300">
        <div class="w-16 h-16 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
            <i class="fas fa-question-circle"></i>
        </div>
        <h3 class="text-xl font-black text-slate-800 mb-2">Konfirmasi Pengajuan</h3>
        <p class="text-slate-500 text-sm leading-relaxed mb-6"> Apakah Anda yakin semua informasi materi, waktu, serta nominal tiket webinar ini sudah benar dan siap diajukan ke Admin? </p>
        <div class="flex gap-4 justify-center">
            <button type="button" onclick="closeConfirmModal()" class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl transition-all">
                Periksa Kembali
            </button>
            <button type="button" onclick="submitFormAsli()" class="flex-1 py-3 bg-teal-600 hover:bg-teal-700 text-white font-black rounded-xl transition-all shadow-lg shadow-teal-600/20">
                Ya, Sudah Benar
            </button>
        </div>
    </div>
</div>

<?php if ($show_success_modal): ?>
<div id="successModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-[32px] max-w-md w-full p-8 shadow-2xl border border-slate-100 text-center">
        <div class="w-16 h-16 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-4 animate-bounce">
            <i class="fas fa-check-circle"></i>
        </div>
        <h3 class="text-xl font-black text-slate-800 mb-2">Berhasil Tersimpan!</h3>
        <p class="text-slate-500 text-sm leading-relaxed mb-6">
            <?= htmlspecialchars($success_message); ?>
        </p>
        <button type="button" onclick="tetapDiSini()" class="w-full py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl transition-all">
            Selesai & Tetap Di Sini
        </button>
    </div>
</div>
<?php endif; ?>

<script>
// Menampilkan Modal Konfirmasi
function openConfirmModal() {
    // Jalankan validasi HTML5 bawaan form terlebih dahulu
    const form = document.getElementById('webinarForm');
    if(!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    // Validasi aturan bisnis tanggal
    const startReg = new Date(document.querySelector('input[name="tanggal_mulai_pendaftaran"]').value);
    const endReg = new Date(document.querySelector('input[name="tanggal_akhir_pendaftaran"]').value);
    const eventDate = new Date(document.querySelector('input[name="tanggal"]').value);
    
    if (endReg <= startReg) {
        alert('❌ Error: Pendaftaran tidak bisa ditutup sebelum atau pada saat dibuka!');
        return;
    } 
    if (eventDate < endReg) {
        alert('❌ Error: Hari pelaksanaan webinar tidak boleh mendahului penutupan pendaftaran!');
        return;
    }

    document.getElementById('confirmModal').classList.remove('hidden');
}

// Menutup Modal Konfirmasi
function closeConfirmModal() {
    document.getElementById('confirmModal').classList.add('hidden');
}

// Trigger submit asli setelah user menekan tombol yakin
function submitFormAsli() {
    document.getElementById('webinarForm').submit();
}

// Aksi ketika klik selesai di modal sukses
function tetapDiSini() {
    // Me-refresh halaman agar form kembali bersih / memuat data ter-update tanpa me-redirect ke halaman admin
    window.location.href = window.location.pathname;
}

// Fungsi toggle field biaya pembayaran
function toggleBiaya() {
    const tipe = document.querySelector('input[name="tipe_webinar"]:checked').value;
    const biayaField = document.getElementById('biaya_field');
    const biayaInput = document.getElementById('biaya_input');
    const qrInput = document.getElementById('qr_input');

    if (tipe === 'berbayar') {
        biayaField.classList.remove('hidden');
        biayaInput.setAttribute('required', 'required');
        <?php if(!$is_edit): ?>
            if (qrInput) qrInput.setAttribute('required', 'required');
        <?php endif; ?>
    } else {
        biayaField.classList.add('hidden');
        biayaInput.removeAttribute('required');
        if (qrInput) qrInput.removeAttribute('required');
        biayaInput.value = 0; 
    }
}

document.querySelectorAll('input[name="tipe_webinar"]').forEach(radio => {
    radio.addEventListener('change', toggleBiaya);
});
document.addEventListener('DOMContentLoaded', toggleBiaya);
</script>

<?php require_once 'includes/footer.php'; ?>