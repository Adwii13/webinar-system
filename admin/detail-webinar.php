<?php
require_once '../config/database.php';
require_once '../includes/header.php';

if (!isset($_GET['id'])) {
    header('Location: kelola-webinar.php');
    exit();
}

$id = intval($_GET['id']);
$query = "SELECT w.*, p.nama_penyelenggara, p.instansi, p.no_wa 
          FROM webinar w 
          LEFT JOIN penyelenggara p ON w.id_penyelenggara = p.id_penyelenggara 
          WHERE w.id_webinar = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$webinar = mysqli_fetch_assoc($result);

if (!$webinar) {
    header('Location: kelola-webinar.php');
    exit();
}

// Ambil peserta yang sudah mendaftar (Perbaikan Join ke tabel mahasiswa agar data nama muncul)
$query_peserta = "SELECT p.*, m.nama_mahasiswa, m.fakultas 
                  FROM pemantauan_webinar p
                  JOIN mahasiswa m ON p.npp = m.npp
                  WHERE p.id_webinar = ? 
                  AND p.status_pendaftaran = 'disetujui' 
                  ORDER BY p.tanggal_daftar DESC";
$stmt_peserta = mysqli_prepare($conn, $query_peserta);
mysqli_stmt_bind_param($stmt_peserta, 'i', $id);
mysqli_stmt_execute($stmt_peserta);
$result_peserta = mysqli_stmt_get_result($stmt_peserta);

$total_peserta = mysqli_num_rows($result_peserta);
$persentase = ($total_peserta / $webinar['kuota_peserta']) * 100;
?>

