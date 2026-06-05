<?php
require_once 'includes/guard-mahasiswa.php';
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proteksi halaman student
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../index.php');
    exit();
}

$session_npp = $_SESSION['npp'] ?? '';

// Query mengambil riwayat pendaftaran mahasiswa ini
$query = "SELECT p.*, w.judul, w.tanggal, w.waktu_mulai, w.tipe_webinar
          FROM pemantauan_webinar p
          JOIN webinar w ON p.id_webinar = w.id_webinar
          WHERE p.npp = '$session_npp'
          ORDER BY p.id_pendaftaran DESC";
$result = mysqli_query($conn, $query);

require_once 'includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-black text-slate-800 uppercase italic">Riwayat Pendaftaran</h1>
        <p class="text-slate-500">Pantau status verifikasi pendaftaran webinar Anda di sini.</p>
    </div>

    <div class="bg-white rounded-[2rem] border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900 text-white uppercase text-xs tracking-wider font-black">
                        <th class="px-6 py-4">Webinar</th>
                        <th class="px-6 py-4">Tanggal Pelaksanaan</th>
                        <th class="px-6 py-4">Tipe</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm font-medium text-slate-600">
                    <?php if (mysqli_num_rows($result) == 0): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-slate-400 italic">
                                Anda belum pernah mendaftar webinar apa pun.
                            </td>
                        </tr>
                    <?php endif; ?>
                    
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-800">
                                <?= htmlspecialchars($row['judul']) ?>
                            </td>
                            <td class="px-6 py-4">
                                <?= date('d M Y', strtotime($row['tanggal'])) ?> - <?= date('H:i', strtotime($row['waktu_mulai'])) ?> WIB
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-black uppercase <?= $row['tipe_webinar'] == 'gratis' ? 'bg-teal-50 text-teal-600' : 'bg-rose-50 text-rose-600' ?>">
                                    <?= $row['tipe_webinar'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <?php if($row['status_pendaftaran'] == 'menunggu'): ?>
                                    <span class="px-3 py-1 bg-amber-50 text-amber-600 rounded-full text-xs font-black uppercase border border-amber-100">
                                        <i class="fas fa-clock mr-1"></i> Menunggu
                                    </span>
                                <?php elseif($row['status_pendaftaran'] == 'disetujui'): ?>
                                    <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-xs font-black uppercase border border-emerald-100">
                                        <i class="fas fa-check-circle mr-1"></i> Disetujui
                                    </span>
                                <?php else: ?>
                                    <span class="px-3 py-1 bg-rose-50 text-rose-600 rounded-full text-xs font-black uppercase border border-rose-100">
                                        <i class="fas fa-times-circle mr-1"></i> Ditolak
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="ringkasan.php?id_pendaftaran=<?= $row['id_pendaftaran'] ?>" 
                                       class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold transition-all text-xs flex items-center gap-1">
                                        <i class="fas fa-file-alt"></i> Detail
                                    </a>

                                    <?php if($row['status_pendaftaran'] == 'menunggu'): ?>
                                        <a href="detail-webinar.php?id=<?= $row['id_webinar'] ?>&edit_id=<?= $row['id_pendaftaran'] ?>" 
                                           class="px-4 py-2 bg-teal-500 hover:bg-teal-600 text-white rounded-xl font-bold transition-all text-xs flex items-center gap-1 shadow-sm shadow-teal-500/10">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>