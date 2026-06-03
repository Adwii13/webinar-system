<?php
require_once 'config/database.php'; // Hanya butuh 1 koneksi ke webinar_db
session_start();

// Jika sudah login, arahkan ke dashboard masing-masing
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') header("Location: admin/dashboard.php");
    elseif ($_SESSION['role'] === 'penyelenggara') header("Location: penyelenggara/dashboard.php");
    else header("Location: student/index.php");
    exit();
}

$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        
        // 1. CEK ROLE: ADMIN
        $q_admin = mysqli_query($conn, "SELECT * FROM admin WHERE username = '$username'");
        if (mysqli_num_rows($q_admin) > 0) {
            $admin = mysqli_fetch_assoc($q_admin);
            // Validasi password ketat menggunakan password_verify
            if (password_verify($password, $admin['password'])) {
                $_SESSION['id_user'] = $admin['id_admin'];
                $_SESSION['username'] = $admin['username'];
                $_SESSION['nama'] = $admin['nama_admin'];
                $_SESSION['role'] = 'admin';
                header("Location: admin/dashboard.php");
                exit();
            }
        }

        // 2. CEK ROLE: PENYELENGGARA
        $q_penyelenggara = mysqli_query($conn, "SELECT * FROM penyelenggara WHERE username = '$username'");
        if (mysqli_num_rows($q_penyelenggara) > 0) {
            $penyelenggara = mysqli_fetch_assoc($q_penyelenggara);
            if (password_verify($password, $penyelenggara['password'])) {
                $_SESSION['id_user'] = $penyelenggara['id_penyelenggara'];
                $_SESSION['username'] = $penyelenggara['username'];
                $_SESSION['nama'] = $penyelenggara['nama_penyelenggara'];
                $_SESSION['role'] = 'penyelenggara';
                header("Location: penyelenggara/dashboard.php");
                exit();
            }
        }

        // 3. CEK ROLE: MAHASISWA (Username = NPP)
        $q_mahasiswa = mysqli_query($conn, "SELECT * FROM mahasiswa WHERE npp = '$username'");
        if (mysqli_num_rows($q_mahasiswa) > 0) {
            $mahasiswa = mysqli_fetch_assoc($q_mahasiswa);
            
            // Di sini kuncinya! Jika password salah atau NULL (kosong), tidak akan bisa lolos.
            if (!empty($mahasiswa['password']) && password_verify($password, $mahasiswa['password'])) {
                $_SESSION['id_user'] = $mahasiswa['npp'];
                $_SESSION['username'] = $mahasiswa['npp'];
                $_SESSION['nama'] = $mahasiswa['nama_mahasiswa'];
                $_SESSION['jurusan'] = $mahasiswa['jurusan']; // Diperlukan untuk filter prodi kelak
                $_SESSION['role'] = 'mahasiswa';
                header("Location: peserta/dashboard.php");
                exit();
            }
        }

        // Jika tidak ada data yang cocok atau password salah setelah melewati 3 cek di atas
        $error_msg = "Username / NPP atau Password Anda salah!";
    } else {
        $error_msg = "Harap isi semua kolom!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Webinar System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-white rounded-[32px] p-8 shadow-xl border border-slate-100">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-black text-slate-800">Webinar Login</h2>
            <p class="text-slate-500 text-sm mt-1">Satu gerbang untuk Admin, Penyelenggara, dan Mahasiswa</p>
        </div>

        <form method="POST" action="" class="space-y-5">
            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Username / NPP</label>
                <input type="text" name="username" required placeholder="Masukkan username atau npp..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:border-teal-500 font-medium">
            </div>

            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Password</label>
                <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:border-teal-500 font-medium">
            </div>

            <button type="submit" class="w-full py-4 bg-teal-600 text-white rounded-2xl font-bold hover:bg-teal-700 transition-all shadow-lg">
                Masuk Sistem
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if (!empty($error_msg)): ?>
    <script>
        Swal.fire({ icon: 'error', title: 'Akses Ditolak', text: '<?= $error_msg ?>', confirmButtonColor: '#0d9488' });
    </script>
    <?php endif; ?>
</body>
</html>