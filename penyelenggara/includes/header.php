<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNIBI Webinar System - Penyelenggara</title>
    
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
    <div class="flex min-h-screen relative w-full">
        
        <aside id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-teal-600 text-white transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out z-50 flex flex-col shadow-xl h-full max-h-screen">
            
            <div class="p-6 flex-1 overflow-y-auto min-h-0">
                <button onclick="toggleSidebar()" class="md:hidden absolute top-5 right-5 text-white/70 hover:text-white">
                    <i class="fas fa-times text-xl"></i>
                </button>
                
                <div class="mb-2">
                    <h1 class="text-2xl font-black tracking-tighter text-white">UNIBI</h1>
                </div>
                <p class="text-teal-100 text-[10px] font-bold uppercase tracking-[0.2em] mb-8 opacity-80">Webinar System</p>
                
                <nav class="space-y-1.5">
                    <?php 
                    $current_page = basename($_SERVER['PHP_SELF']);
                    $menus = [
                        ['dashboard.php', 'fa-solid fa-house', 'Dashboard'],
                        ['tambah-webinar.php', 'fas fa-plus-circle', 'Tambah Webinar'],
                        ['verifikasi-pendaftaran.php', 'fas fa-user-check', 'Verifikasi Peserta'],
                    ];

                    foreach ($menus as $menu): 
                        $isActive = ($current_page == $menu[0]);
                    ?>
                        <a href="<?= $menu[0] ?>" 
                           class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 font-medium 
                           <?= $isActive 
                               ? 'bg-white text-teal-600 shadow-lg shadow-teal-950/20 font-semibold' 
                               : 'text-teal-50 hover:bg-teal-500 hover:text-white' ?>">
                            <i class="<?= $menu[1] ?> w-5 text-center text-sm"></i>
                            <span class="text-sm"><?= $menu[2] ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>

            <div class="mt-auto p-4 border-t border-teal-500/20 bg-teal-700/30 relative shrink-0">
                
                <div id="profileDropdown" class="absolute bottom-[calc(100%+0.5rem)] left-4 right-4 bg-white rounded-2xl shadow-2xl border border-slate-100 hidden p-1.5 animate-fade-in z-50">
                    <a href="../logout.php" class="flex items-center gap-3 px-4 py-3 text-rose-600 hover:bg-rose-50 rounded-xl font-bold text-sm transition-all">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Keluar Sistem</span>
                    </a>
                </div>

                <button type="button" onclick="toggleProfileDropdown(event)" class="w-full flex items-center justify-between p-2 rounded-2xl hover:bg-white/10 transition-all text-left group">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center border border-white/10 shrink-0 group-hover:bg-white group-hover:text-teal-600 transition-all">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-sm font-bold text-white truncate"><?= htmlspecialchars($_SESSION['nama'] ?? 'Bayu Anggara') ?></p>
                            <p class="text-[10px] text-teal-100 font-semibold uppercase tracking-wider opacity-80">Penyelenggara</p>
                        </div>
                    </div>
                    <i id="profileArrow" class="fas fa-chevron-up text-xs text-teal-200 transition-transform duration-300 mr-1 shrink-0"></i>
                </button>

            </div>
        </aside>

        <div id="overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/50 z-40 hidden transition-opacity duration-300"></div>

        <main class="flex-1 ml-0 md:ml-64 w-full min-w-0 bg-slate-50 min-h-screen flex flex-col">
            
            <div class="md:hidden bg-white border-b border-slate-200 p-4 flex items-center justify-between sticky top-0 z-30 w-full shrink-0">
                <h1 class="font-black text-teal-600 tracking-tighter text-lg">UNIBI</h1>
                <button onclick="toggleSidebar()" class="p-2 bg-slate-50 rounded-lg text-slate-600 hover:bg-slate-100 transition-colors">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