<div class="p-8 bg-slate-50 min-h-screen">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h2 class="text-3xl font-black text-slate-800 tracking-tight">Detail Webinar</h2>
            <p class="text-slate-500 font-medium">Informasi lengkap mengenai pelaksanaan webinar</p>
        </div>
        <div class="flex gap-3">
            <a href="kelola-webinar.php" class="px-5 py-2.5 bg-white text-slate-600 rounded-xl font-bold border border-slate-200 hover:bg-slate-50 transition-all flex items-center gap-2 shadow-sm">
                <i class="fas fa-arrow-left text-xs"></i> Kembali
            </a>
            <a href="tambah-webinar.php?edit=<?= $id; ?>" class="px-5 py-2.5 bg-teal-600 text-white rounded-xl font-bold hover:bg-teal-700 transition-all flex items-center gap-2 shadow-lg shadow-teal-900/20">
                <i class="fas fa-edit text-xs"></i> Edit Data
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white rounded-[32px] shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-8">
                    <div class="flex justify-between items-start mb-6">
                        <span class="px-4 py-1.5 bg-teal-50 text-teal-600 rounded-full text-xs font-black uppercase tracking-wider">
                            <?= htmlspecialchars($webinar['kategori']); ?>
                        </span>
                        <div class="flex gap-2">
                            <?php 
                                $v_status = $webinar['status_verifikasi'];
                                $v_color = ($v_status == 'disetujui') ? 'emerald' : (($v_status == 'menunggu') ? 'amber' : 'red');
                            ?>
                            <span class="px-3 py-1 bg-<?= $v_color ?>-100 text-<?= $v_color ?>-700 rounded-lg text-[10px] font-bold uppercase">
                                Verifikasi: <?= $v_status ?>
                            </span>
                        </div>
                    </div>

                    <h1 class="text-2xl font-bold text-slate-800 mb-4"><?= htmlspecialchars($webinar['judul']); ?></h1>
                    
                    <div class="prose prose-slate max-w-none">
                        <h4 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-2">Deskripsi</h4>
                        <p class="text-slate-600 leading-relaxed bg-slate-50 p-6 rounded-2xl border border-slate-100 italic">
                            "<?= nl2br(htmlspecialchars($webinar['deskripsi'])); ?>"
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-10">
                        <div class="space-y-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center text-slate-500">
                                    <i class="fas fa-calendar-day text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Tanggal Pelaksanaan</p>
                                    <p class="text-slate-800 font-bold"><?= date('l, d F Y', strtotime($webinar['tanggal'])); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center text-slate-500">
                                    <i class="fas fa-clock text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Waktu Operasional</p>
                                    <p class="text-slate-800 font-bold"><?= date('H:i', strtotime($webinar['waktu_mulai'])); ?> - <?= date('H:i', strtotime($webinar['waktu_selesai'])); ?> WIB</p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center text-slate-500">
                                    <i class="fas fa-user-tie text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Pembicara Utama</p>
                                    <p class="text-slate-800 font-bold"><?= htmlspecialchars($webinar['pembicara']); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center text-slate-500">
                                    <i class="fas fa-desktop text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Platform Media</p>
                                    <p class="text-slate-800 font-bold"><?= htmlspecialchars($webinar['platform']); ?></p>
                                </div>
                            </div>

                            <div class="mt-4 p-4 bg-emerald-50 border border-emerald-100 rounded-2xl">
                                <label class="block text-xs font-bold text-emerald-600 uppercase mb-1">WhatsApp Group Link</label>
                                <?php if (!empty($webinar['link_group'])): ?>
                                    <div class="flex items-center gap-2">
                                        <i class="fab fa-whatsapp text-emerald-500 text-lg"></i>
                                        <a href="<?= $webinar['link_group'] ?>" target="_blank" class="text-sm font-semibold text-slate-700 hover:text-emerald-600 underline break-all">
                                            <?= $webinar['link_group'] ?>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <p class="text-sm text-slate-400 italic">Link grup belum ditambahkan</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[32px] shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-8 border-b border-slate-50">
                    <h3 class="text-xl font-bold text-slate-800">Daftar Peserta Terverifikasi</h3>
                </div>
                <div class="overflow-x-auto">
                    <?php if($total_peserta > 0): ?>
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="p-6 text-[11px] font-black text-slate-400 uppercase tracking-widest">Mahasiswa</th>
                                <th class="p-6 text-[11px] font-black text-slate-400 uppercase tracking-widest">Fakultas</th>
                                <th class="p-6 text-[11px] font-black text-slate-400 uppercase tracking-widest text-center">Tgl Daftar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php while($peserta = mysqli_fetch_assoc($result_peserta)): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="p-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-teal-100 text-teal-700 rounded-full flex items-center justify-center font-bold text-xs">
                                            <?= substr($peserta['nama_mahasiswa'], 0, 1); ?>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-800"><?= htmlspecialchars($peserta['nama_mahasiswa']); ?></p>
                                            <p class="text-xs text-slate-400">NIM: <?= htmlspecialchars($peserta['npp']); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-6 text-sm text-slate-600 font-medium"><?= htmlspecialchars($peserta['fakultas']); ?></td>
                                <td class="p-6 text-sm text-slate-500 text-center font-medium"><?= date('d M Y', strtotime($peserta['tanggal_daftar'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="p-16 text-center">
                        <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-users-slash text-2xl text-slate-300"></i>
                        </div>
                        <p class="text-slate-400 font-medium italic">Belum ada peserta yang disetujui untuk webinar ini.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="space-y-8">
            <div class="bg-white rounded-[32px] p-8 shadow-sm border border-slate-100">
                <h3 class="text-lg font-bold text-slate-800 mb-6">Kapasitas Pendaftaran</h3>
                
                <div class="relative pt-1">
                    <div class="flex mb-2 items-center justify-between">
                        <div>
                            <span class="text-xs font-black inline-block py-1 px-2 uppercase rounded-full text-teal-600 bg-teal-100">
                                <?= round($persentase, 1); ?>% Terisi
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-bold inline-block text-slate-400">
                                <?= $total_peserta; ?> / <?= $webinar['kuota_peserta']; ?>
                            </span>
                        </div>
                    </div>
                    <div class="overflow-hidden h-3 mb-4 text-xs flex rounded-full bg-slate-100">
                        <div style="width:<?= min(100, $persentase); ?>%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-teal-500 transition-all duration-1000"></div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-8">
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Poin SKKM</p>
                        <p class="text-xl font-black text-teal-600"><?= $webinar['poin_skkm']; ?></p>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Status Web</p>
                        <p class="text-sm font-black text-slate-700 uppercase"><?= $webinar['status']; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[32px] p-8 shadow-sm border border-slate-100">
                <h3 class="text-lg font-bold text-slate-800 mb-6">Timeline Pendaftaran</h3>
                <div class="space-y-6">
                    <div class="flex gap-4">
                        <div class="mt-1 w-2 h-2 rounded-full bg-emerald-500 ring-4 ring-emerald-100"></div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Dibuka Pada</p>
                            <p class="text-sm font-bold text-slate-700"><?= ($webinar['tanggal_mulai_pendaftaran'] ?? '') ? date('d M Y, H:i', strtotime($webinar['tanggal_mulai_pendaftaran'])) : 'Belum ditentukan'; ?></p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="mt-1 w-2 h-2 rounded-full bg-red-500 ring-4 ring-red-100"></div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Ditutup Pada</p>
                            <p class="text-sm font-bold text-slate-700"><?= ($webinar['tanggal_akhir_pendaftaran'] ?? '') ? date('d M Y, H:i', strtotime($webinar['tanggal_akhir_pendaftaran'])) : 'Belum ditentukan'; ?></p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-8 border-t border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-3 text-center">Tipe & Biaya</p>
                    <div class="flex items-center justify-between p-4 bg-teal-50 rounded-2xl">
                        <span class="font-bold text-teal-700 capitalize"><?= $webinar['tipe_webinar']; ?></span>
                        <span class="font-black text-teal-700">
                            <?= ($webinar['tipe_webinar'] == 'berbayar') ? 'Rp ' . number_format($webinar['biaya'], 0, ',', '.') : 'GRATIS'; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>