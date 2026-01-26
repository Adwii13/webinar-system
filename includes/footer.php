<footer class="mt-auto py-6 px-8 border-t border-slate-200 bg-white">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-slate-500 text-sm">
                    &copy; 2026 UNIBI - University E-Learning Platform
                </div>
                <!-- <button onclick="switchView()" class="flex items-center gap-2 px-4 py-2 bg-slate-100 text-slate-600 font-bold rounded-lg hover:bg-indigo-600 hover:text-white transition-all text-sm">
                    <i class="fas fa-exchange-alt"></i> Switch to Student View
                </button> -->
            </div>
        </footer>
    </main>
    </div>
<script>
    function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    
    // Toggle class translate-x-0 untuk buka sidebar
    sidebar.classList.toggle('-translate-x-full');
    // Toggle overlay
    overlay.classList.toggle('hidden');
}
// function switchView() {
//     if (confirm('Switch ke Student View?')) {
//         window.location.href = '../student/index.php';
//     }
// }
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>