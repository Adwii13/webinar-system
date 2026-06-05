<?php
// admin/includes/footer.php
?>
        <footer class="mt-auto py-6 px-8 border-t border-slate-200 bg-white shrink-0">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-slate-500 text-sm font-medium">
                    &copy; 2026 UNIBI - University Webinar System
                </div>
            </div>
        </footer>
    </main> 
</div> 

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// 1. Logika Interaksi Sidebar Mobile Responsive
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
}

// 2. Logika Toggle Dropdown Menu Profil Admin
function toggleProfileDropdown(event) {
    event.stopPropagation(); // Menjaga event klik tidak bocor keluar ke penutup global
    
    const dropdown = document.getElementById('profileDropdown');
    const arrow = document.getElementById('profileArrow');
    
    dropdown.classList.toggle('hidden');
    
    if (dropdown.classList.contains('hidden')) {
        arrow.style.transform = 'rotate(0deg)';
    } else {
        arrow.style.transform = 'rotate(180deg)';
    }
}

// 3. Global Event Listener: Deteksi klik sembarang di luar elemen untuk menutup dropdown otomatis
document.addEventListener('click', function() {
    const dropdown = document.getElementById('profileDropdown');
    const arrow = document.getElementById('profileArrow');
    
    if (dropdown && !dropdown.classList.contains('hidden')) {
        dropdown.classList.add('hidden');
        arrow.style.transform = 'rotate(0deg)';
    }
});
</script>

<style>
/* Animasi Fade In Menus saat Triggers diklik */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in { 
    animation: fadeIn 0.15s ease-out forwards; 
}
</style>

</body>
</html>