<?php
require_once 'includes/admin-guard.php';
require_once '../config/database.php';
require_once 'includes/header.php';

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

// Ambil peserta yang sudah mendaftar dan disetujui
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

// Antisipasi Error Division by Zero jika kuota_peserta bernilai 0 atau kosong
$kuota = intval($webinar['kuota_peserta']);
$persentase = ($kuota > 0) ? ($total_peserta / $kuota) * 100 : 0;
?>

<div class="p-4 md:p-8 bg-slate-50 min-h-screen">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h2 class="text-3xl font-black text-slate-800 tracking-tight">Detail Webinar</h2>
            <p class="text-slate-500 font-medium text-sm">Informasi lengkap, pelacakan peserta, dan linimasa pelaksanaan event.</p>
        </div>
        <div class="flex gap-3 w-full md:w-auto">
            <a href="kelola-webinar.php" class="flex-1 md:flex-initial px-5 py-2.5 bg-white text-slate-600 rounded-xl font-bold border border-slate-200 hover:bg-slate-50 transition-all flex items-center justify-center gap-2 shadow-sm text-sm">
                <i class="fas fa-arrow-left text-xs"></i> Kembali
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white rounded-[32px] shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-6 md:p-8">
                    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
                        <span class="px-4 py-1.5 bg-teal-50 text-teal-700 rounded-full text-xs font-black uppercase tracking-wider border border-teal-100">
                            <?= htmlspecialchars($webinar['kategori']); ?>
                        </span>
                        
                        <div>
                            <?php 
                                $v_status = $webinar['status_verifikasi'];
                                $v_map = [
                                    'disetujui' => 'bg-emerald-50 text-emerald-700 border border-emerald-200/60',
                                    'menunggu'  => 'bg-amber-50 text-amber-700 border border-amber-200/60',
                                    'ditolak'   => 'bg-rose-50 text-rose-700 border border-rose-200/60'
                                ];
                                $badge_class = $v_map[$v_status] ?? 'bg-slate-100 text-slate-600';
                            ?>
                            <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider <?= $badge_class ?>">
                                Verifikasi: <?= htmlspecialchars($v_status) ?>
                            </span>
                        </div>
                    </div>

                    <h1 class="text-2xl font-bold text-slate-800 mb-6 leading-snug"><?= htmlspecialchars($webinar['judul']); ?></h1>
                    
                    <div class="mb-10">
                        <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Deskripsi Event</h4>
                        <div class="text-slate-600 leading-relaxed bg-slate-50/70 p-6 rounded-2xl border border-slate-100 text-sm whitespace-pre-line">
                            <?= htmlspecialchars($webinar['deskripsi']); ?>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t border-slate-100">
                        <div class="space-y-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-slate-50 rounded-xl flex items-center justify-center text-slate-500 border border-slate-100 shrink-0">
                                    <i class="fas fa-calendar-day text-base"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tanggal Pelaksanaan</p>
                                    <p class="text-slate-800 font-bold text-sm"><?= date('l, d F Y', strtotime($webinar['tanggal'])); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-slate-50 rounded-xl flex items-center justify-center text-slate-500 border border-slate-100 shrink-0">
                                    <i class="fas fa-clock text-base"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Waktu Operasional</p>
                                    <p class="text-slate-800 font-bold text-sm"><?= date('H:i', strtotime($webinar['waktu_mulai'])); ?> - <?= date('H:i', strtotime($webinar['waktu_selesai'])); ?> WIB</p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-slate-50 rounded-xl flex items-center justify-center text-slate-500 border border-slate-100 shrink-0">
                                    <i class="fas fa-user-tie text-base"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pembicara Utama</p>
                                    <p class="text-slate-800 font-bold text-sm"><?= htmlspecialchars($webinar['pembicara']); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-slate-50 rounded-xl flex items-center justify-center text-slate-500 border border-slate-100 shrink-0">
                                    <i class="fas fa-desktop text-base"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Platform Media</p>
                                    <p class="text-slate-800 font-bold text-sm"><?= htmlspecialchars($webinar['platform']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 p-4 bg-emerald-50/60 border border-emerald-100 rounded-2xl">
                        <label class="block text-[10px] font-black text-emerald-700 uppercase tracking-wider mb-1">Tautan Grup WhatsApp Peserta</label>
                        <?php if (!empty($webinar['link_group'])): ?>
                            <div class="flex items-center gap-2 min-w-0">
                                <i class="fab fa-whatsapp text-emerald-600 text-lg shrink-0"></i>
                                <a href="<?= htmlspecialchars($webinar['link_group']) ?>" target="_blank" class="text-sm font-semibold text-slate-700 hover:text-emerald-700 underline truncate break-all">
                                    <?= htmlspecialchars($webinar['link_group']) ?>
                                </a>
                            </div>
                        <?php else: ?>
                            <p class="text-xs text-slate-400 italic flex items-center gap-1.5 mt-1">
                                <i class="fas fa-info-circle text-slate-300"></i> Link grup komunikasi belum ditambahkan oleh pihak penyelenggara.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[32px] shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-6 md:p-8 border-b border-slate-100">
                    <h3 class="text-xl font-bold text-slate-800">Daftar Peserta Terverifikasi</h3>
                </div>
                <div class="overflow-x-auto">
                    <?php if($total_peserta > 0): ?>
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/60 border-b border-slate-100">
                                <th class="p-6 text-[11px] font-black text-slate-400 uppercase tracking-widest">Identitas Mahasiswa</th>
                                <th class="p-6 text-[11px] font-black text-slate-400 uppercase tracking-widest">Fakultas / Unit</th>
                                <th class="p-6 text-[11px] font-black text-slate-400 uppercase tracking-widest text-center">Tanggal Registrasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php while($peserta = mysqli_fetch_assoc($result_peserta)): ?>
                            <tr class="hover:bg-slate-50/40 transition-colors">
                                <td class="p-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 bg-teal-50 text-teal-700 rounded-xl flex items-center justify-center font-bold text-xs border border-teal-100/50 uppercase shrink-0">
                                            <?= substr(htmlspecialchars($peserta['nama_mahasiswa']), 0, 1); ?>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-800 leading-tight"><?= htmlspecialchars($peserta['nama_mahasiswa']); ?></p>
                                            <p class="text-[11px] text-slate-400 font-medium mt-0.5">NPP/NIM: <?= htmlspecialchars($peserta['npp']); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-6 text-sm text-slate-600 font-semibold"><?= htmlspecialchars($peserta['fakultas']); ?></td>
                                <td class="p-6 text-sm text-slate-500 text-center font-medium whitespace-nowrap"><?= date('d M Y', strtotime($peserta['tanggal_daftar'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="p-16 text-center">
                        <div class="w-16 h-16 bg-slate-50 border border-slate-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-users-slash text-xl text-slate-300"></i>
                        </div>
                        <p class="text-sm text-slate-400 font-medium italic">Belum ada peserta yang berstatus disetujui untuk event webinar ini.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="space-y-8">
            <div class="bg-white rounded-[32px] p-6 md:p-8 shadow-sm border border-slate-100">
                <h3 class="text-lg font-bold text-slate-800 mb-6">Kapasitas Pendaftaran</h3>
                
                <div class="relative pt-1">
                    <div class="flex mb-3 items-center justify-between">
                        <div>
                            <span class="text-[10px] font-black inline-block py-1 px-2.5 uppercase rounded-md text-teal-700 bg-teal-50 border border-teal-200/50">
                                <?= round($persentase, 1); ?>% Terisi
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-bold text-slate-500">
                                <b class="text-slate-800"><?= $total_peserta; ?></b> <span class="text-slate-300">/</span> <?= $webinar['kuota_peserta']; ?> Seats
                            </span>
                        </div>
                    </div>
                    <div class="overflow-hidden h-2.5 mb-4 text-xs flex rounded-full bg-slate-100">
                        <div style="width:<?= min(100, $persentase); ?>%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-teal-500 transition-all duration-700 rounded-full"></div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-6">
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-center">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1">Poin SKKM</p>
                        <p class="text-2xl font-black text-teal-600"><?= intval($webinar['poin_skkm']); ?></p>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-center">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1">Status Web</p>
                        <?php
                            $s_map = [
                                'publish' => 'text-emerald-600',
                                'draft'   => 'text-slate-500',
                                'closed'  => 'text-rose-600'
                            ];
                            $s_color = $s_map[$webinar['status']] ?? 'text-slate-700';
                        ?>
                        <p class="text-sm font-black uppercase mt-1.5 <?= $s_color ?>"><?= htmlspecialchars($webinar['status']); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[32px] p-6 md:p-8 shadow-sm border border-slate-100">
                <h3 class="text-lg font-bold text-slate-800 mb-6">Timeline Registrasi</h3>
                <div class="space-y-6 relative border-l-2 border-slate-100 pl-4 ml-2">
                    <div class="relative">
                        <div class="absolute -left-[21px] top-1 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-4 ring-emerald-100"></div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Gerbang Dibuka</p>
                            <p class="text-xs font-bold text-slate-700 mt-0.5">
                                <?= (!empty($webinar['tanggal_mulai_pendaftaran'])) ? date('d M Y, H:i', strtotime($webinar['tanggal_mulai_pendaftaran'])) . ' WIB' : 'Belum ditentukan'; ?>
                            </p>
                        </div>
                    </div>
                    <div class="relative">
                        <div class="absolute -left-[21px] top-1 w-2.5 h-2.5 rounded-full bg-rose-500 ring-4 ring-rose-100"></div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Gerbang Ditutup</p>
                            <p class="text-xs font-bold text-slate-700 mt-0.5">
                                <?= (!empty($webinar['tanggal_akhir_pendaftaran'])) ? date('d M Y, H:i', strtotime($webinar['tanggal_akhir_pendaftaran'])) . ' WIB' : 'Belum ditentukan'; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-slate-100">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 text-center">Metode & Biaya Masuk</p>
                    <div class="flex items-center justify-between p-4 bg-teal-50/60 border border-teal-100/60 rounded-2xl">
                        <span class="text-xs font-bold text-teal-800 uppercase tracking-wider"><?= htmlspecialchars($webinar['tipe_webinar']); ?></span>
                        <span class="text-sm font-black text-teal-800">
                            <?= ($webinar['tipe_webinar'] == 'berbayar') ? 'Rp ' . number_format($webinar['biaya'], 0, ',', '.') : 'GRATIS'; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>