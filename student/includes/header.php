<?php
// student/includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNIBI Webinar System - Mahasiswa</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; opacity: 0; transition: opacity 0.3s ease; }
        body.loaded { opacity: 1; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
</head>
<body class="h-full" onload="document.body.classList.add('loaded')">
    <div class="flex min-h-screen">
        
        <aside class="w-64 bg-teal-600 text-white fixed h-full z-50 flex flex-col shadow-xl">
            <div class="p-8">
                <div class="mb-2">
                    <h1 class="text-2xl font-black tracking-tighter text-white">UNIBI</h1>
                </div>
                <p class="text-teal-100 text-[10px] font-bold uppercase tracking-[0.2em] mb-10 opacity-80">Student Portal</p>
                
                <nav class="space-y-1.5">
                    <?php 
                    $current_page = basename($_SERVER['PHP_SELF']);
                    // Menu Khusus Mahasiswa
                    $menus = [
                        ['index.php', 'fas fa-tachometer-alt', 'Dashboard Saya'],
                        ['daftar-webinar.php', 'fas fa-search', 'Cari Webinar'],
                        ['riwayat.php', 'fas fa-history', 'Riwayat Webinar'],
                    ];

                    foreach ($menus as $menu): 
                        $isActive = ($current_page == $menu[0]);
                    ?>
                        <a href="<?= $menu[0] ?>" 
                           class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 font-medium 
                           <?= $isActive 
                               ? 'bg-white text-teal-600 shadow-lg shadow-teal-900/20' 
                               : 'text-teal-50 hover:bg-teal-500 hover:text-white' ?>">
                            <i class="<?= $menu[1] ?> w-5 text-sm"></i>
                            <span class="text-sm"><?= $menu[2] ?></span>
                        </a>
                    <?php endforeach; ?>

                    <div class="pt-6 border-t border-teal-500/50 mt-6">
                        <a href="../admin/dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-teal-100 hover:bg-white hover:text-teal-600 transition-all font-bold border border-teal-400/30">
                            <i class="fas fa-user-shield w-5 text-sm"></i>
                            <span class="text-sm">Panel Admin</span>
                        </a>
                    </div>
                </nav>
            </div>

            <div class="mt-auto p-6 bg-teal-700/50 border-t border-teal-500/30">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center border border-white/10 text-white">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-sm font-bold text-white truncate">Mahasiswa UNIBI</p>
                        <p class="text-[10px] text-teal-100 font-semibold uppercase tracking-wider opacity-80">Verified Account</p>
                    </div>
                </div>
            </div>
        </aside>

        <main class="flex-1 ml-64 min-w-0 bg-slate-50">
            <div class="p-8 pb-0">
                 <?php if (isset($_SESSION['success'])): ?>
                    <div class="bg-emerald-100 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-6 flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-check-circle"></i>
                            <span class="text-sm font-bold"><?= $_SESSION['success'] ?></span>
                        </div>
                        <button onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
            </div>