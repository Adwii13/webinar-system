<?php
// 1. PROTEKSI SESSION & DATABASE (Harus di baris paling atas)
require_once 'includes/admin-guard.php'; 
require_once '../config/database.php';

// Buat variabel penampung pesan feedback agar tidak terjadi error "undefined variable"
$message = '';
$message_type = ''; // bisa diisi 'success' atau 'error'

// 2. LOGIKA PEMROSESAN (Hanya berjalan jika form disubmit via POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil dan bersihkan data inputan (Kini menggunakan $_POST['no_wa'])
    $nama_penyelenggara = trim($_POST['nama_penyelenggara']);
    $username           = strtolower(trim($_POST['username']));
    $no_wa              = trim($_POST['no_wa']); 
    $password           = $_POST['password'];

    // Validasi input kosong
    if (empty($nama_penyelenggara) || empty($username) || empty($no_wa) || empty($password)) {
        $message = "Semua bidang formulir wajib diisi!";
        $message_type = "error";
    } else {
        // Cek Duplikasi Username atau Nomor WA di Database (Kolom diubah menjadi no_wa)
        $stmt_check = mysqli_prepare($conn, "SELECT id_penyelenggara FROM penyelenggara WHERE username = ? OR no_wa = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt_check, 'ss', $username, $no_wa);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);
        
        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $message = "Username atau Nomor WhatsApp sudah terdaftar di sistem!";
            $message_type = "error";
            mysqli_stmt_close($stmt_check);
        } else {
            mysqli_stmt_close($stmt_check);

            // Amankan password dengan Bcrypt Hashing
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert Data Baru (Kolom menggunakan no_wa)
            $query_insert = "INSERT INTO penyelenggara (nama_penyelenggara, username, no_wa, password) VALUES (?, ?, ?, ?)";
            $stmt_insert = mysqli_prepare($conn, $query_insert);
            mysqli_stmt_bind_param($stmt_insert, 'ssss', $nama_penyelenggara, $username, $no_wa, $hashed_password);

            if (mysqli_stmt_execute($stmt_insert)) {
                $message = "Akun penyelenggara '$nama_penyelenggara' berhasil dibuat!";
                $message_type = "success";
            } else {
                $message = "Terjadi kegagalan sistem saat menyimpan data: " . mysqli_error($conn);
                $message_type = "error";
            }
            mysqli_stmt_close($stmt_insert);
        }
    }
}

// 3. LOAD HEADER TAMPILAN
require_once 'includes/header.php';
?>

<div class="p-4 md:p-8 bg-slate-50 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-[32px] shadow-sm border border-slate-100 p-8 w-full max-w-2xl">
        
        <div class="flex items-center gap-4 mb-8 border-b border-slate-100 pb-6">
            <div class="p-3 bg-teal-50 rounded-2xl text-teal-600">
                <i class="fas fa-user-plus text-xl"></i>
            </div>
            <div>
                <h3 class="text-2xl font-bold text-slate-800">Tambah Akun Penyelenggara</h3>
                <p class="text-sm text-slate-400">Daftarkan akun baru untuk organisasi atau panitia penyelenggara webinar.</p>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <?php if ($message_type === 'error'): ?>
                <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 text-sm font-semibold rounded-2xl flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-lg"></i>
                    <p><?= htmlspecialchars($message); ?></p>
                </div>
            <?php elseif ($message_type === 'success'): ?>
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold rounded-2xl flex items-center gap-3">
                    <i class="fas fa-check-circle text-lg"></i>
                    <p><?= htmlspecialchars($message); ?></p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-6">
            
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide">Nama Penyelenggara / Instansi</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="far fa-building"></i>
                    </span>
                    <input type="text" name="nama_penyelenggara" required 
                           value="<?= isset($_POST['nama_penyelenggara']) && $message_type === 'error' ? htmlspecialchars($_POST['nama_penyelenggara']) : ''; ?>"
                           placeholder="Contoh: BEM FIK, HMTI, atau Nama Personal" 
                           class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-teal-500 focus:bg-white text-sm text-slate-800 font-medium transition-all">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide">Username</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="far fa-user"></i>
                    </span>
                    <input type="text" name="username" required 
                           value="<?= isset($_POST['username']) && $message_type === 'error' ? htmlspecialchars($_POST['username']) : ''; ?>"
                           placeholder="username_penyelenggara" 
                           class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-teal-500 focus:bg-white text-sm text-slate-800 font-medium transition-all">
                </div>
                <p class="text-[11px] text-slate-400 mt-1 italic">*Gunakan huruf kecil tanpa spasi.</p>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide">Nomor WhatsApp</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fab fa-whatsapp text-lg"></i>
                    </span>
                    <input type="tel" name="no_wa" required 
                           value="<?= isset($_POST['no_wa']) && $message_type === 'error' ? htmlspecialchars($_POST['no_wa']) : ''; ?>"
                           placeholder="Contoh: 081234567890" 
                           class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-teal-500 focus:bg-white text-sm text-slate-800 font-medium transition-all">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide">Password Akun</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" id="passwordField" name="password" required placeholder="••••••••" 
                           class="w-full pl-11 pr-12 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-teal-500 focus:bg-white text-sm text-slate-800 font-medium transition-all">
                    <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-slate-600">
                        <i id="passwordIcon" class="far fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="flex gap-4 border-t border-slate-50 pt-6 mt-8">
                <a href="dashboard.php" class="flex-1 py-3 border border-slate-200 text-center text-slate-500 font-bold rounded-xl hover:bg-slate-50 transition-all text-sm">
                    Kembali Ke Dashboard
                </a>
                <button type="submit" class="flex-1 py-3 bg-teal-600 text-white font-bold rounded-xl hover:bg-teal-700 shadow-lg shadow-teal-900/10 transition-all text-sm">
                    Simpan Akun
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function togglePasswordVisibility() {
    const passwordField = document.getElementById('passwordField');
    const passwordIcon = document.getElementById('passwordIcon');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        passwordIcon.classList.remove('fa-eye');
        passwordIcon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        passwordIcon.classList.remove('fa-eye-slash');
        passwordIcon.classList.add('fa-eye');
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>