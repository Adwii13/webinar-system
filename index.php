<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNIBI Webinar System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="antialiased">
    <div class="min-h-screen bg-gradient-to-br from-teal-500 via-teal-600 to-emerald-700 flex items-center justify-center p-5 relative overflow-hidden">
        
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-teal-400/20 rounded-full blur-3xl"></div>

        <div class="glass-effect p-10 md:p-14 rounded-[40px] shadow-2xl text-center max-w-lg w-full transform transition-all relative z-10 border border-white/20">
            
            <div class="w-24 h-24 bg-teal-50 text-teal-600 rounded-3xl flex items-center justify-center text-4xl mx-auto mb-8 shadow-inner">
                <i class="fas fa-video"></i>
            </div>
            
            <h1 class="text-3xl md:text-4xl font-black text-slate-800 mb-3 tracking-tight italic uppercase">
                UNIBI <span class="text-teal-600">Webinar</span>
            </h1>
            
            <p class="text-slate-500 font-medium text-lg mb-10 leading-relaxed">
                Sistem Manajemen Webinar <br> Universitas Informatika dan Bisnis Indonesia
            </p>
            
            <div class="flex flex-col gap-4">
                <a href="student/index.php" 
                   class="flex items-center justify-center gap-3 p-5 rounded-2xl bg-teal-600 text-white font-black text-sm uppercase tracking-widest shadow-xl shadow-teal-600/20 hover:bg-teal-700 hover:-translate-y-1 transition-all active:scale-95">
                    <i class="fas fa-user-graduate text-lg"></i> Masuk Sebagai Mahasiswa
                </a>
                
                <a href="admin/dashboard.php" 
                   class="flex items-center justify-center gap-3 p-5 rounded-2xl bg-white text-slate-600 border-2 border-slate-100 font-black text-sm uppercase tracking-widest hover:bg-slate-50 hover:border-teal-200 hover:text-teal-600 hover:-translate-y-1 transition-all active:scale-95">
                    <i class="fas fa-user-tie text-lg"></i> Masuk Sebagai Admin
                </a>
            </div>
            
            <div class="mt-12 pt-8 border-t border-slate-100 text-slate-400 text-[11px] font-bold uppercase tracking-[0.2em] space-y-1">
                <p>&copy; 2026 UNIBI - Integrated Webinar Platform</p>
            </div>
        </div>
    </div>
</body>
</html>