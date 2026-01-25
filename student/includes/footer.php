<footer class="mt-auto py-6 px-8 border-t border-slate-200 bg-white">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-slate-500 text-sm font-medium">
                    &copy; 2026 UNIBI - University E-Learning Platform
                </div>
                <!-- <button onclick="switchView()" class="flex items-center gap-2 px-4 py-2 bg-slate-100 text-slate-600 font-bold rounded-lg hover:bg-teal-600 hover:text-white transition-all text-sm">
                    <i class="fas fa-user-shield text-xs"></i>
                    Switch to Admin View
                </button> -->
            </div>
        </footer>

    </main> </div> 
<script>
/**
 * Fungsi untuk berpindah kembali ke dashboard admin
 */
// function switchView() {
//     if (confirm('Beralih ke Panel Admin?')) {
//         window.location.href = '../admin/dashboard.php';
//     }
// }

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    
    // Toggle class translate-x-0 untuk buka sidebar
    sidebar.classList.toggle('-translate-x-full');
    // Toggle overlay
    overlay.classList.toggle('hidden');
}

/**
 * Transisi halus saat halaman dimuat (sesuai script di header)
 */
window.addEventListener('load', function() {
    document.body.classList.add('loaded');
});

/**
 * Auto-hide notifikasi jika ada
 */
setTimeout(() => {
    const alerts = document.querySelectorAll('.bg-emerald-100');
    alerts.forEach(alert => {
        alert.style.transition = 'opacity 0.5s ease';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
    });
}, 4000);
</script>

</body>
</html>