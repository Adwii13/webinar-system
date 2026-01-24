<?php
// index.php - Landing Page dengan pilihan
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNIBI Webinar System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="antialiased">
    <div class="min-h-screen bg-gradient-to-br from-indigo-500 to-purple-700 flex items-center justify-center p-5">
        
        <div class="bg-white p-10 md:p-14 rounded-3xl shadow-2xl text-center max-w-lg w-full transform transition-all">
            
            <div class="text-indigo-600 text-6xl mb-6">
                <i class="fas fa-video"></i>
            </div>
            
            <h1 class="text-3xl md:text-4xl font-extrabold text-slate-800 mb-2">
                UNIBI Webinar System
            </h1>
            
            <p class="text-slate-500 text-lg mb-10">
                Sistem Manajemen Webinar Universitas
            </p>
            
            <div class="flex flex-col gap-4">
                <a href="student/index.php" 
                   class="flex items-center justify-center gap-3 p-4 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-bold text-lg shadow-lg shadow-indigo-200 hover:-translate-y-1 hover:shadow-indigo-300 transition-all active:scale-95">
                    <i class="fas fa-user-graduate"></i> Masuk sebagai Mahasiswa
                </a>
                
                <a href="admin/dashboard.php" 
                   class="flex items-center justify-center gap-3 p-4 rounded-xl bg-slate-50 text-slate-600 border-2 border-slate-200 font-bold text-lg hover:bg-slate-100 hover:-translate-y-1 transition-all active:scale-95">
                    <i class="fas fa-user-tie"></i> Masuk sebagai Admin
                </a>
            </div>
            
            <div class="mt-10 text-slate-400 text-sm space-y-1">
                <p>&copy; 2026 UNIBI - University E-Learning Platform</p>
                <p class="font-medium text-indigo-400/80 italic">Tanpa Login Required - Mode Demo</p>
            </div>
        </div>
    </div>
</body>
</html>